<?php

namespace Tests\Feature;

use App\Features\Auth\Livewire\Login;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_by_role(): void
    {
        $this->get('/')->assertOk()->assertSeeLivewire(Login::class);
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertRedirect('/admin/users');
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/stocks');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/stocks')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_authenticated_user_cannot_open_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/');
    }

    public function test_role_restrictions(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($admin)->get('/stocks')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($member)->get('/stocks')->assertOk();
        $this->actingAs($member)->get('/profile')->assertOk();
        $this->actingAs($admin)->get('/profile')->assertOk();
    }

    public function test_ownership_is_enforced_on_stock_detail(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $stock = Stock::factory()->for($owner)->create();

        $this->actingAs($owner)->get("/stocks/{$stock->id}")->assertOk();
        $this->actingAs($other)->get("/stocks/{$stock->id}")->assertForbidden();
        $this->actingAs($owner)->get('/stocks/999999')->assertNotFound();
    }

    public function test_admin_can_view_user_stocks_page(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get("/admin/users/{$member->id}/stocks")->assertOk();
    }
}
