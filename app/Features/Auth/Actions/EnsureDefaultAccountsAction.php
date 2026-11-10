<?php

namespace App\Features\Auth\Actions;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;

/**
 * Memastikan akun bawaan tersedia: admin / admin dan member / member (sesuai seeder).
 *
 * Aman dijalankan berulang kali, termasuk pada database kosong (mis. server deploy
 * yang databasenya dibuat ulang). Stok contoh hanya dibuat saat akun member baru dibuat.
 */
class EnsureDefaultAccountsAction
{
    private const DEMO_STOCKS = [
        ['Kertas A4', 'ATK-001', 50, 'rim', 45000],
        ['Spidol Hitam', 'ATK-002', 3, 'pcs', 8000],
        ['Tinta Printer', 'ATK-003', 0, 'botol', 65000],
        ['Map Folder', 'ATK-004', 25, 'pcs', 3500],
        ['Stapler', 'ATK-005', 8, 'pcs', 15000],
    ];

    public function execute(): void
    {
        $this->ensure('admin', 'Administrator', 'admin@example.com', User::ROLE_ADMIN);
        $member = $this->ensure('member', 'Member Demo', 'member@example.com', User::ROLE_MEMBER);

        if ($member->wasRecentlyCreated) {
            $this->createDemoStocks($member);
        }
    }

    private function ensure(string $username, string $name, string $email, string $role): User
    {
        $user = User::firstOrNew(['username' => $username]);

        if (! $user->exists) {
            $user->name = $name;
            $user->email = User::where('email', $email)->exists() ? $username.'.'.uniqid().'@example.com' : $email;
        }

        $user->role = $role;
        $user->password = $username;
        $user->save();

        return $user;
    }

    private function createDemoStocks(User $member): void
    {
        $min = 5;

        foreach (self::DEMO_STOCKS as [$name, $sku, $quantity, $unit, $price]) {
            Stock::create([
                'user_id' => $member->id,
                'name' => $name,
                'sku' => $sku,
                'quantity' => $quantity,
                'unit' => $unit,
                'price' => $price,
                'min_stock' => $min,
                'status' => StockStatus::fromQuantity($quantity, $min)->value,
            ]);
        }
    }
}
