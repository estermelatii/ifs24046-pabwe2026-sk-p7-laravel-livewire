<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockDeleteConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_asks_then_confirms_delete(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create(['name' => 'Kardus Besar']);
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('askDelete', $stock->id)
            ->assertSet('deletingId', $stock->id)->assertSet('deletingName', 'Kardus Besar')
            ->assertSee('Ya, hapus')
            ->call('confirmDelete')
            ->assertSet('deletingId', null)->assertDontSee('Ya, hapus');

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_list_cancel_keeps_stock(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('askDelete', $stock->id)->call('cancelDelete')
            ->assertSet('deletingId', null)->assertSet('deletingName', '');

        $this->assertDatabaseHas('stocks', ['id' => $stock->id]);
    }

    public function test_list_cannot_ask_delete_for_foreign_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $foreign = Stock::factory()->create();

        Livewire::test(StockList::class)->call('askDelete', $foreign->id)->assertForbidden();
    }

    public function test_detail_confirm_flow(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();
        $this->actingAs($user);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('askDelete')->assertSet('confirmingDelete', true)->assertSee('Ya, hapus')
            ->call('cancelDelete')->assertSet('confirmingDelete', false)
            ->call('askDelete')->call('confirmDelete')->assertRedirect('/stocks');

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
