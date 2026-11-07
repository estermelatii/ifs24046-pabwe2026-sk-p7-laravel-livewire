<div>
    {{-- Sapaan --}}
    <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm text-stone-600">Selamat datang kembali,</p>
            <h1 class="font-display mt-1 text-4xl font-semibold leading-tight">{{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">Begini kondisi stok barang Anda hari ini.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-[#EADCCF] bg-[#FFFCF9] px-4 py-2 text-sm font-medium">
            <x-icon name="calendar" class="h-4 w-4 text-[#9A5B33]" /> {{ now()->translatedFormat('j F Y') }}
        </div>
    </div>

    @php
        $restockCount = $summary['low_stock'] + $summary['out_of_stock'];
        $stats = [
            ['Jenis barang', number_format($summary['all'], 0, ',', '.'), 'cube', 'barang tercatat'],
            ['Total unit', number_format($insights['units'], 0, ',', '.'), 'squares', 'semua barang digabung'],
            ['Nilai persediaan', 'Rp '.number_format($insights['value'], 0, ',', '.'), 'banknotes', 'jumlah x harga'],
            ['Perlu diisi ulang', number_format($restockCount, 0, ',', '.'), 'exclamation', 'menipis atau habis'],
        ];
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($stats as [$label, $value, $icon, $hint])
            <div wire:key="stat-{{ $label }}" class="flex items-center gap-4 rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-5">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#EFE2D6] text-[#9A5B33]"><x-icon :name="$icon" class="h-6 w-6" /></span>
                <div class="min-w-0">
                    <p class="text-sm text-stone-600">{{ $label }}</p>
                    <p class="font-display break-words text-2xl font-semibold leading-tight tabular-nums">{{ $value }}</p>
                    <p class="text-xs text-stone-500">{{ $hint }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @php
        $tot = max($summary['all'], 1);
        $angleA = round($summary['available'] / $tot * 360, 1);
        $angleB = $angleA + round($summary['low_stock'] / $tot * 360, 1);
        $ring = $summary['all'] > 0
            ? "conic-gradient(#5E9B6B 0deg {$angleA}deg, #D9A441 {$angleA}deg {$angleB}deg, #C4553F {$angleB}deg 360deg)"
            : 'conic-gradient(#EFE2D6 0deg 360deg)';
        $legend = [['Tersedia', 'available', '#5E9B6B'], ['Menipis', 'low_stock', '#D9A441'], ['Habis', 'out_of_stock', '#C4553F']];
    @endphp
    <div class="mb-6 grid gap-6 lg:grid-cols-5">
        <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6 lg:col-span-2">
            <h2 class="font-display text-lg font-semibold">Komposisi stok</h2>
            <div class="mt-5 flex flex-wrap items-center gap-6">
                <div class="relative h-40 w-40 shrink-0 rounded-full" style="background: {{ $ring }}" role="img" aria-label="Diagram komposisi status stok">
                    <div class="absolute inset-[22px] grid place-items-center rounded-full bg-[#FFFCF9] text-center">
                        <div>
                            <p class="font-display text-3xl font-semibold leading-none">{{ $summary['all'] }}</p>
                            <p class="mt-1 text-xs text-stone-500">jenis barang</p>
                        </div>
                    </div>
                </div>
                <ul class="space-y-3 text-sm">
                    @foreach ($legend as [$label, $key, $color])
                        <li class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $color }}"></span>
                            <span class="w-16">{{ $label }}</span>
                            <span class="font-semibold tabular-nums">{{ $summary[$key] }}</span>
                            <span class="text-stone-500">({{ $summary['all'] > 0 ? round($summary[$key] / $summary['all'] * 100) : 0 }}%)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6 lg:col-span-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-lg font-semibold">Perlu diisi ulang</h2>
                <span class="text-xs text-stone-500">Jumlah paling sedikit di atas</span>
            </div>
            <div class="mt-3">
                @forelse ($insights['restock'] as $item)
                    <a href="/stocks/{{ $item->id }}" wire:key="restock-{{ $item->id }}" class="flex items-center gap-4 border-b border-[#EFE2D6] py-3 last:border-0">
                        @if ($item->coverImageUrl())
                            <img src="{{ $item->coverImageUrl() }}" alt="" class="h-11 w-11 rounded-xl object-cover">
                        @else
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#EFE2D6] text-stone-500"><x-icon name="cube" class="h-5 w-5" /></span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-semibold">{{ $item->name }}</span>
                            <span class="block text-xs text-stone-500">Sisa {{ $item->quantity }} {{ $item->unit }}, batas {{ $item->min_stock }}</span>
                        </span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-emerald-700"><x-icon name="trophy" class="h-6 w-6" /></span>
                        <p class="font-semibold">Belum ada yang perlu diisi ulang</p>
                        <p class="mt-1 text-sm text-stone-500">Barang yang menipis atau habis akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @php
        $bannerTitle = $summary['all'] === 0
            ? 'Mulai dari barang pertama Anda'
            : ($restockCount > 0 ? "Ada {$restockCount} barang yang perlu perhatian" : 'Semua stok aman. Kerja bagus!');
        $bannerText = $summary['all'] === 0
            ? 'Tambahkan barang, impor dari Excel, atau minta AI membuatkan contoh.'
            : ($restockCount > 0 ? 'Cek daftar di atas dan isi ulang yang sudah habis lebih dulu.' : 'Terus perbarui jumlah barang agar catatan tetap akurat.');
    @endphp
    <div class="mb-10 flex items-center gap-4 rounded-2xl bg-gradient-to-r from-[#3A2418] to-[#7A4A2E] px-6 py-5 text-white">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-white/10"><x-icon name="trophy" class="h-6 w-6 text-[#E9B872]" /></span>
        <div>
            <p class="font-display text-lg font-semibold">{{ $bannerTitle }}</p>
            <p class="text-sm text-stone-300">{{ $bannerText }}</p>
        </div>
    </div>

    {{-- Daftar barang --}}
    <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-2xl font-semibold">Daftar barang</h2>
            <p class="mt-1 text-sm text-stone-600">Cari, ubah, atau sesuaikan jumlah tiap barang.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="create" id="stock-create" class="inline-flex items-center gap-2 rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#834A27] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#9A5B33]">
                <x-icon name="plus" class="h-4 w-4" /> Tambah barang
            </button>
            <button wire:click="openAi" class="inline-flex items-center gap-2 rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#9A5B33] hover:text-[#9A5B33]">
                <x-icon name="sparkles" class="h-4 w-4" /> Buat dengan AI
            </button>
            <button wire:click="export" class="inline-flex items-center gap-2 rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#9A5B33] hover:text-[#9A5B33]">
                <x-icon name="download" class="h-4 w-4" /> Ekspor Excel
            </button>
        </div>
    </div>

    @php
        $chips = [
            ['', 'Semua', $summary['all'], 'bg-stone-400'],
            ['available', 'Tersedia', $summary['available'], 'bg-emerald-500'],
            ['low_stock', 'Menipis', $summary['low_stock'], 'bg-amber-500'],
            ['out_of_stock', 'Habis', $summary['out_of_stock'], 'bg-red-500'],
        ];
    @endphp
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($chips as [$value, $label, $count, $dot])
            <button type="button" wire:click="$set('statusFilter', '{{ $value }}')" wire:key="chip-{{ $label }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium transition-colors',
                        'border-[#3A2418] bg-[#3A2418] text-white' => $statusFilter === $value,
                        'border-[#EADCCF] bg-[#FFFCF9] hover:border-stone-400' => $statusFilter !== $value,
                    ])>
                <span class="h-2 w-2 rounded-full {{ $dot }}"></span>{{ $label }}<span class="tabular-nums opacity-70">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU"
                   class="w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] py-2.5 pl-9 pr-3 text-sm placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
        </div>
        <form wire:submit="import" class="flex items-center gap-2">
            <input type="file" wire:model="importFile" accept=".xlsx"
                   class="text-sm text-stone-600 file:mr-3 file:rounded-md file:border-0 file:bg-stone-200 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#3A2418] hover:file:bg-stone-300">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-3 py-2 text-sm font-medium transition-colors hover:border-[#9A5B33] hover:text-[#9A5B33]">
                <x-icon name="upload" class="h-4 w-4" /> Impor
            </button>
        </form>
    </div>
    @error('importFile') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="overflow-x-auto rounded-2xl border border-[#EADCCF] bg-[#FFFCF9]">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="border-b border-[#EADCCF] bg-[#FAF3EC] text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Barang</th>
                    <th class="px-5 py-3 font-medium">Jumlah</th>
                    <th class="px-5 py-3 font-medium">Harga</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
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
                        <a href="/stocks/{{ $stock->id }}" class="group flex items-center gap-3">
                            @if ($stock->coverImageUrl())
                                <img src="{{ $stock->coverImageUrl() }}" alt="" class="h-11 w-11 rounded-xl object-cover">
                            @else
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#EFE2D6] text-stone-500"><x-icon name="cube" class="h-5 w-5" /></span>
                            @endif
                            <span>
                                <span class="block font-semibold group-hover:text-[#9A5B33]">{{ $stock->name }}</span>
                                <span class="block text-xs text-stone-500">{{ $stock->sku ?? 'Tanpa SKU' }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <button wire:click="adjust({{ $stock->id }}, -1)" class="grid h-7 w-7 place-items-center rounded-md border border-[#DCC9B8] hover:bg-stone-100" aria-label="Kurangi satu">
                                <x-icon name="minus" class="h-3.5 w-3.5" />
                            </button>
                            <span class="min-w-[4.5rem] text-center font-semibold tabular-nums">{{ $stock->quantity }} <span class="font-normal text-stone-500">{{ $stock->unit }}</span></span>
                            <button wire:click="adjust({{ $stock->id }}, 1)" class="grid h-7 w-7 place-items-center rounded-md border border-[#DCC9B8] hover:bg-stone-100" aria-label="Tambah satu">
                                <x-icon name="plus" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="mt-2 h-1.5 w-36 rounded-full bg-stone-200" title="Batas menipis: {{ $stock->min_stock }}">
                            <div class="h-1.5 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </td>
                    <td class="px-5 py-4 tabular-nums">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                    <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-1">
                            <button wire:click="edit({{ $stock->id }})" class="rounded-md p-2 text-stone-500 hover:bg-stone-200 hover:text-[#3A2418]" aria-label="Ubah {{ $stock->name }}" title="Ubah">
                                <x-icon name="pencil" class="h-4 w-4" />
                            </button>
                            <button wire:click="askDelete({{ $stock->id }})" class="rounded-md p-2 text-stone-500 hover:bg-red-50 hover:text-red-600" aria-label="Hapus {{ $stock->name }}" title="Hapus">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center">
                        <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-[#EFE2D6] text-stone-500"><x-icon name="cube" class="h-6 w-6" /></span>
                        <p class="font-semibold">Belum ada barang di sini</p>
                        <p class="mt-1 text-sm text-stone-500">Tambahkan barang pertama, impor dari Excel, atau minta AI membuatkan contoh.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $stocks->links() }}</div>

    @if ($showForm)
        <div x-data @keydown.escape.window="$wire.closeForm()" class="fixed inset-0 z-40 flex items-center justify-center bg-[#3A2418]/60 p-4 backdrop-blur-sm">
            <form wire:submit="save" class="max-h-full w-full max-w-xl overflow-y-auto rounded-2xl bg-[#FFFCF9] p-6 sm:p-7">
                <div class="mb-5 flex items-start justify-between">
                    <h2 class="font-display text-xl font-bold">{{ $editingId ? 'Ubah barang' : 'Tambah barang' }}</h2>
                    <button type="button" wire:click="closeForm" class="rounded-md p-1.5 text-stone-500 hover:bg-stone-100" aria-label="Tutup"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                @include('livewire.stock._form-fields')
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeForm" class="rounded-xl border border-[#DCC9B8] px-4 py-2.5 text-sm font-medium hover:bg-stone-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showAi)
        <div x-data @keydown.escape.window="$wire.closeAi()" class="fixed inset-0 z-40 flex items-center justify-center bg-[#3A2418]/60 p-4 backdrop-blur-sm">
            <div class="max-h-full w-full max-w-2xl overflow-y-auto rounded-2xl bg-[#FFFCF9] p-6 sm:p-7">
                <div class="mb-1 flex items-start justify-between">
                    <h2 class="font-display text-xl font-bold">Buat barang dengan AI</h2>
                    <button type="button" wire:click="closeAi" class="rounded-md p-1.5 text-stone-500 hover:bg-stone-100" aria-label="Tutup"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                <p class="mb-5 text-sm text-stone-600">Tulis jenis barangnya, lalu pilih hasil yang ingin disimpan.</p>

                <form wire:submit="generate" class="flex flex-wrap gap-2">
                    <input type="text" wire:model="aiPrompt" placeholder="Contoh: perlengkapan kantor"
                           class="min-w-0 flex-1 rounded-xl border border-[#DCC9B8] px-3.5 py-2.5 text-sm placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
                    <input type="number" wire:model="aiCount" min="1" max="20" aria-label="Jumlah item"
                           class="w-20 rounded-xl border border-[#DCC9B8] px-3 py-2.5 text-sm focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25">
                    <button type="submit" wire:loading.attr="disabled" wire:target="generate" class="inline-flex items-center gap-2 rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27] disabled:opacity-60">
                        <x-icon name="sparkles" class="h-4 w-4" />
                        <span wire:loading.remove wire:target="generate">Buat</span>
                        <span wire:loading wire:target="generate">Memproses...</span>
                    </button>
                </form>
                @error('aiPrompt') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('aiCount') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                @if ($aiResults)
                    <ul class="mt-5 divide-y divide-[#EFE2D6] rounded-xl border border-[#EADCCF] text-sm">
                        @foreach ($aiResults as $i => $item)
                            <li class="flex items-start gap-3 p-3.5" wire:key="ai-{{ $i }}">
                                <input type="checkbox" wire:model="aiSelected" value="{{ $i }}" class="mt-1 h-4 w-4 rounded border-stone-300 text-[#9A5B33] focus:ring-[#9A5B33]">
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $item['name'] }} <span class="font-normal text-stone-500">({{ $item['quantity'] }} {{ $item['unit'] }})</span></p>
                                    <p class="text-stone-500">{{ $item['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeAi" class="rounded-xl border border-[#DCC9B8] px-4 py-2.5 text-sm font-medium hover:bg-stone-50">Tutup</button>
                    @if ($aiResults)
                        <button type="button" wire:click="saveGenerated" class="rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">Simpan yang dipilih</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <x-confirm-dialog :show="$deletingId !== null" :name="$deletingName" />
</div>
