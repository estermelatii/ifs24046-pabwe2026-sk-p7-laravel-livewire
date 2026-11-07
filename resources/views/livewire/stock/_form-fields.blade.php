<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input label="Nama barang" name="name" wire:model="name" />
    </div>
    <x-input label="SKU" name="sku" wire:model="sku" />
    <x-input label="Satuan" name="unit" wire:model="unit" />
    <x-input label="Jumlah" name="quantity" type="number" min="0" step="1" wire:model="quantity" />
    <x-input label="Batas stok menipis" name="min_stock" type="number" min="0" step="1" wire:model="min_stock" />
    <div class="sm:col-span-2">
        <x-input label="Harga (Rp)" name="price" type="number" min="0" step="any" wire:model="price" />
    </div>
    <div class="sm:col-span-2">
        @if (! empty($editor))
            {{-- Halaman detail: editor Markdown Toast UI --}}
            <x-markdown-editor model="description" label="Deskripsi" />
        @else
            <label for="f-description" class="mb-1.5 block text-sm font-medium text-[#3A2418]">Deskripsi</label>
            <textarea id="f-description" wire:model="description" rows="4" placeholder="Boleh pakai Markdown, misalnya **tebal** atau daftar berpoin."
                      class="w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-3.5 py-2.5 font-mono text-sm text-[#3A2418] placeholder:font-sans placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25"></textarea>
        @endif
        @error('description') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
<p class="mt-3 text-xs text-stone-500">Status (tersedia, menipis, habis) dihitung otomatis dari jumlah dan batas stok menipis.</p>
