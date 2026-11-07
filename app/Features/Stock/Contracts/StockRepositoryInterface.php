<?php

namespace App\Features\Stock\Contracts;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StockRepositoryInterface
{
    public function paginateForUser(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator;

    public function allForUser(User $user): Collection;

    public function find(int $id): ?Stock;

    public function create(array $data): Stock;

    public function update(Stock $stock, array $data): Stock;

    public function delete(Stock $stock): bool;
}
