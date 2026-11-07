<?php

namespace Database\Seeders;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin',
        ]);

        $member = User::factory()->member()->create([
            'name' => 'Member Demo',
            'username' => 'member',
            'email' => 'member@example.com',
            'password' => 'member',
        ]);

        Stock::factory()->for($member)->withQuantity(50)->create(['name' => 'Kertas A4', 'sku' => 'ATK-001', 'unit' => 'rim']);
        Stock::factory()->for($member)->withQuantity(3)->create(['name' => 'Spidol Hitam', 'sku' => 'ATK-002', 'unit' => 'pcs']);
        Stock::factory()->for($member)->withQuantity(0)->create(['name' => 'Tinta Printer', 'sku' => 'ATK-003', 'unit' => 'botol']);
        Stock::factory()->for($member)->withQuantity(25)->create(['name' => 'Map Folder', 'sku' => 'ATK-004', 'unit' => 'pcs']);
        Stock::factory()->for($member)->withQuantity(8, 10)->create(['name' => 'Stapler', 'sku' => 'ATK-005', 'unit' => 'pcs']);
    }
}
