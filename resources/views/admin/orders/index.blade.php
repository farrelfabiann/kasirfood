@extends('layouts.admin')

@section('title', 'Pesanan')
@section('eyebrow', 'Monitoring Real-time')
@section('heading', 'Pesanan Masuk')

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary !px-4 !py-2.5 text-xs">
        <x-icon name="refresh" class="h-4 w-4" />
        <span class="hidden sm:inline">Muat ulang</span>
    </a>
@endsection

@section('content')
    <div
        x-data="ordersMonitor(5000)"
    >
        <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-card mb-4 grid gap-2 p-3 sm:grid-cols-2 lg:grid-cols-[1fr_150px_150px_170px_auto]">
            <div class="relative sm:col-span-2 lg:col-span-1">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-stone-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nomor pesanan atau nama..."
                    class="input-field !py-2.5 !pl-10 text-sm"
                >
            </div>

            <select name="status" class="input-field !py-2.5 text-sm">
                <option value="">Semua status</option>
                <option value="pending" @selected(request('status') === 'pending')>Menunggu bayar</option>
                <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>

            <select name="type" class="input-field !py-2.5 text-sm">
                <option value="">Semua tipe</option>
                <option value="dine_in" @selected(request('type') === 'dine_in')>Dine In</option>
                <option value="online" @selected(request('type') === 'online')>Take Away</option>
            </select>

            <select name="payment" class="input-field !py-2.5 text-sm">
                <option value="">Semua metode</option>
                <option value="qris" @selected(request('payment') === 'qris')>DOKU (Online)</option>
                <option value="edc" @selected(request('payment') === 'edc')>EDC Kiosk (Kartu)</option>
                <option value="cash" @selected(request('payment') === 'cash')>Tunai (Kasir)</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 !py-2.5 text-sm">Filter</button>
                @if (request()->hasAny(['q', 'status', 'type', 'payment']))
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary !px-3 !py-2.5" aria-label="Reset filter">
                        <x-icon name="refresh" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </form>

        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <p class="flex items-center gap-2 text-[11px] font-semibold text-stone-500">
                <span class="flex h-2 w-2 rounded-full" :class="autoRefresh ? 'bg-emerald-500 animate-soft-pulse' : 'bg-stone-300'"></span>
                <span x-text="autoRefresh ? 'Pembaruan otomatis tiap 5 detik' : 'Pembaruan otomatis dijeda'"></span>
                <span class="text-stone-400" x-show="lastUpdated" x-text="lastUpdated ? '· ' + lastUpdated.toLocaleTimeString('id-ID') : ''"></span>
            </p>

            <button
                type="button"
                @click="autoRefresh = !autoRefresh"
                class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-[11px] font-bold text-stone-600 shadow-sm transition hover:border-stone-300"
            >
                <x-icon name="refresh" class="h-3.5 w-3.5" />
                <span x-text="autoRefresh ? 'Jeda' : 'Lanjutkan'"></span>
            </button>
        </div>

        <div id="orders-live">
            @include('admin.orders._list', ['orders' => $orders, 'stats' => $stats])
        </div>
    </div>
@endsection
