<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_counts_by_status_for_one_user(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->withQuantity(50)->count(2)->create();
        Stock::factory()->for($user)->withQuantity(3)->create();
        Stock::factory()->for($user)->withQuantity(0)->count(3)->create();
        Stock::factory()->withQuantity(0)->create();

        $summary = (new StockService(new StockRepository))->summary($user);

        $this->assertSame(
            ['all' => 6, 'available' => 2, 'low_stock' => 1, 'out_of_stock' => 3],
            $summary
        );
    }

    public function test_summary_is_zero_for_user_without_stock(): void
    {
        $summary = (new StockService(new StockRepository))->summary(User::factory()->create());

        $this->assertSame(['all' => 0, 'available' => 0, 'low_stock' => 0, 'out_of_stock' => 0], $summary);
    }
}
