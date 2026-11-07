@props(['model' => 'description', 'label' => 'Deskripsi'])
{{-- Editor Markdown Toast UI. Jika JS belum di-build, otomatis jatuh ke textarea biasa. --}}
<div wire:ignore
     x-data="{ editor: null, fallback: false }"
     x-init="
        if (window.ToastEditor) {
            editor = new window.ToastEditor({
                el: $refs.host,
                height: '320px',
                initialEditType: 'markdown',
                previewStyle: 'tab',
                usageStatistics: false,
                placeholder: 'Tulis deskripsi dengan Markdown...',
                initialValue: $wire.{{ $model }} ?? '',
            });
            editor.on('change', () => { $wire.{{ $model }} = editor.getMarkdown(); });
        } else {
            fallback = true;
        }
     ">
    <label class="mb-1.5 block text-sm font-medium text-[#3A2418]">{{ $label }}</label>
    <div id="markdown-editor" x-ref="host" x-show="! fallback"></div>
    <textarea x-show="fallback" x-cloak wire:model="{{ $model }}" rows="6"
              placeholder="Boleh pakai Markdown, misalnya **tebal** atau daftar berpoin."
              class="w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-3.5 py-2.5 font-mono text-sm text-[#3A2418] placeholder:font-sans placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25"></textarea>
</div>
