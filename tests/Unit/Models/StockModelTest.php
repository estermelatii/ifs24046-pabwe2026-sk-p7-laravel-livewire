<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_and_relation(): void
    {
        $stock = Stock::factory()->withQuantity(0)->create();

        $this->assertSame(StockStatus::OutOfStock, $stock->status);
        $this->assertInstanceOf(User::class, $stock->user);
        $this->assertTrue($stock->user->stocks->contains($stock));
    }

    public function test_cover_image_url(): void
    {
        Storage::fake('public');
        $stock = Stock::factory()->make(['cover_image_path' => null]);
        $this->assertNull($stock->coverImageUrl());

        $stock->cover_image_path = 'stock-covers/a.png';
        $this->assertStringContainsString('stock-covers/a.png', $stock->coverImageUrl());
    }

    public function test_factory_with_quantity_state(): void
    {
        $this->assertSame('low_stock', Stock::factory()->withQuantity(2, 5)->make()->status->value);
    }
}
