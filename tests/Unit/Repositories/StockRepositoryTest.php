<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_and_queries(): void
    {
        $repo = new StockRepository;
        $user = User::factory()->create();

        $stock = $repo->create([
            'user_id' => $user->id, 'name' => 'Lampu', 'sku' => 'L-1', 'quantity' => 4,
            'unit' => 'pcs', 'min_stock' => 5, 'status' => 'low_stock',
        ]);
        Stock::factory()->for($user)->withQuantity(30)->create(['name' => 'Kabel', 'sku' => 'K-9']);
        Stock::factory()->create(['name' => 'Lampu Orang']);

        $this->assertSame($stock->id, $repo->find($stock->id)->id);
        $this->assertNull($repo->find(0));
        $this->assertCount(2, $repo->allForUser($user));
        $this->assertSame(2, $repo->paginateForUser($user)->total());
        $this->assertSame(1, $repo->paginateForUser($user, 'Lampu')->total());
        $this->assertSame(1, $repo->paginateForUser($user, 'K-9')->total());
        $this->assertSame(1, $repo->paginateForUser($user, null, 'low_stock')->total());

        $updated = $repo->update($stock, ['name' => 'Lampu LED']);
        $this->assertSame('Lampu LED', $updated->name);

        $this->assertTrue($repo->delete($stock));
        $this->assertNull($repo->find($stock->id));
    }
}
