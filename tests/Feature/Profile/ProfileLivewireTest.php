<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['password' => 'lama123']);
        $this->actingAs($this->user);
    }

    public function test_mount_fills_form(): void
    {
        Livewire::test(Profile::class)
            ->assertSet('name', $this->user->name)
            ->assertSet('username', $this->user->username)
            ->assertSet('email', $this->user->email);
    }

    public function test_update_profile(): void
    {
        Livewire::test(Profile::class)
            ->set('name', 'Nama Baru')->set('username', 'baru')->set('email', 'baru@example.com')
            ->call('saveProfile')->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'username' => 'baru']);
    }

    public function test_profile_unique_ignores_self_but_not_others(): void
    {
        $other = User::factory()->create();

        Livewire::test(Profile::class)
            ->set('username', $this->user->username)->call('saveProfile')->assertHasNoErrors();

        Livewire::test(Profile::class)
            ->set('username', $other->username)->call('saveProfile')->assertHasErrors('username');
    }

    public function test_change_password(): void
    {
        Livewire::test(Profile::class)
            ->set('current_password', 'lama123')->set('new_password', 'baru1234')->set('new_password_confirmation', 'baru1234')
            ->call('savePassword')->assertHasNoErrors();

        $this->assertTrue(Hash::check('baru1234', $this->user->refresh()->password));
    }

    public function test_wrong_current_password(): void
    {
        Livewire::test(Profile::class)
            ->set('current_password', 'salah')->set('new_password', 'baru1234')->set('new_password_confirmation', 'baru1234')
            ->call('savePassword')->assertHasErrors('current_password');
    }

    public function test_upload_photo_replaces_previous(): void
    {
        Storage::fake('public');

        Livewire::test(Profile::class)
            ->set('photo', UploadedFile::fake()->image('a.png'))->call('savePhoto')->assertHasNoErrors();
        $first = $this->user->refresh()->profile_photo_path;

        Livewire::test(Profile::class)
            ->set('photo', UploadedFile::fake()->image('b.png'))->call('savePhoto')->assertHasNoErrors();

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($this->user->refresh()->profile_photo_path);
    }

    public function test_photo_must_be_image(): void
    {
        Livewire::test(Profile::class)
            ->set('photo', UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'))
            ->call('savePhoto')->assertHasErrors('photo');
    }
}
