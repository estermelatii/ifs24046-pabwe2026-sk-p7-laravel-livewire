<?php

namespace App\Features\Stock\Policies;

use App\Features\Stock\Models\Stock;
use App\Models\User;

class StockPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Pemilik boleh melihat; admin boleh melihat (read-only). */
    public function view(User $user, Stock $stock): bool
    {
        return $user->isAdmin() || $stock->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isMember();
    }

    /** Hanya pemilik (member) yang boleh mengubah. Admin read-only. */
    public function update(User $user, Stock $stock): bool
    {
        return $user->isMember() && $stock->user_id === $user->id;
    }

    public function delete(User $user, Stock $stock): bool
    {
        return $this->update($user, $stock);
    }
}
