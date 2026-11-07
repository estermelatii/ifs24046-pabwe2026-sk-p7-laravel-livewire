<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_member_and_logs_in(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('username', 'siti')
            ->set('email', 'siti@example.com')
            ->set('password', 'rahasia1')
            ->set('password_confirmation', 'rahasia1')
            ->call('submit')
            ->assertRedirect('/stocks');

        $this->assertDatabaseHas('users', ['username' => 'siti', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_validates_unique_and_confirmation(): void
    {
        User::factory()->create(['username' => 'siti', 'email' => 'siti@example.com']);

        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('username', 'siti')
            ->set('email', 'siti@example.com')
            ->set('password', 'rahasia1')
            ->set('password_confirmation', 'beda')
            ->call('submit')
            ->assertHasErrors(['username', 'email', 'password']);
    }
}
