<?php

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class StockDetail extends Component
{
    use WithFileUploads;

    public Stock $stock;

    public bool $editing = false;

    public bool $confirmingDelete = false;

    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public int|string $quantity = 0;

    public string $unit = 'pcs';

    public string|float|null $price = null;

    public int|string $min_stock = 5;

    public $cover = null;

    public function mount(Stock $stock): void
    {
        $this->authorize('view', $stock);
        $this->stock = $stock;
        $this->fillForm();
    }

    public function startEdit(): void
    {
        $this->authorize('update', $this->stock);
        $this->fillForm();
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    public function save(StockService $service): void
    {
        $this->authorize('update', $this->stock);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        $this->stock = $service->update($this->stock, $data);
        $this->editing = false;
        session()->flash('status', 'Stok berhasil diperbarui.');
    }

    public function adjust(int $delta, StockService $service): void
    {
        $this->authorize('update', $this->stock);
        $this->stock = $service->adjustQuantity($this->stock, $delta);
        $this->fillForm();
    }

    public function saveCover(StockService $service): void
    {
        $this->authorize('update', $this->stock);
        $this->validate(['cover' => ['required', 'image', 'max:2048']]);

        $this->stock = $service->storeCover($this->stock, $this->cover);
        $this->reset('cover');
        session()->flash('status', 'Cover berhasil diperbarui.');
    }

    public function removeCover(StockService $service): void
    {
        $this->authorize('update', $this->stock);
        $this->stock = $service->removeCover($this->stock);
    }

    public function askDelete(): void
    {
        $this->authorize('delete', $this->stock);
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function confirmDelete(StockService $service)
    {
        return $this->delete($service);
    }

    public function delete(StockService $service)
    {
        $this->authorize('delete', $this->stock);
        $service->delete($this->stock);
        session()->flash('status', 'Stok berhasil dihapus.');

        return $this->redirect('/stocks');
    }

    public function render(): View
    {
        return view('livewire.stock.stock-detail', [
            'descriptionHtml' => Str::markdown((string) $this->stock->description, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    private function fillForm(): void
    {
        $this->name = $this->stock->name;
        $this->sku = (string) $this->stock->sku;
        $this->description = (string) $this->stock->description;
        $this->quantity = $this->stock->quantity;
        $this->unit = $this->stock->unit;
        $this->price = $this->stock->price;
        $this->min_stock = $this->stock->min_stock;
    }
}
