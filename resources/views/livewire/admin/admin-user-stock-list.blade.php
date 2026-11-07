<div>
    <a href="/admin/users" class="inline-flex items-center gap-2 text-sm font-medium text-stone-600 hover:text-[#9A5B33]">
        <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke pengguna
    </a>

    <div class="mb-8 mt-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-bold">Stok milik {{ $user->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">&#64;{{ $user->username }}</p>
        </div>
        <span class="inline-flex rounded-full bg-[#E9B872]/25 px-3 py-1 text-xs font-semibold text-[#7a5600]">Mode lihat saja</span>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <div class="relative w-full sm:w-80">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU"
                   class="w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] py-2.5 pl-9 pr-3 text-sm placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
        </div>
        <select wire:model.live="status" aria-label="Filter status" class="rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-3 py-2.5 text-sm focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
            <option value="">Semua status</option>
            @foreach (\App\Features\Stock\Enums\StockStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-[#EADCCF] bg-[#FFFCF9]">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-[#EADCCF] bg-[#FAF3EC] text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Barang</th>
                    <th class="px-5 py-3 font-medium">Jumlah</th>
                    <th class="px-5 py-3 font-medium">Harga</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFE2D6]">
            @forelse ($stocks as $stock)
                @php
                    $pct = $stock->min_stock > 0
                        ? min(100, (int) round($stock->quantity / ($stock->min_stock * 3) * 100))
                        : ($stock->quantity > 0 ? 100 : 0);
                    $bar = match ($stock->status->value) {
                        'available' => 'bg-emerald-500',
                        'low_stock' => 'bg-amber-500',
                        default => 'bg-red-500',
                    };
                @endphp
                <tr wire:key="stock-{{ $stock->id }}" class="transition-colors hover:bg-[#FAF3EC]">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if ($stock->coverImageUrl())
                                <img src="{{ $stock->coverImageUrl() }}" alt="" class="h-11 w-11 rounded-xl object-cover">
                            @else
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#EFE2D6] text-stone-500"><x-icon name="cube" class="h-5 w-5" /></span>
                            @endif
                            <div>
                                <p class="font-semibold">{{ $stock->name }}</p>
                                <p class="text-xs text-stone-500">{{ $stock->sku ?? 'Tanpa SKU' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-semibold tabular-nums">{{ $stock->quantity }} <span class="font-normal text-stone-500">{{ $stock->unit }}</span></p>
                        <div class="mt-2 h-1.5 w-36 rounded-full bg-stone-200"><div class="h-1.5 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div></div>
                    </td>
                    <td class="px-5 py-4 tabular-nums">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                    <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-14 text-center">
                        <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-[#EFE2D6] text-stone-500"><x-icon name="cube" class="h-6 w-6" /></span>
                        <p class="font-semibold">Belum ada barang</p>
                        <p class="mt-1 text-sm text-stone-500">Member ini belum mencatat stok, atau tidak ada yang cocok dengan filter.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $stocks->links() }}</div>
</div>
