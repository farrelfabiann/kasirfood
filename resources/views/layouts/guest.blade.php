<!DOCTYPE html>
<html lang="id" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#d92d0b">

        <title>{{ config('app.frontend_name', 'KlikKluk') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/klikkluk_logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-stone-50 font-sans text-stone-900 antialiased">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <aside class="relative hidden overflow-hidden bg-brand-700 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-500/60 blur-3xl"></div>
                    <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-sun-400/20 blur-3xl"></div>
                </div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo class="h-12 w-12 text-white/15 ring-1 ring-white/30" />
                    <div>
                        <p class="font-display text-lg font-extrabold tracking-tight">{{ config('app.frontend_name', 'KlikKluk') }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/55">Admin Panel</p>
                    </div>
                </div>

                <div class="relative">
                    <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white/80 backdrop-blur">
                        <x-icon name="sparkles" class="h-4 w-4 text-sun-300" />
                        Kelola kiosk
                    </p>
                    <h1 class="mt-6 max-w-md text-balance font-display text-4xl font-extrabold leading-tight tracking-tight">
                        Satu tempat untuk menu, pesanan, dan pembayaran.
                    </h1>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-white/70">
                        Pantau pesanan masuk secara langsung, atur ketersediaan menu, dan konfirmasi pembayaran tunai dari kasir.
                    </p>
                </div>

                <div class="relative flex flex-wrap gap-2 text-[11px] font-semibold text-white/65">
                    <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">Monitoring real-time</span>
                    <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">QRIS · EDC · Tunai</span>
                    <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">Kiosk mandiri</span>
                </div>
            </aside>

            <main class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <a href="{{ route('kiosk.index') }}" class="mb-6 flex items-center gap-3 lg:hidden">
                        <x-application-logo class="h-11 w-11 text-brand-600" />
                        <div>
                            <p class="font-display text-base font-extrabold tracking-tight text-stone-900">{{ config('app.frontend_name', 'KlikKluk') }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Admin Panel</p>
                        </div>
                    </a>

                    <div class="rounded-3xl border border-stone-200/80 bg-white p-6 shadow-sm sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-5 text-center text-[11px] font-medium text-stone-400">
                        Ingin memesan? <a href="{{ route('kiosk.index') }}" class="font-bold text-brand-600 hover:text-brand-700">Buka kiosk pelanggan</a>
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
