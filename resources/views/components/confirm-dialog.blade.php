@props(['show', 'title' => 'Hapus stok ini?', 'message' => 'Data yang dihapus tidak bisa dikembalikan.', 'confirm' => 'confirmDelete', 'cancel' => 'cancelDelete', 'name' => null])
@if ($show)
    <div class="fixed inset-0 z-[60] grid place-items-center p-4" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" x-data x-on:keydown.escape.window="$wire.{{ $cancel }}()">
        <div class="absolute inset-0 bg-[#2A1A11]/60 backdrop-blur-sm" wire:click="{{ $cancel }}"></div>
        <div class="relative w-full max-w-sm rounded-3xl bg-white p-7 text-center shadow-2xl">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-red-50 text-red-500 ring-8 ring-red-50/60"><x-icon name="trash" class="h-7 w-7" /></span>
            <h3 id="confirm-title" class="font-display mt-5 text-xl font-bold text-stone-900">{{ $title }}</h3>
            @if ($name)<p class="mt-2 rounded-lg bg-stone-100 px-3 py-1.5 text-sm font-semibold text-stone-700">{{ $name }}</p>@endif
            <p class="mt-3 text-sm leading-relaxed text-stone-500">{{ $message }}</p>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" wire:click="{{ $cancel }}" id="confirm-cancel" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">Batal</button>
                <button type="button" wire:click="{{ $confirm }}" id="confirm-delete" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">Ya, hapus</button>
            </div>
        </div>
    </div>
@endif
