<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold">Profil</h1>
        <p class="mt-1 text-sm text-stone-600">Atur data diri, foto, dan password akun Anda.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="h-fit rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6 text-center">
            @if ($user->profilePhotoUrl())
                <img src="{{ $user->profilePhotoUrl() }}" alt="Foto profil" class="mx-auto h-28 w-28 rounded-full object-cover">
            @else
                <span class="font-display mx-auto grid h-28 w-28 place-items-center rounded-full bg-[#E9B872] text-4xl font-bold text-[#3A2418]">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif
            <p class="font-display mt-4 text-xl font-bold">{{ $user->name }}</p>
            <p class="text-sm text-stone-500">&#64;{{ $user->username }}</p>
            <span class="mt-2 inline-flex rounded-full bg-[#EFE2D6] px-3 py-1 text-xs font-semibold">{{ $user->isAdmin() ? 'Admin' : 'Member' }}</span>

            <form wire:submit="savePhoto" class="mt-6 space-y-3 border-t border-[#EFE2D6] pt-5 text-left">
                <input type="file" wire:model="photo" accept="image/*"
                       class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-md file:border-0 file:bg-stone-200 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#3A2418] hover:file:bg-stone-300">
                @error('photo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <button type="submit" class="w-full rounded-xl border border-[#DCC9B8] px-3.5 py-2 text-sm font-medium hover:border-[#9A5B33] hover:text-[#9A5B33]">Simpan foto</button>
            </form>
        </section>

        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6 sm:p-7">
                <h2 class="font-display text-lg font-bold">Data diri</h2>
                <form wire:submit="saveProfile" class="mt-5 space-y-4">
                    <x-input label="Nama lengkap" name="name" id="profile-name" wire:model="name" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Username" name="username" id="profile-username" wire:model="username" />
                        <x-input label="Email" name="email" id="profile-email" type="email" wire:model="email" />
                    </div>
                    <button type="submit" class="rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">Simpan data diri</button>
                </form>
            </section>

            <section class="rounded-2xl border border-[#EADCCF] bg-[#FFFCF9] p-6 sm:p-7">
                <h2 class="font-display text-lg font-bold">Ganti password</h2>
                <form wire:submit="savePassword" class="mt-5 space-y-4">
                    <x-input label="Password saat ini" name="current_password" id="profile-current" type="password" wire:model="current_password" autocomplete="current-password" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Password baru" name="new_password" id="profile-new" type="password" wire:model="new_password" autocomplete="new-password" />
                        <x-input label="Ulangi password baru" name="new_password_confirmation" id="profile-new-confirm" type="password" wire:model="new_password_confirmation" autocomplete="new-password" />
                    </div>
                    <button type="submit" class="rounded-xl bg-[#9A5B33] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#834A27]">Ganti password</button>
                </form>
            </section>
        </div>
    </div>
</div>
