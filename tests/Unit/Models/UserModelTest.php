<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $this->assertTrue(User::factory()->admin()->make()->isAdmin());
        $this->assertFalse(User::factory()->admin()->make()->isMember());
        $this->assertTrue(User::factory()->member()->make()->isMember());
        $this->assertFalse(User::factory()->member()->make()->isAdmin());
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'rahasia']);

        $this->assertNotSame('rahasia', $user->password);
        $this->assertTrue(Hash::check('rahasia', $user->password));
    }

    public function test_profile_photo_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->make();
        $this->assertNull($user->profilePhotoUrl());

        $user->profile_photo_path = 'profile-photos/x.png';
        $this->assertStringContainsString('profile-photos/x.png', $user->profilePhotoUrl());
    }
}
