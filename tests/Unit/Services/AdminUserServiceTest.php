<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_with_count_and_search(): void
    {
        $service = new AdminUserService(new StockRepository);
        $budi = User::factory()->create(['name' => 'Budi', 'username' => 'budi', 'email' => 'budi@example.com']);
        User::factory()->create(['name' => 'Citra', 'username' => 'citra', 'email' => 'citra@example.com']);
        Stock::factory()->count(2)->for($budi)->create();

        $this->assertSame(2, $service->users()->total());
        $this->assertSame(2, $service->users()->first()->stocks_count);
        $this->assertSame(1, $service->users('budi')->total());
        $this->assertSame(1, $service->users('citra@example')->total());
    }

    public function test_stocks_of_user(): void
    {
        $service = new AdminUserService(new StockRepository);
        $user = User::factory()->create();
        Stock::factory()->for($user)->withQuantity(0)->create(['name' => 'Habis']);
        Stock::factory()->for($user)->withQuantity(99)->create(['name' => 'Penuh']);

        $this->assertSame(2, $service->stocksOf($user)->total());
        $this->assertSame(1, $service->stocksOf($user, 'Habis')->total());
        $this->assertSame(1, $service->stocksOf($user, null, 'available')->total());
    }
}
