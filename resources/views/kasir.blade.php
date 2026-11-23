<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#d92d0b">
    <title>{{ $kiosk['app']['name'] }} · Self Order Kiosk</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    <script>
        window.__KIOSK__ = @js($kiosk);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-full bg-stone-50 font-sans text-stone-900 antialiased selection:bg-brand-200 selection:text-brand-900"
    x-data="kiosk(window.__KIOSK__)"
    :class="{ 'overflow-hidden': showProductModal || showCart || showCheckout || showEdc || showConfirmation || showTypeModal || !started }"
>
    {{-- Welcome screen --}}
    <section
        x-show="!started"
        x-transition.opacity.duration.250ms
        class="fixed inset-0 z-50 overflow-y-auto bg-brand-700"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-brand-500/60 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-20 h-[30rem] w-[30rem] rounded-full bg-sun-400/20 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-white/5 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-5 py-10 sm:px-8">
            <div class="flex flex-col items-center gap-3 text-white">
                <img src="{{ $kiosk['app']['logo_url'] ?? asset('images/klikkluk_logo.png') }}" alt="Logo" class="h-12 w-auto object-contain" />
                <div class="text-center">
                    <p class="font-display text-lg font-extrabold tracking-tight">{{ $kiosk['app']['name'] }}</p>
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-white/60">Self Order Kiosk</p>
                </div>
            </div>

            <div class="mt-10 max-w-3xl text-center sm:mt-14">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur">
                    <x-icon name="sparkles" class="h-4 w-4 text-sun-300" />
                    Pesan sendiri, tanpa antre
                </p>
                <h1 class="mt-6 text-balance font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Mau makan di sini<br class="hidden sm:block"> atau bawa pulang?
                </h1>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-white/70 sm:text-base">
                    Sentuh salah satu pilihan di bawah untuk mulai memesan. Pesananmu akan langsung masuk ke dapur.
                </p>
            </div>

            <div class="mt-10 grid w-full max-w-4xl gap-4 sm:mt-12 sm:grid-cols-2 sm:gap-6">
                <button
                    type="button"
                    @click="start('dine_in')"
                    class="group relative flex min-h-[190px] flex-col items-start justify-between overflow-hidden rounded-[2rem] bg-white p-6 text-left shadow-kiosk transition duration-200 hover:-translate-y-1 focus:outline-none focus-visible:ring-4 focus-visible:ring-sun-300 sm:min-h-[230px] sm:p-8"
                >
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <x-icon name="table" class="h-8 w-8" />
                    </span>
                    <span>
                        <span class="block font-display text-2xl font-extrabold text-stone-900 sm:text-3xl">Makan di Tempat</span>
                        <span class="mt-1 block text-sm font-medium text-stone-500">Dine In · nikmati di restoran</span>
                    </span>
                    <span class="absolute right-6 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-stone-100 text-stone-500 transition group-hover:bg-brand-600 group-hover:text-white sm:right-8">
                        <x-icon name="arrow-right" class="h-5 w-5" />
                    </span>
                </button>

                <button
                    type="button"
                    @click="start('take_away')"
                    class="group relative flex min-h-[190px] flex-col items-start justify-between overflow-hidden rounded-[2rem] bg-sun-300 p-6 text-left shadow-kiosk transition duration-200 hover:-translate-y-1 focus:outline-none focus-visible:ring-4 focus-visible:ring-white sm:min-h-[230px] sm:p-8"
                >
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-stone-950/10 text-stone-900 transition group-hover:bg-stone-950 group-hover:text-white">
                        <x-icon name="bag" class="h-8 w-8" />
                    </span>
                    <span>
                        <span class="block font-display text-2xl font-extrabold text-stone-950 sm:text-3xl">Bawa Pulang</span>
                        <span class="mt-1 block text-sm font-medium text-stone-800/70">Take Away · siap dibungkus</span>
                    </span>
                    <span class="absolute right-6 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/50 text-stone-800 transition group-hover:bg-stone-950 group-hover:text-white sm:right-8">
                        <x-icon name="arrow-right" class="h-5 w-5" />
                    </span>
                </button>
            </div>

            <p class="mt-8 flex items-center gap-2 text-xs font-medium text-white/55">
                <x-icon name="info" class="h-4 w-4" />
                Layar ini digunakan untuk memesan secara mandiri. Bayar dengan QRIS, kartu EDC kiosk, atau tunai di kasir.
            </p>
        </div>
    </section>

    {{-- Main kiosk --}}
    <div x-show="started" x-cloak class="flex h-[100dvh] overflow-hidden">
        {{-- Sidebar kategori: selalu vertikal di sisi kiri, portrait maupun landscape --}}
        <aside class="flex w-20 shrink-0 flex-col border-r border-stone-200/80 bg-white sm:w-24 md:w-28 lg:w-32">
            <div class="flex h-12 shrink-0 items-center justify-center border-b border-stone-100 md:h-[4.5rem]">
                <img src="{{ $kiosk['app']['logo_url'] ?? asset('images/klikkluk_logo.png') }}" alt="Logo" class="h-7 w-auto object-contain" />
            </div>

            <div class="no-scrollbar flex-1 space-y-1.5 overflow-y-auto p-1.5 md:space-y-2 md:p-2">
                <button
                    type="button"
                    @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-brand-600 text-white shadow-card' : 'text-stone-600 hover:bg-stone-100'"
                    class="flex w-full flex-col items-center gap-1 rounded-xl px-1 py-2.5 text-center text-[10px] font-bold leading-tight transition md:gap-1.5 md:rounded-2xl md:py-3 md:text-[11px]"
                >
                    <x-icon name="grid" class="h-5 w-5 shrink-0 md:h-6 md:w-6" />
                    <span class="w-full truncate px-0.5">Semua</span>
                </button>

                @foreach ($kiosk['categories'] as $category)
                    <button
                        type="button"
                        @click="activeCategory = {{ $category['id'] }}"
                        :class="activeCategory === {{ $category['id'] }} ? 'bg-brand-600 text-white shadow-card' : 'text-stone-600 hover:bg-stone-100'"
                        class="flex w-full flex-col items-center gap-1 rounded-xl px-1 py-2.5 text-center text-[10px] font-bold leading-tight transition md:gap-1.5 md:rounded-2xl md:py-3 md:text-[11px]"
                    >
                        @if (! empty($category['icon_url']))
                            <img
                                src="{{ $category['icon_url'] }}"
                                alt=""
                                class="h-5 w-5 shrink-0 rounded-md bg-white p-0.5 object-contain md:h-6 md:w-6"
                            >
                        @else
                            <x-icon :name="$category['icon']" class="h-5 w-5 shrink-0 md:h-6 md:w-6" />
                        @endif
                        <span class="w-full truncate px-0.5">{{ $category['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Konten kanan, scroll independen --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="shrink-0 border-b border-stone-200/70 bg-stone-50/90 backdrop-blur-xl">
                <div class="flex items-center gap-2 px-3 py-2.5 sm:gap-3 sm:px-4 md:px-5">
                    <div class="hidden min-w-0 md:block">
                        <p class="truncate font-display text-sm font-extrabold tracking-tight text-stone-900">{{ $kiosk['app']['name'] }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-400">Self Order</p>
                    </div>

                    <div class="relative min-w-0 flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-stone-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input
                            type="search"
                            x-model.debounce.150ms="search"
                            placeholder="Cari menu..."
                            class="h-10 w-full rounded-xl border-stone-200 bg-white pl-9 pr-3 text-xs font-medium text-stone-800 shadow-sm placeholder:text-stone-400 focus:border-brand-500 focus:ring-brand-500 sm:h-11 sm:text-sm"
                        >
                    </div>

                    <button
                        type="button"
                        @click="showTypeModal = true"
                        :aria-label="orderType === 'dine_in' ? 'Makan di Tempat' : 'Bawa Pulang'"
                        class="flex h-10 shrink-0 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-2.5 text-[11px] font-bold text-stone-700 shadow-sm transition hover:border-brand-200 hover:text-brand-700 sm:h-11 sm:gap-2 sm:px-3.5 sm:text-xs"
                    >
                        <x-icon name="table" class="h-4 w-4" x-show="orderType === 'dine_in'" />
                        <x-icon name="bag" class="h-4 w-4" x-show="orderType === 'take_away'" />
                        <span class="sm:hidden" x-text="orderType === 'dine_in' ? 'Dine In' : 'Bawa'"></span>
                        <span class="hidden sm:inline" x-text="orderType === 'dine_in' ? 'Makan di Tempat' : 'Bawa Pulang'"></span>
                        <x-icon name="chevron-down" class="hidden h-4 w-4 text-stone-400 sm:block" />
                    </button>
                </div>
            </header>

            {{-- Katalog --}}
            <main class="no-scrollbar flex-1 overflow-y-auto px-3 pb-28 pt-3 sm:px-4 sm:pt-4 md:px-5 lg:px-6">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Katalog Menu</p>
                        <h2 class="mt-0.5 font-display text-lg font-extrabold tracking-tight text-stone-900 sm:text-xl" x-text="activeCategory === 'all' ? 'Semua Menu' : (categories.find(category => String(category.id) === String(activeCategory))?.name ?? 'Menu')"></h2>
                    </div>
                    <p class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-stone-500 shadow-sm">
                        <span x-text="filteredProducts().length"></span> menu tersedia
                    </p>
                </div>

                <div x-cloak class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 md:gap-3 lg:grid-cols-5">
                    <template x-for="product in filteredProducts()" :key="product.id">
                        <article
                            class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-card"
                            :class="{ 'opacity-90': !product.is_available }"
                        >
                            <button
                                type="button"
                                @click="openProduct(product)"
                                :disabled="!product.is_available"
                                class="relative block aspect-square w-full overflow-hidden bg-stone-100 text-left disabled:cursor-not-allowed"
                            >
                                <template x-if="product.image_url">
                                    <img
                                        :src="product.image_url"
                                        :alt="product.name"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                        :class="{ 'grayscale': !product.is_available }"
                                        loading="lazy"
                                    >
                                </template>
                                <template x-if="!product.image_url">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-100 via-sun-50 to-sun-100 text-brand-500">
                                        <x-icon name="utensils" class="h-9 w-9 md:h-10 md:w-10" />
                                    </div>
                                </template>

                                <span
                                    x-show="product.category_name"
                                    x-text="product.category_name"
                                    class="absolute left-2 top-2 max-w-[calc(100%-1rem)] truncate rounded-full bg-stone-950/65 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white backdrop-blur"
                                ></span>

                                <template x-if="!product.is_available">
                                    <span class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-stone-950/55 backdrop-blur-[1px]">
                                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-stone-900">Habis</span>
                                        <span class="text-[9px] font-medium text-white/80">Tidak tersedia</span>
                                    </span>
                                </template>
                            </button>

                            <div class="flex flex-1 flex-col p-2.5">
                                <h3 class="truncate text-xs font-bold text-stone-900 sm:text-sm" :title="product.name" x-text="product.name"></h3>
                                <p class="mt-0.5 line-clamp-2 min-h-[2rem] text-[10px] leading-4 text-stone-500" x-text="product.description || 'Menu pilihan untukmu.'"></p>

                                <div class="mt-auto flex items-center justify-between gap-1.5 pt-2">
                                    <p class="truncate text-sm font-extrabold text-brand-600" x-text="formatRupiah(product.price)"></p>
                                    <button
                                        type="button"
                                        @click="openProduct(product)"
                                        :disabled="!product.is_available"
                                        :aria-label="'Tambah ' + product.name"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-stone-900 text-white transition hover:bg-brand-600 active:scale-95 disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400"
                                    >
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                <div x-show="filteredProducts().length === 0" x-cloak class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                        <x-icon name="search" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 font-display text-base font-bold text-stone-800" x-text="products.length === 0 ? 'Belum ada menu tersedia' : 'Menu tidak ditemukan'"></p>
                    <p class="mt-1 text-xs text-stone-500" x-text="products.length === 0 ? 'Silakan tambahkan produk melalui dashboard admin terlebih dahulu.' : 'Coba kata kunci lain atau pilih kategori yang berbeda.'"></p>
                    <button type="button" x-show="products.length > 0" @click="search = ''; activeCategory = 'all'" class="btn-secondary mt-4">Lihat semua menu</button>
                </div>
            </main>
        </div>
    </div>

    {{-- Floating cart bar --}}
    <div x-show="started && !showConfirmation" x-cloak class="fixed bottom-0 left-20 right-0 z-30 px-2 pb-2 safe-bottom sm:left-24 sm:px-4 sm:pb-4 md:left-28 lg:left-32">
        <div class="mx-auto flex w-full max-w-5xl items-center gap-2 rounded-2xl border border-white/10 bg-stone-950/95 p-2.5 text-white shadow-kiosk backdrop-blur-xl sm:gap-3 sm:rounded-3xl sm:p-3">
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-sun-300 sm:h-12 sm:w-12 sm:rounded-2xl">
                <x-icon name="cart" class="h-5 w-5 sm:h-6 sm:w-6" />
                <span
                    x-show="cartCount > 0"
                    x-text="cartCount"
                    class="absolute -right-1.5 -top-1.5 flex h-6 min-w-6 items-center justify-center rounded-full bg-brand-500 px-1.5 text-[11px] font-extrabold text-white ring-2 ring-stone-950"
                ></span>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-[9px] font-bold uppercase tracking-[0.14em] text-white/45 sm:text-[10px] sm:tracking-[0.18em]">Total pesanan</p>
                <p class="truncate font-display text-base font-extrabold sm:text-lg" x-text="formatRupiah(cartTotal)"></p>
            </div>

            <button
                type="button"
                @click="openCart"
                :disabled="cart.length === 0"
                :aria-label="cart.length === 0 ? 'Keranjang kosong' : 'Lihat pesanan'"
                class="flex h-10 shrink-0 items-center gap-1.5 rounded-xl bg-sun-300 px-3 text-xs font-extrabold text-stone-950 transition hover:bg-sun-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-white/10 disabled:text-white/35 sm:h-12 sm:gap-2 sm:rounded-2xl sm:px-6 sm:text-sm"
            >
                <span class="sm:hidden" x-text="cart.length === 0 ? 'Kosong' : 'Lihat'"></span>
                <span class="hidden sm:inline" x-text="cart.length === 0 ? 'Keranjang kosong' : 'Lihat Pesanan'"></span>
                <x-icon name="arrow-right" class="h-4 w-4 sm:h-5 sm:w-5" />
            </button>
        </div>
    </div>

    {{-- Product option modal --}}
    <div x-show="showProductModal" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-stone-950/55 backdrop-blur-sm" @click="showProductModal = false" x-transition.opacity></div>

        <div
            x-show="showProductModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-6 opacity-0 sm:scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
            x-transition:leave-end="translate-y-6 opacity-0 sm:scale-95"
            class="relative flex max-h-[93vh] w-full max-w-2xl flex-col overflow-hidden rounded-t-[2rem] bg-white shadow-kiosk sm:rounded-[2rem]"
        >
            <div class="flex items-start gap-4 border-b border-stone-100 p-5 sm:p-6">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-stone-100">
                    <template x-if="selectedProduct?.image_url">
                        <img :src="selectedProduct.image_url" :alt="selectedProduct.name" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!selectedProduct?.image_url">
                        <div class="flex h-full w-full items-center justify-center text-brand-400">
                            <x-icon name="utensils" class="h-8 w-8" />
                        </div>
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Kustomisasi Menu</p>
                    <h2 class="mt-1 font-display text-xl font-extrabold text-stone-900" x-text="selectedProduct?.name"></h2>
                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-stone-500" x-text="selectedProduct?.description || 'Atur pilihanmu sebelum menambahkan ke pesanan.'"></p>
                </div>
                <button type="button" @click="showProductModal = false" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500 transition hover:bg-stone-200 hover:text-stone-900">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <template x-if="selectedProduct && selectedProduct.options.length > 0">
                    <div class="space-y-6">
                        <template x-for="group in selectedProduct.options" :key="group.groupName">
                            <fieldset>
                                <legend class="flex w-full items-center justify-between gap-3">
                                    <span class="font-display text-sm font-bold text-stone-900" x-text="group.groupName"></span>
                                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-stone-500" x-text="group.type === 'radio' ? 'Pilih satu' : 'Bisa lebih dari satu'"></span>
                                </legend>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <template x-for="option in group.options" :key="group.groupName + '-' + option.name">
                                        <label
                                            class="flex cursor-pointer items-start justify-between gap-3 rounded-2xl border p-3.5 transition"
                                            :class="optionSelected(group.groupName, option.name) ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                        >
                                            <input
                                                :type="group.type === 'radio' ? 'radio' : 'checkbox'"
                                                class="sr-only"
                                                :checked="optionSelected(group.groupName, option.name)"
                                                @change="toggleOption(group, option)"
                                            >
                                            <span class="text-sm font-semibold text-stone-800" x-text="option.name"></span>
                                            <span
                                                class="shrink-0 text-xs font-bold"
                                                :class="optionSelected(group.groupName, option.name) ? 'text-brand-600' : 'text-stone-500'"
                                                x-text="option.price > 0 ? '+ ' + formatRupiah(option.price) : 'Gratis'"
                                            ></span>
                                        </label>
                                    </template>
                                </div>
                            </fieldset>
                        </template>
                    </div>
                </template>

                <p x-show="!selectedProduct || selectedProduct.options.length === 0" class="rounded-2xl bg-stone-50 px-4 py-3 text-xs font-medium text-stone-500">
                    Menu ini tidak memiliki opsi tambahan.
                </p>

                <div class="mt-5">
                    <label for="item-note" class="text-xs font-bold uppercase tracking-wide text-stone-500">Catatan khusus <span class="font-medium normal-case tracking-normal text-stone-400">(opsional)</span></label>
                    <input id="item-note" type="text" x-model="itemNote" maxlength="120" placeholder="Contoh: es sedikit, sambal dipisah" class="input-field mt-2">
                </div>

                <div class="mt-5 flex items-center justify-between gap-4 rounded-2xl bg-stone-50 p-3">
                    <div>
                        <p class="text-xs font-bold text-stone-700">Jumlah</p>
                        <p class="text-[11px] text-stone-400">Berapa porsi yang kamu mau?</p>
                    </div>
                    <div class="flex items-center gap-1 rounded-2xl border border-stone-200 bg-white p-1 shadow-sm">
                        <button type="button" @click="selectedQty = Math.max(1, selectedQty - 1)" class="flex h-10 w-10 items-center justify-center rounded-xl text-stone-600 transition hover:bg-stone-100 disabled:opacity-40" :disabled="selectedQty <= 1">
                            <x-icon name="minus" class="h-5 w-5" />
                        </button>
                        <span class="w-10 text-center font-display text-base font-extrabold text-stone-900" x-text="selectedQty"></span>
                        <button type="button" @click="selectedQty = Math.min(20, selectedQty + 1)" class="flex h-10 w-10 items-center justify-center rounded-xl text-stone-600 transition hover:bg-stone-100">
                            <x-icon name="plus" class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-t border-stone-100 bg-white p-4 sm:p-5">
                <button type="button" @click="addToCart" class="flex w-full items-center justify-between gap-3 rounded-2xl bg-brand-600 px-5 py-4 text-left text-white shadow-sm transition hover:bg-brand-700 active:scale-[0.99]">
                    <span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Total item</span>
                        <span class="font-display text-lg font-extrabold" x-text="formatRupiah(selectedLineTotal)"></span>
                    </span>
                    <span class="flex items-center gap-2 text-sm font-extrabold">
                        Tambah ke Pesanan
                        <x-icon name="plus" class="h-5 w-5" />
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Cart modal --}}
    <div x-show="showCart" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-stone-950/55 backdrop-blur-sm" @click="showCart = false" x-transition.opacity></div>

        <div class="relative flex max-h-[93vh] w-full max-w-2xl flex-col overflow-hidden rounded-t-[2rem] bg-white shadow-kiosk sm:rounded-[2rem]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-100 p-5 sm:p-6">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Ringkasan Pesanan</p>
                    <h2 class="mt-1 font-display text-xl font-extrabold text-stone-900">Keranjangmu</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="clearCart" class="rounded-xl px-3 py-2 text-xs font-bold text-stone-400 transition hover:bg-stone-100 hover:text-brand-600">Kosongkan</button>
                    <button type="button" @click="showCart = false" class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-500 transition hover:bg-stone-200 hover:text-stone-900">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="space-y-3">
                    <template x-for="item in cart" :key="item.signature">
                        <div class="flex gap-3 rounded-2xl border border-stone-200/80 bg-white p-3">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-stone-100">
                                <template x-if="item.image_url">
                                    <img :src="item.image_url" :alt="item.name" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!item.image_url">
                                    <div class="flex h-full w-full items-center justify-center text-brand-400">
                                        <x-icon name="utensils" class="h-6 w-6" />
                                    </div>
                                </template>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-stone-900" x-text="item.name"></p>
                                        <p x-show="item.options.length > 0" class="mt-0.5 text-[11px] leading-4 text-stone-500" x-text="optionSummary(item)"></p>
                                        <p x-show="item.note" class="mt-0.5 text-[11px] italic leading-4 text-brand-600" x-text="'Catatan: ' + item.note"></p>
                                    </div>
                                    <button type="button" @click="removeItem(item)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-stone-300 transition hover:bg-brand-50 hover:text-brand-600">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1 rounded-xl border border-stone-200 bg-stone-50 p-0.5">
                                        <button type="button" @click="changeQty(item, -1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-stone-600 transition hover:bg-white">
                                            <x-icon name="minus" class="h-4 w-4" />
                                        </button>
                                        <span class="w-7 text-center text-xs font-extrabold text-stone-900" x-text="item.qty"></span>
                                        <button type="button" @click="changeQty(item, 1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-stone-600 transition hover:bg-white">
                                            <x-icon name="plus" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p class="text-sm font-extrabold text-stone-900" x-text="formatRupiah(item.unit_price * item.qty)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="cart.length === 0" class="py-12 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                        <x-icon name="cart" class="h-7 w-7" />
                    </span>
                    <p class="mt-3 font-display text-base font-bold text-stone-800">Keranjang masih kosong</p>
                    <p class="mt-1 text-xs text-stone-500">Tambahkan menu favoritmu terlebih dahulu.</p>
                </div>
            </div>

            <div class="border-t border-stone-100 bg-stone-50/70 p-4 sm:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-stone-500">Total pembayaran</span>
                    <span class="font-display text-xl font-extrabold text-stone-900" x-text="formatRupiah(cartTotal)"></span>
                </div>
                <button type="button" @click="proceedToCheckout" :disabled="cart.length === 0" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-brand-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                    Lanjut ke Pembayaran
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Checkout modal --}}
    <div x-show="showCheckout" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-stone-950/60 backdrop-blur-sm" @click="showCheckout = false" x-transition.opacity></div>

        <div class="relative flex max-h-[95vh] w-full max-w-5xl flex-col overflow-hidden rounded-t-[2rem] bg-stone-50 shadow-kiosk sm:rounded-[2rem]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 bg-white p-4 sm:p-5">
                <button type="button" @click="showCheckout = false; showCart = true" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-stone-500 transition hover:bg-stone-100 hover:text-stone-900">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </button>
                <div class="text-center">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Langkah Terakhir</p>
                    <p class="font-display text-base font-extrabold text-stone-900 sm:text-lg">Pembayaran</p>
                </div>
                <div class="w-20"></div>
            </div>

            <form @submit.prevent="submitOrder" class="flex-1 overflow-y-auto">
                <div class="grid gap-5 p-4 sm:p-6 lg:grid-cols-[1fr_360px]">
                    <div class="space-y-5">
                        <section class="rounded-3xl border border-stone-200/80 bg-white p-4 shadow-sm sm:p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tipe Pesanan</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="orderType = 'dine_in'"
                                    :class="orderType === 'dine_in' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                    class="flex items-center gap-3 rounded-2xl border p-3.5 text-left transition"
                                >
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-stone-100 text-stone-600">
                                        <x-icon name="table" class="h-5 w-5" />
                                    </span>
                                    <span>
                                        <span class="block text-sm font-bold text-stone-900">Makan di Tempat</span>
                                        <span class="block text-[11px] text-stone-500">Dine In</span>
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    @click="orderType = 'take_away'"
                                    :class="orderType === 'take_away' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                    class="flex items-center gap-3 rounded-2xl border p-3.5 text-left transition"
                                >
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-stone-100 text-stone-600">
                                        <x-icon name="bag" class="h-5 w-5" />
                                    </span>
                                    <span>
                                        <span class="block text-sm font-bold text-stone-900">Bawa Pulang</span>
                                        <span class="block text-[11px] text-stone-500">Take Away</span>
                                    </span>
                                </button>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-stone-200/80 bg-white p-4 shadow-sm sm:p-5">
                            <label for="customer-name" class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama Pelanggan</label>
                            <input id="customer-name" type="text" x-model="customerName" maxlength="80" placeholder="Tulis namamu di sini" class="input-field mt-2" autocomplete="name">
                            <p class="mt-2 text-[11px] text-stone-400">Nama ini akan dipanggil saat pesanan siap.</p>
                        </section>

                        <section class="rounded-3xl border border-stone-200/80 bg-white p-4 shadow-sm sm:p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Metode Pembayaran</p>
                            <div class="mt-3 grid gap-2.5">
                                <button
                                    type="button"
                                    @click="paymentMethod = 'qris'"
                                    :disabled="!payment.qris_enabled"
                                    :class="paymentMethod === 'qris' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                    class="flex w-full items-center gap-3.5 rounded-2xl border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-stone-900 text-white">
                                        <x-icon name="qr-code" class="h-6 w-6" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-stone-900">QRIS</span>
                                        <span class="block text-[11px] leading-4 text-stone-500">Bayar dengan QRIS lewat DOKU Checkout</span>
                                    </span>
                                    <span x-show="paymentMethod === 'qris'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="paymentMethod = 'edc'"
                                    :class="paymentMethod === 'edc' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                    class="flex w-full items-center gap-3.5 rounded-2xl border p-4 text-left transition"
                                >
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                                        <x-icon name="credit-card" class="h-6 w-6" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-stone-900">Kartu Debit / Kredit</span>
                                        <span class="block text-[11px] leading-4 text-stone-500">Tap kartu pada terminal EDC di layar kiosk</span>
                                    </span>
                                    <span x-show="paymentMethod === 'edc'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="paymentMethod = 'cash'"
                                    :class="paymentMethod === 'cash' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-stone-200 bg-white hover:border-stone-300'"
                                    class="flex w-full items-center gap-3.5 rounded-2xl border p-4 text-left transition"
                                >
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sun-100 text-amber-700">
                                        <x-icon name="banknotes" class="h-6 w-6" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-stone-900">Bayar Tunai di Kasir</span>
                                        <span class="block text-[11px] leading-4 text-stone-500">Ambil nomor antrean, lalu bayar ke kasir</span>
                                    </span>
                                    <span x-show="paymentMethod === 'cash'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    </span>
                                </button>
                            </div>

                            <p x-show="!payment.qris_enabled" class="mt-3 flex items-start gap-2 rounded-2xl bg-sun-50 px-3 py-2.5 text-[11px] font-medium leading-5 text-amber-800">
                                <x-icon name="warning" class="mt-0.5 h-4 w-4 shrink-0" />
                                QRIS belum dikonfigurasi. Kamu tetap bisa memakai kartu EDC atau membayar tunai di kasir.
                            </p>
                        </section>
                    </div>

                    <aside class="lg:sticky lg:top-0">
                        <div class="rounded-3xl border border-stone-200/80 bg-white p-4 shadow-sm sm:p-5">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-display text-base font-extrabold text-stone-900">Pesananmu</p>
                                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[11px] font-bold text-stone-500"><span x-text="cartCount"></span> item</span>
                            </div>

                            <div class="mt-3 max-h-56 space-y-2 overflow-y-auto pr-1">
                                <template x-for="item in cart" :key="item.signature">
                                    <div class="flex items-start justify-between gap-3 border-b border-stone-100 pb-2 last:border-0 last:pb-0">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-stone-800"><span x-text="item.qty"></span>× <span x-text="item.name"></span></p>
                                            <p x-show="item.options.length > 0" class="text-[10px] leading-4 text-stone-400" x-text="optionSummary(item)"></p>
                                        </div>
                                        <p class="shrink-0 text-xs font-bold text-stone-700" x-text="formatRupiah(item.unit_price * item.qty)"></p>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 space-y-2 border-t border-stone-100 pt-3">
                                <div class="flex items-center justify-between text-xs text-stone-500">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-stone-700" x-text="formatRupiah(cartTotal)"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-stone-900">Total Bayar</span>
                                    <span class="font-display text-xl font-extrabold text-brand-600" x-text="formatRupiah(cartTotal)"></span>
                                </div>
                            </div>

                            <p x-show="error" class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-medium leading-5 text-red-700" x-text="error"></p>

                            <button type="submit" :disabled="submitting || cart.length === 0" class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-4 text-sm font-extrabold text-white transition hover:bg-brand-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="!submitting" x-text="paymentMethod === 'qris' ? 'Lanjut ke DOKU Checkout' : (paymentMethod === 'edc' ? 'Lanjut ke Terminal EDC' : 'Buat Pesanan & Ambil Nomor')"></span>
                                <span x-show="submitting" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 1-9 9v-3a6 6 0 0 0 6-6h3Z"></path></svg>
                                    Memproses...
                                </span>
                                <x-icon x-show="!submitting" name="arrow-right" class="h-5 w-5" />
                            </button>

                            <p class="mt-2 text-center text-[10px] leading-4 text-stone-400">
                                Dengan memesan, pesanan langsung diteruskan ke dapur setelah pembayaran dikonfirmasi.
                            </p>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </div>

    {{-- Type modal --}}
    <div x-show="showTypeModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-stone-950/55 backdrop-blur-sm" @click="showTypeModal = false"></div>
        <div class="relative w-full max-w-md rounded-[2rem] bg-white p-5 shadow-kiosk">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Ubah Tipe Pesanan</p>
                    <h3 class="mt-1 font-display text-lg font-extrabold text-stone-900">Mau makan di mana?</h3>
                </div>
                <button type="button" @click="showTypeModal = false" class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-4 grid gap-2">
                <button type="button" @click="setOrderType('dine_in')" :class="orderType === 'dine_in' ? 'border-brand-500 bg-brand-50' : 'border-stone-200 bg-white'" class="flex items-center gap-3 rounded-2xl border p-3.5 text-left transition hover:border-brand-300">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="table" class="h-5 w-5" /></span>
                    <span class="flex-1">
                        <span class="block text-sm font-bold text-stone-900">Makan di Tempat</span>
                        <span class="block text-[11px] text-stone-500">Dine In</span>
                    </span>
                    <x-icon name="check" class="h-5 w-5 text-brand-600" x-show="orderType === 'dine_in'" />
                </button>
                <button type="button" @click="setOrderType('take_away')" :class="orderType === 'take_away' ? 'border-brand-500 bg-brand-50' : 'border-stone-200 bg-white'" class="flex items-center gap-3 rounded-2xl border p-3.5 text-left transition hover:border-brand-300">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sun-100 text-amber-700"><x-icon name="bag" class="h-5 w-5" /></span>
                    <span class="flex-1">
                        <span class="block text-sm font-bold text-stone-900">Bawa Pulang</span>
                        <span class="block text-[11px] text-stone-500">Take Away</span>
                    </span>
                    <x-icon name="check" class="h-5 w-5 text-brand-600" x-show="orderType === 'take_away'" />
                </button>
            </div>
        </div>
    </div>

    {{-- EDC kiosk terminal --}}
    <div x-show="showEdc" x-cloak class="fixed inset-0 z-[68] overflow-y-auto bg-stone-950" role="dialog" aria-modal="true">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute left-1/2 top-1/3 h-[28rem] w-[28rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="absolute -bottom-40 right-0 h-80 w-80 rounded-full bg-sun-300/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-xl flex-col items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full rounded-[2rem] border border-white/10 bg-white/5 p-6 text-center shadow-kiosk backdrop-blur-sm sm:p-8">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                    Terminal EDC Aktif
                </span>

                <div class="relative mx-auto mt-8 flex h-40 w-40 items-center justify-center">
                    <span class="absolute inset-0 animate-ping rounded-full bg-brand-500/20"></span>
                    <span class="absolute inset-4 rounded-full bg-brand-500/15"></span>
                    <span class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-stone-900 shadow-kiosk">
                        <x-icon name="credit-card" class="h-11 w-11" />
                    </span>
                </div>

                <h2 class="mt-6 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Menunggu Kartu Anda</h2>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-white/70">
                    Silakan Tap atau Masukkan Kartu Debit/Kredit Anda pada Terminal EDC di Bawah Layar
                </p>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 px-5 py-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Total Pembayaran</p>
                    <p class="mt-1 font-display text-4xl font-extrabold tracking-tight text-white" x-text="formatRupiah(order?.total_amount)"></p>
                    <p class="mt-1 text-[11px] font-semibold text-white/40" x-text="order?.order_number"></p>
                </div>

                <button
                    type="button"
                    @click="simulateEdc"
                    :disabled="edcSubmitting"
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-4 text-sm font-extrabold text-emerald-950 transition hover:bg-emerald-400 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!edcSubmitting" class="flex items-center gap-2">
                        <x-icon name="check-circle" class="h-5 w-5" />
                        Simulasi Tap EDC Berhasil
                    </span>
                    <span x-show="edcSubmitting" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 1-9 9v-3a6 6 0 0 0 6-6h3Z"></path></svg>
                        Memproses kartu...
                    </span>
                </button>

                <p class="mt-2 text-[10px] leading-4 text-white/40">
                    Mode demo: tombol simulasi menandai pembayaran kartu sebagai lunas tanpa perangkat EDC fisik.
                </p>

                <button
                    type="button"
                    @click="cancelEdc"
                    :disabled="edcSubmitting"
                    class="mt-3 text-xs font-bold text-white/50 underline-offset-4 transition hover:text-white hover:underline disabled:opacity-50"
                >
                    Batalkan pembayaran
                </button>
            </div>
        </div>
    </div>

    {{-- Confirmation --}}
    <div x-show="showConfirmation" x-cloak class="fixed inset-0 z-[70] overflow-y-auto bg-brand-700">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-32 top-0 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-sun-300/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-2xl flex-col items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full rounded-[2rem] bg-white p-6 text-center shadow-kiosk sm:p-8">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600" x-show="paymentState === 'paid'">
                    <x-icon name="check-circle" class="h-11 w-11" />
                </div>
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-sun-100 text-amber-600" x-show="paymentState !== 'paid'">
                    <x-icon name="clock" class="h-11 w-11" />
                </div>

                <h2 class="mt-5 font-display text-3xl font-extrabold tracking-tight text-stone-900" x-text="paymentState === 'paid' ? 'Pembayaran Berhasil!' : 'Pesanan Diterima!'"></h2>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-stone-500" x-text="paymentState === 'paid' ? 'Pembayaran kamu sudah diterima. Pesanan akan segera disiapkan.' : (paymentState === 'cash' ? 'Silakan menuju kasir dan tunjukkan Nomor Antrean Anda untuk melakukan pembayaran tunai.' : 'Selesaikan pembayaran, lalu tunjukkan nomor antrean ke kasir.')"></p>

                <div class="mt-6 rounded-3xl border border-dashed border-brand-200 bg-brand-50/60 px-5 py-6">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-500">Nomor Antrean</p>
                    <p class="mt-1 font-display text-5xl font-extrabold tracking-tight text-brand-700 sm:text-6xl" x-text="order?.queue_number"></p>
                    <p class="mt-2 text-[11px] font-semibold text-brand-500" x-text="order?.order_number"></p>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2 text-left">
                    <div class="rounded-2xl bg-stone-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-stone-400">Nama</p>
                        <p class="mt-0.5 truncate text-sm font-bold text-stone-900" x-text="order?.customer_name"></p>
                    </div>
                    <div class="rounded-2xl bg-stone-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-stone-400">Tipe</p>
                        <p class="mt-0.5 text-sm font-bold text-stone-900" x-text="order?.order_type_label"></p>
                    </div>
                    <div class="rounded-2xl bg-stone-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-stone-400">Pembayaran</p>
                        <p class="mt-0.5 text-sm font-bold text-stone-900" x-text="order?.payment_method_label"></p>
                    </div>
                    <div class="rounded-2xl bg-stone-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-stone-400">Total</p>
                        <p class="mt-0.5 text-sm font-extrabold text-brand-600" x-text="formatRupiah(order?.total_amount)"></p>
                    </div>
                </div>

                <div class="mt-5 max-h-44 space-y-2 overflow-y-auto rounded-2xl border border-stone-100 bg-stone-50/60 p-3 text-left">
                    <template x-for="(item, index) in (order?.items ?? [])" :key="index">
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <p class="font-bold text-stone-800"><span x-text="item.quantity"></span>× <span x-text="item.product_name"></span></p>
                                <p x-show="item.options?.length" class="text-[10px] leading-4 text-stone-400" x-text="(item.options ?? []).map(option => option.name).join(' · ')"></p>
                                <p x-show="item.note" class="text-[10px] italic leading-4 text-brand-500" x-text="'Catatan: ' + item.note"></p>
                            </div>
                            <p class="shrink-0 font-bold text-stone-700" x-text="formatRupiah(item.price * item.quantity)"></p>
                        </div>
                    </template>
                </div>

                <p x-show="paymentState === 'cash'" class="mt-4 flex items-start gap-2 rounded-2xl bg-sun-50 px-4 py-3 text-left text-xs font-medium leading-5 text-amber-800">
                    <x-icon name="banknotes" class="mt-0.5 h-4 w-4 shrink-0" />
                    Silakan menuju kasir dan tunjukkan Nomor Antrean Anda untuk melakukan pembayaran tunai. Pesanan akan diproses setelah kasir mengonfirmasi pembayaran.
                </p>
                <p x-show="paymentState === 'pending'" class="mt-4 flex items-start gap-2 rounded-2xl bg-sun-50 px-4 py-3 text-left text-xs font-medium leading-5 text-amber-800">
                    <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0" />
                    Pembayaran sedang diproses. Nomor antrean tetap berlaku setelah pembayaran diterima.
                </p>

                <button type="button" @click="newOrder" class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-stone-950 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-brand-600 active:scale-[0.99]">
                    <x-icon name="plus" class="h-5 w-5" />
                    Pesan Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div
        x-show="toast.visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="-translate-y-2 opacity-0"
        class="fixed left-1/2 top-4 z-[90] w-[calc(100%-2rem)] max-w-md -translate-x-1/2"
    >
        <div
            class="flex items-start gap-3 rounded-2xl px-4 py-3 text-sm font-semibold shadow-kiosk"
            :class="toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-stone-950 text-white'"
        >
            <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0" x-show="toast.type !== 'error'" />
            <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" x-show="toast.type === 'error'" />
            <span class="flex-1" x-text="toast.message"></span>
        </div>
    </div>
</body>
</html>
