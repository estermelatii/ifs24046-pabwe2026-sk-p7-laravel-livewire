<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockInsightsTest extends TestCase
{
    use RefreshDatabase;

    private function service(): StockService
    {
        return new StockService(new StockRepository);
    }

    public function test_insights_totals_value_and_restock_list(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->withQuantity(10)->create(['name' => 'A', 'price' => 1000]);
        Stock::factory()->for($user)->withQuantity(3)->create(['name' => 'B', 'price' => 500]);
        Stock::factory()->for($user)->withQuantity(0)->create(['name' => 'C', 'price' => null]);
        Stock::factory()->withQuantity(99)->create(['price' => 1]);

        $insights = $this->service()->insights($user);

        $this->assertSame(13, $insights['units']);
        $this->assertSame(11500.0, $insights['value']);
        $this->assertSame(['C', 'B'], $insights['restock']->pluck('name')->all());
    }

    public function test_restock_is_limited_to_four_lowest_items(): void
    {
        $user = User::factory()->create();
        foreach ([4, 3, 2, 1, 0] as $qty) {
            Stock::factory()->for($user)->withQuantity($qty)->create();
        }

        $restock = $this->service()->insights($user)['restock'];

        $this->assertCount(4, $restock);
        $this->assertSame([0, 1, 2, 3], $restock->pluck('quantity')->all());
    }

    public function test_insights_are_empty_for_user_without_stock(): void
    {
        $insights = $this->service()->insights(User::factory()->create());

        $this->assertSame(0, $insights['units']);
        $this->assertSame(0.0, $insights['value']);
        $this->assertTrue($insights['restock']->isEmpty());
    }
}
