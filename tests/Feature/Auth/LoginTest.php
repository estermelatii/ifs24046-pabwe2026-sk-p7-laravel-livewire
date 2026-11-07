<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_required_ids(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="login-username"', false)
            ->assertSee('id="login-password"', false)
            ->assertSee('id="login-submit"', false);
    }

    public function test_member_login_by_username_redirects_to_stocks(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia1']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'rahasia1')
            ->call('submit')
            ->assertRedirect('/stocks');

        $this->assertAuthenticated();
    }

    public function test_admin_login_by_email_redirects_to_admin_users(): void
    {
        User::factory()->admin()->create(['email' => 'adm@example.com', 'password' => 'rahasia1']);

        Livewire::test(Login::class)
            ->set('login', 'adm@example.com')
            ->set('password', 'rahasia1')
            ->call('submit')
            ->assertRedirect('/admin/users');
    }

    public function test_wrong_credentials_show_error(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia1']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'salah')
            ->call('submit')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_validation_requires_fields(): void
    {
        Livewire::test(Login::class)->call('submit')->assertHasErrors(['login', 'password']);
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
