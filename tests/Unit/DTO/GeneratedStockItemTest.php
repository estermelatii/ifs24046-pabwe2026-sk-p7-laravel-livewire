<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_from_array_full(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => ' Kabel ', 'sku' => 'K-1', 'description' => 'd', 'quantity' => '7',
            'unit' => 'm', 'price' => '1500.5', 'min_stock' => 2,
        ]);

        $this->assertSame('Kabel', $item->name);
        $this->assertSame(7, $item->quantity);
        $this->assertSame(1500.5, $item->price);
        $this->assertSame(
            ['name' => 'Kabel', 'sku' => 'K-1', 'description' => 'd', 'quantity' => 7, 'unit' => 'm', 'price' => 1500.5, 'min_stock' => 2],
            $item->toArray()
        );
    }

    public function test_from_array_defaults_and_sanitising(): void
    {
        $item = GeneratedStockItem::fromArray(['quantity' => -4, 'price' => 'abc', 'sku' => '', 'description' => '', 'unit' => '', 'min_stock' => -1]);

        $this->assertSame('Item tanpa nama', $item->name);
        $this->assertNull($item->sku);
        $this->assertNull($item->description);
        $this->assertNull($item->price);
        $this->assertSame(0, $item->quantity);
        $this->assertSame('pcs', $item->unit);
        $this->assertSame(0, $item->minStock);
    }

    public function test_negative_price_is_clamped(): void
    {
        $this->assertSame(0.0, GeneratedStockItem::fromArray(['name' => 'x', 'price' => -5])->price);
    }
}
