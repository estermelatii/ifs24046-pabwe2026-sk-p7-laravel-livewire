<?php

use App\Features\Auth\Actions\EnsureDefaultAccountsAction;
use Illuminate\Database\Migrations\Migration;

/**
 * Memastikan akun admin (admin / admin) dan member (member / member) selalu ada,
 * juga pada database kosong di server deploy. Dilewati saat testing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        app(EnsureDefaultAccountsAction::class)->execute();
    }

    public function down(): void
    {
        // Sengaja kosong: akun tidak dihapus saat rollback.
    }
};
