<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        $updated = (new ProfileService)->updateProfile($user, ['name' => 'N', 'username' => 'u1', 'email' => 'n@example.com']);

        $this->assertSame('u1', $updated->username);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create(['password' => 'lama']);
        $service = new ProfileService;

        $this->assertFalse($service->updatePassword($user, 'salah', 'baru'));
        $this->assertTrue($service->updatePassword($user, 'lama', 'baru'));
        $this->assertTrue(Hash::check('baru', $user->refresh()->password));
    }

    public function test_update_photo_deletes_old_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $service = new ProfileService;

        $user = $service->updatePhoto($user, UploadedFile::fake()->image('a.png'));
        $old = $user->profile_photo_path;
        $user = $service->updatePhoto($user, UploadedFile::fake()->image('b.png'));

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}
