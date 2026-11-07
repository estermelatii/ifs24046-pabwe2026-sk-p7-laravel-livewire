<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500..700&family=Instrument+Sans:wght@400..600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Fraunces', 'Instrument Sans', ui-sans-serif, sans-serif; letter-spacing: -0.02em; }
        [x-cloak] { display: none !important; }
        .rounded-2xl.border { box-shadow: 0 2px 16px rgba(90,55,35,.07); }
        .md-body { line-height: 1.7; color: #5a463b; }
        .md-body > * + * { margin-top: .75rem; }
        .md-body h1, .md-body h2, .md-body h3 { font-family: 'Fraunces', sans-serif; font-weight: 700; color: #3A2418; line-height: 1.25; }
        .md-body h1 { font-size: 1.35rem; } .md-body h2 { font-size: 1.15rem; } .md-body h3 { font-size: 1rem; }
        .md-body ul { list-style: disc; padding-left: 1.25rem; }
        .md-body ol { list-style: decimal; padding-left: 1.25rem; }
        .md-body strong { color: #3A2418; }
        .md-body a { color: #9A5B33; text-decoration: underline; }
        .md-body code { background: #EFE2D6; padding: .1rem .35rem; border-radius: .25rem; font-size: .875em; }
        .md-body blockquote { border-left: 3px solid #E9B872; padding-left: .9rem; color: #7a6559; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen bg-[#F5ECE3] text-[#3A2418] antialiased">
@auth
    @php
        $me = auth()->user();
        $nav = $me->isAdmin()
            ? [['/admin/users', 'Pengguna', 'users', 'admin/users*']]
            : [['/stocks', 'Stok barang', 'cube', 'stocks*']];
        $nav[] = ['/profile', 'Profil', 'user-circle', 'profile'];
    @endphp

    <div x-data="{ open: false }" class="min-h-screen lg:flex">
        <header class="flex items-center justify-between bg-[#3A2418] px-4 py-3 lg:hidden">
            <x-brand />
            <button type="button" @click="open = true" class="rounded-xl p-2 text-white hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-[#E9B872]" aria-label="Buka menu">
                <x-icon name="menu" class="h-6 w-6" />
            </button>
        </header>

        <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        <aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-gradient-to-b from-[#3A2418] to-[#5B3A29] px-4 py-6 text-stone-300 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="mb-8 flex items-center justify-between px-2">
                <x-brand />
                <button type="button" @click="open = false" class="rounded-xl p-1.5 text-stone-300 hover:bg-white/10 lg:hidden" aria-label="Tutup menu">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex flex-1 flex-col gap-1" aria-label="Menu utama">
                @foreach ($nav as [$href, $label, $icon, $pattern])
                    @php($active = request()->is($pattern))
                    <a href="{{ $href }}" @class([
                        'flex items-center gap-3 rounded-xl border-l-[3px] px-3 py-2.5 text-sm font-medium transition-colors',
                        'border-[#E9B872] bg-white/10 text-white' => $active,
                        'border-transparent hover:bg-white/5 hover:text-white' => ! $active,
                    ])>
                        <x-icon :name="$icon" class="h-5 w-5" />
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 pt-4">
                <div class="mb-3 flex items-center gap-3 px-2">
                    @if ($me->profilePhotoUrl())
                        <img src="{{ $me->profilePhotoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                    @else
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-[#E9B872] font-semibold text-[#3A2418]">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ $me->name }}</p>
                        <p class="text-xs text-stone-400">{{ $me->isAdmin() ? 'Admin' : 'Member' }}</p>
                    </div>
                </div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" id="logout-button" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-300 transition-colors hover:bg-white/5 hover:text-white focus-visible:outline-2 focus-visible:outline-[#E9B872]">
                        <x-icon name="logout" class="h-5 w-5" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 sm:px-8 lg:px-10 lg:py-9">
            @if (session('status'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-transition.opacity role="status"
                     class="fixed right-4 top-4 z-50 max-w-sm rounded-xl border border-emerald-200 bg-[#FFFCF9] px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg">
                    {{ session('status') }}
                </div>
            @endif
            <div class="mx-auto max-w-6xl">
                {{ $slot }}
            </div>
        </main>
    </div>
@else
    <main class="min-h-screen">
        {{ $slot }}
    </main>
@endauth
@livewireScripts
</body>
</html>
