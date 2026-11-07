<div class="grid min-h-screen lg:grid-cols-2">
    <x-auth-aside title="Lihat sisa stok sekilas." text="Catat barang, pantau yang mulai menipis, dan ekspor datanya ke Excel kapan saja." />

    <section class="flex items-center justify-center px-6 py-12 sm:px-12">
        <div class="w-full max-w-sm">
            <div class="mb-10 lg:hidden"><x-brand :light="false" /></div>

            <h1 class="font-display text-3xl font-bold">Masuk</h1>
            <p class="mt-2 text-sm text-stone-600">Gunakan username atau email akun Anda.</p>

            <form wire:submit="submit" class="mt-8 space-y-5">
                <x-input label="Username atau email" name="login" id="login-username" wire:model="login" autocomplete="username" />
                <x-input label="Password" name="password" id="login-password" type="password" wire:model="password" autocomplete="current-password" />

                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-stone-300 text-[#9A5B33] focus:ring-[#9A5B33]">
                    Ingat saya
                </label>

                <button id="login-submit" type="submit" class="w-full rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#834A27] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#9A5B33]">
                    <span wire:loading.remove wire:target="submit">Masuk</span>
                    <span wire:loading wire:target="submit">Memeriksa...</span>
                </button>
            </form>

            <p class="mt-8 text-sm text-stone-600">Belum punya akun? <a href="/register" class="font-semibold text-[#9A5B33] hover:underline">Daftar sekarang</a></p>
        </div>
    </section>
</div>
