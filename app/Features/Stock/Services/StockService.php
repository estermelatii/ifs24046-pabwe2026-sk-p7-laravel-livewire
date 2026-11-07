<?php

namespace App\Features\Stock\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StockService
{
    public function __construct(private readonly StockRepositoryInterface $stocks) {}

    public function list(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->stocks->paginateForUser($user, $search, $status, $perPage);
    }

    /**
     * Ringkasan jumlah barang per status untuk satu user.
     *
     * @return array{all: int, available: int, low_stock: int, out_of_stock: int}
     */
    public function summary(User $user): array
    {
        $all = $this->stocks->allForUser($user);
        $counts = $all->countBy(fn (Stock $stock) => $stock->status->value);

        return [
            'all' => $all->count(),
            'available' => $counts['available'] ?? 0,
            'low_stock' => $counts['low_stock'] ?? 0,
            'out_of_stock' => $counts['out_of_stock'] ?? 0,
        ];
    }

    /**
     * Angka tambahan untuk dashboard: total unit, nilai persediaan, dan barang yang perlu diisi ulang.
     *
     * @return array{units: int, value: float, restock: \Illuminate\Support\Collection<int, Stock>}
     */
    public function insights(User $user): array
    {
        $all = $this->stocks->allForUser($user);

        return [
            'units' => (int) $all->sum('quantity'),
            'value' => (float) $all->sum(fn (Stock $stock) => $stock->quantity * (float) $stock->price),
            'restock' => $all
                ->filter(fn (Stock $stock) => $stock->status !== StockStatus::Available)
                ->sortBy('quantity')
                ->take(4)
                ->values(),
        ];
    }

    public function find(int $id): ?Stock
    {
        return $this->stocks->find($id);
    }

    public function create(User $user, array $data): Stock
    {
        return $this->stocks->create($this->normalize($data) + ['user_id' => $user->id]);
    }

    public function update(Stock $stock, array $data): Stock
    {
        return $this->stocks->update($stock, $this->normalize($data));
    }

    public function adjustQuantity(Stock $stock, int $delta): Stock
    {
        $quantity = max(0, $stock->quantity + $delta);

        return $this->stocks->update($stock, [
            'quantity' => $quantity,
            'status' => StockStatus::fromQuantity($quantity, $stock->min_stock)->value,
        ]);
    }

    public function delete(Stock $stock): bool
    {
        $this->deleteCover($stock);

        return $this->stocks->delete($stock);
    }

    public function storeCover(Stock $stock, UploadedFile $file): Stock
    {
        $this->deleteCover($stock);
        $path = $file->store('stock-covers', 'public');

        return $this->stocks->update($stock, ['cover_image_path' => $path]);
    }

    public function removeCover(Stock $stock): Stock
    {
        $this->deleteCover($stock);

        return $this->stocks->update($stock, ['cover_image_path' => null]);
    }

    /**
     * @param  array<int, GeneratedStockItem>  $items
     */
    public function createFromGenerated(User $user, array $items): int
    {
        foreach ($items as $item) {
            $this->create($user, $item->toArray());
        }

        return count($items);
    }

    private function deleteCover(Stock $stock): void
    {
        if ($stock->cover_image_path) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }
    }

    private function normalize(array $data): array
    {
        $quantity = (int) ($data['quantity'] ?? 0);
        $min = (int) ($data['min_stock'] ?? 5);

        return [
            'name' => $data['name'],
            'sku' => ($data['sku'] ?? null) ?: null,
            'description' => ($data['description'] ?? null) ?: null,
            'quantity' => $quantity,
            'unit' => ($data['unit'] ?? null) ?: 'pcs',
            'price' => ($data['price'] ?? null) === '' ? null : ($data['price'] ?? null),
            'min_stock' => $min,
            'status' => StockStatus::fromQuantity($quantity, $min)->value,
        ];
    }
}
