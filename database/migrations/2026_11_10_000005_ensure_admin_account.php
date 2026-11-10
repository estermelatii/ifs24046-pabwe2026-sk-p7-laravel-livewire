<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Memastikan akun admin bawaan (admin / admin, sesuai seeder) tersedia pada
 * database yang SUDAH berisi data (mis. server deploy) tetapi akun admin-nya
 * hilang atau sandinya berubah.
 *
 * - Database kosong dilewati: seeder (migrate:fresh --seed) yang membuat akun.
 * - Saat testing dilewati agar tidak mengganggu data uji.
 * - Tidak menyentuh stok maupun user lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing') || ! User::query()->exists()) {
            return;
        }

        $admin = User::firstOrNew(['username' => 'admin']);

        if (! $admin->exists) {
            $admin->name = 'Administrator';
            $admin->email = User::where('email', 'admin@example.com')->exists()
                ? 'admin.'.uniqid().'@example.com'
                : 'admin@example.com';
        }

        $admin->role = User::ROLE_ADMIN;
        $admin->password = 'admin';
        $admin->save();
    }

    public function down(): void
    {
        // Sengaja kosong: akun tidak dihapus saat rollback.
    }
};
