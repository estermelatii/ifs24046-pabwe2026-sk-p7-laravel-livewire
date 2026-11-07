<?php

namespace App\Features\Ai\DTO;

final readonly class GeneratedStockItem
{
    public function __construct(
        public string $name,
        public ?string $sku = null,
        public ?string $description = null,
        public int $quantity = 0,
        public string $unit = 'pcs',
        public ?float $price = null,
        public int $minStock = 5,
    ) {}

    public static function fromArray(array $data): self
    {
        $name = trim((string) ($data['name'] ?? ''));

        return new self(
            name: $name !== '' ? $name : 'Item tanpa nama',
            sku: isset($data['sku']) && $data['sku'] !== '' ? (string) $data['sku'] : null,
            description: isset($data['description']) && $data['description'] !== '' ? (string) $data['description'] : null,
            quantity: max(0, (int) ($data['quantity'] ?? 0)),
            unit: isset($data['unit']) && $data['unit'] !== '' ? (string) $data['unit'] : 'pcs',
            price: isset($data['price']) && is_numeric($data['price']) ? max(0.0, (float) $data['price']) : null,
            minStock: max(0, (int) ($data['min_stock'] ?? 5)),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'price' => $this->price,
            'min_stock' => $this->minStock,
        ];
    }
}
