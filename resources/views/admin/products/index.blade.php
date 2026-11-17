@extends('layouts.admin')

@section('title', 'Menu')
@section('eyebrow', 'Manajemen Menu')
@section('heading', 'Daftar Menu')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary !px-4 !py-2.5 text-xs sm:text-sm">
        <x-icon name="plus" class="h-4 w-4" />
        <span class="hidden sm:inline">Tambah Menu</span>
        <span class="sm:hidden">Tambah</span>
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.products.index') }}" class="admin-card mb-4 grid gap-2 p-3 sm:grid-cols-[1fr_180px_170px_auto]">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-stone-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama atau deskripsi menu..."
                class="input-field !py-2.5 !pl-10 text-sm"
            >
        </div>

        <select name="category" class="input-field !py-2.5 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <select name="availability" class="input-field !py-2.5 text-sm">
            <option value="">Semua status</option>
            <option value="available" @selected(request('availability') === 'available')>Tersedia</option>
            <option value="unavailable" @selected(request('availability') === 'unavailable')>Habis</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex-1 !py-2.5 text-sm">Cari</button>
            @if (request()->hasAny(['q', 'category', 'availability']))
                <a href="{{ route('admin.products.index') }}" class="btn-secondary !px-3 !py-2.5" aria-label="Reset filter">
                    <x-icon name="refresh" class="h-4 w-4" />
                </a>
            @endif
        </div>
    </form>

    <div class="admin-card overflow-hidden">
        @if ($products->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                    <x-icon name="cube" class="h-7 w-7" />
                </span>
                <p class="mt-4 font-display text-base font-extrabold text-stone-800">Belum ada menu</p>
                <p class="mt-1 text-sm text-stone-500">Tambahkan menu pertama agar muncul di kiosk pelanggan.</p>
                <a href="{{ route('admin.products.create') }}" class="btn-primary mt-5">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Menu
                </a>
            </div>
        @else
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full min-w-[820px] text-left">
                    <thead>
                        <tr class="border-b border-stone-100 bg-stone-50/70 text-[10px] font-bold uppercase tracking-[0.14em] text-stone-400">
                            <th class="px-5 py-3">Menu</th>
                            <th class="px-3 py-3">Kategori</th>
                            <th class="px-3 py-3">Harga</th>
                            <th class="px-3 py-3">Opsi</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($products as $product)
                            <tr class="align-middle transition hover:bg-stone-50/60">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-stone-100">
                                            @if ($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-brand-400">
                                                    <x-icon name="utensils" class="h-6 w-6" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-stone-900">{{ $product->name }}</p>
                                            <p class="mt-0.5 max-w-[260px] truncate text-[11px] text-stone-400">{{ $product->description ?: 'Tanpa deskripsi' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3.5">
                                    <span class="badge bg-stone-100 text-stone-600">{{ $product->category?->name ?? 'Tanpa kategori' }}</span>
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-sm font-extrabold text-stone-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3.5">
                                    @php $groupCount = count($product->additional_options ?? []); @endphp
                                    @if ($groupCount > 0)
                                        <span class="badge bg-brand-50 text-brand-700">{{ $groupCount }} grup</span>
                                    @else
                                        <span class="badge bg-stone-100 text-stone-400">Tanpa opsi</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3.5">
                                    <form method="POST" action="{{ route('admin.products.availability', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="badge transition {{ $product->is_available ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-red-50 text-red-600 hover:bg-red-100' }}"
                                            title="Klik untuk mengubah status"
                                        >
                                            <x-icon :name="$product->is_available ? 'check-circle' : 'x-circle'" class="h-3.5 w-3.5" />
                                            {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                                        </button>
                                    </form>
                                </td>

                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="flex h-9 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 text-[11px] font-bold text-stone-600 transition hover:border-brand-200 hover:text-brand-700">
                                            <x-icon name="pencil" class="h-3.5 w-3.5" />
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus menu {{ $product->name }}? Riwayat pesanan tetap tersimpan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-red-50 hover:text-red-600" aria-label="Hapus {{ $product->name }}">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu mobile --}}
            <ul class="divide-y divide-stone-100 lg:hidden">
                @foreach ($products as $product)
                    @php $groupCount = count($product->additional_options ?? []); @endphp
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-stone-100">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-brand-400">
                                        <x-icon name="utensils" class="h-7 w-7" />
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-stone-900">{{ $product->name }}</p>
                                <p class="mt-0.5 line-clamp-2 text-[11px] leading-4 text-stone-400">{{ $product->description ?: 'Tanpa deskripsi' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <span class="badge bg-stone-100 text-stone-600">{{ $product->category?->name ?? 'Tanpa kategori' }}</span>
                                    <span class="badge bg-stone-100 text-stone-500">{{ $groupCount }} grup opsi</span>
                                </div>
                            </div>

                            <p class="shrink-0 text-sm font-extrabold text-stone-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.products.availability', $product) }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="badge {{ $product->is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}"
                                >
                                    <x-icon :name="$product->is_available ? 'check-circle' : 'x-circle'" class="h-3.5 w-3.5" />
                                    {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.products.edit', $product) }}" class="ml-auto flex h-9 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 text-[11px] font-bold text-stone-600 transition hover:border-brand-200 hover:text-brand-700">
                                <x-icon name="pencil" class="h-3.5 w-3.5" />
                                Edit
                            </a>

                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus menu {{ $product->name }}? Riwayat pesanan tetap tersimpan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-red-50 hover:text-red-600" aria-label="Hapus {{ $product->name }}">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($products->hasPages())
                <div class="border-t border-stone-100 px-5 py-4">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
