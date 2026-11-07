<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_user_list_shows_users_with_stock_count_and_search(): void
    {
        $budi = User::factory()->create(['name' => 'Budi Santoso']);
        User::factory()->create(['name' => 'Citra Dewi']);
        Stock::factory()->count(3)->for($budi)->create();

        Livewire::test(UserList::class)
            ->assertSee('Budi Santoso')->assertSee('Citra Dewi')
            ->set('search', 'Budi')->assertSee('Budi Santoso')->assertDontSee('Citra Dewi');
    }

    public function test_user_stock_list_is_read_only(): void
    {
        $member = User::factory()->create();
        Stock::factory()->for($member)->withQuantity(0)->create(['name' => 'Barang Habis']);
        Stock::factory()->for($member)->withQuantity(40)->create(['name' => 'Barang Banyak']);

        $component = Livewire::test(AdminUserStockList::class, ['user' => $member])
            ->assertSee('Barang Habis')->assertSee('Barang Banyak')
            ->assertDontSee('Hapus')->assertDontSee('Edit')
            ->set('status', 'out_of_stock')->assertSee('Barang Habis')->assertDontSee('Barang Banyak')
            ->set('status', '')->set('search', 'Banyak')->assertSee('Barang Banyak')->assertDontSee('Barang Habis');

        $this->assertFalse(method_exists($component->instance(), 'delete'));
        $this->assertFalse(method_exists($component->instance(), 'save'));
    }
}
