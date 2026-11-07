<?php

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar stok milik satu user. READ-ONLY: tidak ada aksi ubah/hapus.
 */
#[Layout('components.layouts.app')]
class AdminUserStockList extends Component
{
    use WithPagination;

    public User $user;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(AdminUserService $service): View
    {
        return view('livewire.admin.admin-user-stock-list', [
            'stocks' => $service->stocksOf($this->user, $this->search ?: null, $this->status ?: null),
        ]);
    }
}
