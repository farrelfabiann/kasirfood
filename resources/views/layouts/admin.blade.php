<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#d92d0b">
    <title>@yield('title', 'Admin') · {{ config('app.frontend_name', 'KlikKluk') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $navItems = [
        [
            'route' => 'admin.dashboard',
            'label' => 'Dashboard',
            'icon' => 'chart',
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'route' => 'admin.orders.index',
            'label' => 'Pesanan',
            'icon' => 'receipt',
            'active' => request()->routeIs('admin.orders.*'),
        ],
        [
            'route' => 'admin.products.index',
            'label' => 'Menu',
            'icon' => 'cube',
            'active' => request()->routeIs('admin.products.*'),
        ],
        [
            'route' => 'admin.categories.index',
            'label' => 'Kategori',
            'icon' => 'tag',
            'active' => request()->routeIs('admin.categories.*'),
        ],
    ];
@endphp
<body class="min-h-full bg-stone-100 font-sans text-stone-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Backdrop mobile --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-30 bg-stone-950/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-stone-200 bg-white transition-transform duration-200 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-3 border-b border-stone-100 px-5 py-5">
                <x-application-logo class="h-11 w-11 shrink-0 text-brand-600" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-display text-base font-extrabold tracking-tight text-stone-900">
                        {{ config('app.frontend_name', 'KlikKluk') }}
                    </p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Admin Panel</p>
                </div>
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-stone-100 lg:hidden"
                    @click="sidebarOpen = false"
                    aria-label="Tutup menu"
                >
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Manajemen</p>

                @foreach ($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-semibold transition',
                            'bg-brand-600 text-white shadow-card' => $item['active'],
                            'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! $item['active'],
                        ])
                    >
                        <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <p class="px-3 pb-2 pt-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Kiosk</p>

                <a
                    href="{{ route('kiosk.index') }}"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900"
                >
                    <x-icon name="home" class="h-5 w-5 shrink-0" />
                    <span class="flex-1">Buka Kiosk</span>
                    <x-icon name="arrow-right" class="h-4 w-4 rotate-45 text-stone-400" />
                </a>
            </nav>

            <div class="border-t border-stone-100 p-3">
                <div class="flex items-center gap-3 rounded-2xl bg-stone-50 px-3.5 py-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-stone-900 text-white">
                        <x-icon name="user" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-stone-900">{{ auth()->user()?->name }}</p>
                        <p class="truncate text-[11px] text-stone-500">{{ auth()->user()?->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-semibold text-stone-500 transition hover:bg-red-50 hover:text-red-600">
                        <x-icon name="logout" class="h-5 w-5 shrink-0" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-stone-200/80 bg-stone-100/85 backdrop-blur-xl">
                <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 shadow-sm transition hover:border-stone-300 lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Buka menu"
                    >
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-600">@yield('eyebrow', 'Admin Panel')</p>
                        <h1 class="truncate font-display text-lg font-extrabold tracking-tight text-stone-900 sm:text-xl">
                            @yield('heading', 'Dashboard')
                        </h1>
                    </div>

                    @hasSection('actions')
                        <div class="flex shrink-0 items-center gap-2">
                            @yield('actions')
                        </div>
                    @endif
                </div>
            </header>

            <main class="flex-1 px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                        <span class="flex-1">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                        <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                        <span class="flex-1">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="flex items-center gap-2 font-bold">
                            <x-icon name="warning" class="h-5 w-5 text-red-600" />
                            Periksa kembali data yang kamu isi.
                        </p>
                        <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
