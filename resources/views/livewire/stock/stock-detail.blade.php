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
<div>
    <a href="/stocks" class="inline-flex items-center gap-2 text-sm font-medium text-stone-600 hover:text-[#9A5B33]">
        <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar
    </a>

    <div class="mt-4 grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] lg:col-span-2">
            @if ($stock->coverImageUrl())
                <img src="{{ $stock->coverImageUrl() }}" alt="Cover {{ $stock->name }}" class="h-64 w-full object-cover">
            @else
                <div class="grid h-40 place-items-center bg-[#EFE2D6] text-stone-400"><x-icon name="photo" class="h-10 w-10" /></div>
            @endif

            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="font-display text-3xl font-bold">{{ $stock->name }}</h1>
                        <p class="mt-1 text-sm text-stone-500">SKU: {{ $stock->sku ?? 'belum diisi' }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
                </div>

                @if ($editing)
                    <form wire:submit="save" class="mt-6 border-t border-[#EFE2D6] pt-6">
                        @include('livewire.stock._form-fields', ['editor' => true])
                        <div class="mt-5 flex gap-2">
                            <button type="submit" class="rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">Simpan perubahan</button>
                            <button type="button" wire:click="cancelEdit" class="rounded-xl border border-[#DCC9B8] px-4 py-2.5 text-sm font-medium hover:bg-stone-50">Batal</button>
                        </div>
                    </form>
                @else
                    <div class="mt-6 border-t border-[#EFE2D6] pt-6">
                        <h2 class="mb-3 font-display text-lg font-bold">Deskripsi</h2>
                        @if ($stock->description)
                            <div class="md-body text-sm">{!! $descriptionHtml !!}</div>
                        @else
                            <p class="text-sm text-stone-500">Belum ada deskripsi untuk barang ini.</p>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6">
                <h2 class="text-sm font-medium text-stone-600">Jumlah di gudang</h2>
                <p class="font-display mt-1 text-4xl font-bold tabular-nums">{{ $stock->quantity }} <span class="text-lg font-medium text-stone-500">{{ $stock->unit }}</span></p>

                <div class="mt-4 h-2 rounded-full bg-stone-200">
                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                </div>
                <p class="mt-2 text-xs text-stone-500">Dianggap menipis di {{ $stock->min_stock }} {{ $stock->unit }} atau kurang.</p>

                <div class="mt-5 flex items-center gap-2">
                    <button wire:click="adjust(-1)" class="grid h-10 w-10 place-items-center rounded-xl border border-[#DCC9B8] hover:bg-stone-100" aria-label="Kurangi satu"><x-icon name="minus" class="h-4 w-4" /></button>
                    <button wire:click="adjust(1)" class="grid h-10 w-10 place-items-center rounded-xl border border-[#DCC9B8] hover:bg-stone-100" aria-label="Tambah satu"><x-icon name="plus" class="h-4 w-4" /></button>
                    <span class="ml-auto text-sm font-semibold tabular-nums">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : 'Harga belum diisi' }}</span>
                </div>
            </section>

            <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6">
                <h2 class="mb-3 text-sm font-medium text-stone-600">Kelola barang</h2>
                @unless ($editing)
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="startEdit" class="inline-flex items-center gap-2 rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">
                            <x-icon name="pencil" class="h-4 w-4" /> Ubah
                        </button>
                        <button wire:click="askDelete" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                            <x-icon name="trash" class="h-4 w-4" /> Hapus
                        </button>
                    </div>
                @else
                    <p class="text-sm text-stone-500">Sedang mengubah. Simpan atau batalkan di kolom kiri.</p>
                @endunless
            </section>

            <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6">
                <h2 class="mb-3 text-sm font-medium text-stone-600">Foto cover</h2>
                <form wire:submit="saveCover" class="space-y-3">
                    <input type="file" wire:model="cover" accept="image/*" aria-label="Pilih foto cover stok"
                           class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-md file:border-0 file:bg-stone-200 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#3A2418] hover:file:bg-stone-300">
                    @error('cover') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-[#DCC9B8] px-3.5 py-2 text-sm font-medium hover:border-[#9A5B33] hover:text-[#9A5B33]">
                            <x-icon name="upload" class="h-4 w-4" /> Unggah cover
                        </button>
                        @if ($stock->cover_image_path)
                            <button type="button" wire:click="removeCover" class="text-sm font-medium text-red-600 hover:underline">Lepas cover</button>
                        @endif
                    </div>
                </form>
            </section>
        </div>
    </div>

    <x-confirm-dialog :show="$confirmingDelete" :name="$stock->name" />
</div>
