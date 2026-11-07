@props(['label', 'name', 'type' => 'text', 'id' => null])
@php($inputId = $id ?? 'f-'.$name)
<div>
    <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-[#3A2418]">{{ $label }}</label>
    <input id="{{ $inputId }}" type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-[#DCC9B8] bg-[#FFFCF9] px-3.5 py-2.5 text-sm text-[#3A2418] placeholder:text-stone-400 focus:border-[#9A5B33] focus:outline-none focus:ring-2 focus:ring-[#9A5B33]/25']) }}>
    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
