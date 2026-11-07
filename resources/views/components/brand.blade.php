@props(['light' => true])
<div class="flex items-center gap-2.5">
    <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#E9B872] text-[#3A2418]">
        <x-icon name="cube" class="h-5 w-5" />
    </span>
    <span class="font-display text-xl font-bold {{ $light ? 'text-white' : 'text-[#3A2418]' }}">Stok Barang</span>
</div>
