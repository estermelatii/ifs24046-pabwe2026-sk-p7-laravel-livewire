<?php

namespace Tests\Unit\Actions;

use App\Features\Auth\Actions\EnsureDefaultAccountsAction;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnsureDefaultAccountsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_member_and_demo_stocks_on_empty_database(): void
    {
        (new EnsureDefaultAccountsAction)->execute();

        $admin = User::where('username', 'admin')->firstOrFail();
        $member = User::where('username', 'member')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('admin', $admin->password));
        $this->assertTrue($member->isMember());
        $this->assertTrue(Hash::check('member', $member->password));
        $this->assertSame(5, Stock::where('user_id', $member->id)->count());
        $this->assertSame('out_of_stock', Stock::where('sku', 'ATK-003')->firstOrFail()->status->value);
    }

    public function test_is_idempotent(): void
    {
        $action = new EnsureDefaultAccountsAction;
        $action->execute();
        $action->execute();

        $this->assertSame(2, User::count());
        $this->assertSame(5, Stock::count());
    }

    public function test_repairs_existing_admin_role_and_password(): void
    {
        User::factory()->create(['username' => 'admin', 'role' => User::ROLE_MEMBER, 'password' => 'salah']);

        (new EnsureDefaultAccountsAction)->execute();

        $admin = User::where('username', 'admin')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('admin', $admin->password));
    }

    public function test_uses_alternative_email_when_default_email_is_taken(): void
    {
        User::factory()->create(['username' => 'orang_lain', 'email' => 'admin@example.com']);

        (new EnsureDefaultAccountsAction)->execute();

        $admin = User::where('username', 'admin')->firstOrFail();
        $this->assertNotSame('admin@example.com', $admin->email);
        $this->assertStringStartsWith('admin.', $admin->email);
    }

    public function test_existing_member_does_not_get_demo_stocks(): void
    {
        User::factory()->create(['username' => 'member']);

        (new EnsureDefaultAccountsAction)->execute();

        $this->assertSame(0, Stock::count());
    }
}
