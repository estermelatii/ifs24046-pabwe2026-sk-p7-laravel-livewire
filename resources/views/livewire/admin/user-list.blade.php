<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold">Pengguna</h1>
        <p class="mt-1 text-sm text-stone-600">Semua akun terdaftar. Pilih member untuk melihat stok miliknya.</p>
    </div>

    <div class="relative mb-4 w-full sm:w-80">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, username, atau email"
               class="w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] py-2.5 pl-9 pr-3 text-sm placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
    </div>

    <div class="overflow-x-auto rounded-2xl border border-[#EADCCF] bg-[#FFFCF9]">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-[#EADCCF] bg-[#FAF3EC] text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Pengguna</th>
                    <th class="px-5 py-3 font-medium">Role</th>
                    <th class="px-5 py-3 font-medium">Jumlah barang</th>
                    <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFE2D6]">
            @forelse ($users as $u)
                <tr wire:key="user-{{ $u->id }}" class="transition-colors hover:bg-[#FAF3EC]">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if ($u->profilePhotoUrl())
                                <img src="{{ $u->profilePhotoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-[#EFE2D6] font-semibold">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                            @endif
                            <div>
                                <p class="font-semibold">{{ $u->name }}</p>
                                <p class="text-xs text-stone-500">&#64;{{ $u->username }} - {{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-[#E9B872]/25 text-[#7a5600]' => $u->isAdmin(),
                            'bg-[#EFE2D6] text-[#3A2418]' => ! $u->isAdmin(),
                        ])>{{ $u->isAdmin() ? 'Admin' : 'Member' }}</span>
                    </td>
                    <td class="px-5 py-4 tabular-nums">{{ $u->stocks_count }}</td>
                    <td class="px-5 py-4 text-right">
                        @if ($u->isMember())
                            <a href="/admin/users/{{ $u->id }}/stocks" class="inline-flex items-center gap-2 rounded-xl border border-[#DCC9B8] px-3 py-1.5 text-sm font-medium hover:border-[#9A5B33] hover:text-[#9A5B33]">
                                <x-icon name="cube" class="h-4 w-4" /> Lihat stok
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-14 text-center">
                        <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-[#EFE2D6] text-stone-500"><x-icon name="users" class="h-6 w-6" /></span>
                        <p class="font-semibold">Tidak ada pengguna yang cocok</p>
                        <p class="mt-1 text-sm text-stone-500">Coba kata kunci lain.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
