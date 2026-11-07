<div class="grid min-h-screen lg:grid-cols-2">
    <x-auth-aside title="Mulai catat stok Anda." text="Buat akun member, tambahkan barang pertama, dan biarkan status stok terhitung otomatis." />

    <section class="flex items-center justify-center px-6 py-12 sm:px-12">
        <div class="w-full max-w-sm">
            <div class="mb-10 lg:hidden"><x-brand :light="false" /></div>

            <h1 class="font-display text-3xl font-bold">Buat akun</h1>
            <p class="mt-2 text-sm text-stone-600">Hanya butuh satu menit.</p>

            <form wire:submit="submit" class="mt-8 space-y-5">
                <x-input label="Nama lengkap" name="name" id="register-name" wire:model="name" autocomplete="name" />
                <x-input label="Username" name="username" id="register-username" wire:model="username" autocomplete="username" />
                <x-input label="Email" name="email" id="register-email" type="email" wire:model="email" autocomplete="email" />
                <x-input label="Password" name="password" id="register-password" type="password" wire:model="password" autocomplete="new-password" />
                <x-input label="Ulangi password" name="password_confirmation" id="register-password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" />

                <button id="register-submit" type="submit" class="w-full rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#834A27] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#9A5B33]">
                    <span wire:loading.remove wire:target="submit">Daftar</span>
                    <span wire:loading wire:target="submit">Menyimpan...</span>
                </button>
            </form>

            <p class="mt-8 text-sm text-stone-600">Sudah punya akun? <a href="/login" class="font-semibold text-[#9A5B33] hover:underline">Masuk</a></p>
        </div>
    </section>
</div>
