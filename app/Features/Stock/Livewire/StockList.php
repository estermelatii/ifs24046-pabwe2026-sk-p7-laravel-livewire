<?php

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Layout('components.layouts.app')]
class StockList extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public ?int $deletingId = null;

    public string $deletingName = '';

    // Form modal
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public int|string $quantity = 0;

    public string $unit = 'pcs';

    public string|float|null $price = null;

    public int|string $min_stock = 5;

    // Import Excel
    public $importFile = null;

    // AI generator
    public bool $showAi = false;

    public string $aiPrompt = '';

    public int|string $aiCount = 5;

    /** @var array<int, array<string, mixed>> */
    public array $aiResults = [];

    /** @var array<int, int|string> */
    public array $aiSelected = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Stock::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id, StockService $service): void
    {
        $stock = $this->ownedStock($id, $service);

        $this->editingId = $stock->id;
        $this->name = $stock->name;
        $this->sku = (string) $stock->sku;
        $this->description = (string) $stock->description;
        $this->quantity = $stock->quantity;
        $this->unit = $stock->unit;
        $this->price = $stock->price;
        $this->min_stock = $stock->min_stock;
        $this->showForm = true;
    }

    public function save(StockService $service): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        if ($this->editingId) {
            $service->update($this->ownedStock($this->editingId, $service), $data);
            session()->flash('status', 'Stok berhasil diperbarui.');
        } else {
            $this->authorize('create', Stock::class);
            $service->create(auth()->user(), $data);
            session()->flash('status', 'Stok berhasil ditambahkan.');
        }

        $this->closeForm();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function askDelete(int $id, StockService $service): void
    {
        $stock = $this->ownedStock($id, $service);
        $this->authorize('delete', $stock);
        $this->deletingId = $stock->id;
        $this->deletingName = $stock->name;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function confirmDelete(StockService $service): void
    {
        $this->delete((int) $this->deletingId, $service);
        $this->cancelDelete();
    }

    public function delete(int $id, StockService $service): void
    {
        $stock = $this->ownedStock($id, $service);
        $this->authorize('delete', $stock);
        $service->delete($stock);
        session()->flash('status', 'Stok berhasil dihapus.');
    }

    public function adjust(int $id, int $delta, StockService $service): void
    {
        $service->adjustQuantity($this->ownedStock($id, $service), $delta);
    }

    public function export(StockExcelService $excel): StreamedResponse
    {
        $content = $excel->export(auth()->user()->stocks()->orderBy('id')->get());

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'stok-barang.xlsx');
    }

    public function import(StockExcelService $excel): void
    {
        $this->validate(['importFile' => ['required', 'file', 'mimes:xlsx', 'max:5120']]);

        try {
            $count = $excel->import(auth()->user(), $this->importFile->getRealPath());
            session()->flash('status', "{$count} stok berhasil diimpor.");
        } catch (Throwable) {
            $this->addError('importFile', 'File Excel tidak dapat dibaca.');

            return;
        }

        $this->reset('importFile');
    }

    public function openAi(): void
    {
        $this->showAi = true;
    }

    public function closeAi(): void
    {
        $this->showAi = false;
        $this->reset('aiPrompt', 'aiResults', 'aiSelected');
        $this->aiCount = 5;
    }

    public function generate(AiClientInterface $ai): void
    {
        $this->validate([
            'aiPrompt' => ['required', 'string', 'max:500'],
            'aiCount' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        try {
            $items = $ai->generate($this->aiPrompt, (int) $this->aiCount);
        } catch (Throwable $e) {
            $this->addError('aiPrompt', 'Gagal generate: '.$e->getMessage());

            return;
        }

        $this->aiResults = array_map(fn (GeneratedStockItem $i) => $i->toArray(), $items);
        $this->aiSelected = array_keys($this->aiResults);
    }

    public function saveGenerated(StockService $service): void
    {
        $this->authorize('create', Stock::class);

        $items = [];
        foreach ($this->aiSelected as $index) {
            if (isset($this->aiResults[(int) $index])) {
                $items[] = GeneratedStockItem::fromArray($this->aiResults[(int) $index]);
            }
        }

        $count = $service->createFromGenerated(auth()->user(), $items);
        session()->flash('status', "{$count} stok hasil AI berhasil disimpan.");
        $this->closeAi();
    }

    public function render(StockService $service): View
    {
        return view('livewire.stock.stock-list', [
            'stocks' => $service->list(auth()->user(), $this->search ?: null, $this->statusFilter ?: null),
            'summary' => $service->summary(auth()->user()),
            'insights' => $service->insights(auth()->user()),
        ]);
    }

    private function ownedStock(int $id, StockService $service): Stock
    {
        $stock = $service->find($id);
        abort_if($stock === null, 404);
        $this->authorize('update', $stock);

        return $stock;
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'sku', 'description', 'price');
        $this->quantity = 0;
        $this->unit = 'pcs';
        $this->min_stock = 5;
        $this->resetValidation();
    }
}
