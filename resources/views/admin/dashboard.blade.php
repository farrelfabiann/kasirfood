@extends('layouts.admin')

@section('title', 'Dashboard')
@section('eyebrow', 'Ringkasan Hari Ini')
@section('heading', 'Dashboard')

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary !px-4 !py-2.5">
        <x-icon name="receipt" class="h-4 w-4" />
        <span class="hidden sm:inline">Lihat Pesanan</span>
    </a>
@endsection

@section('content')
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Pendapatan Lunas</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="banknotes" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-stone-900">
                Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] font-medium text-stone-400">Total pesanan berstatus lunas hari ini.</p>
        </div>

        <div class="admin-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Pesanan Hari Ini</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="receipt" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-stone-900">{{ $stats['orders_today'] }}</p>
            <p class="mt-1 text-[11px] font-medium text-stone-400">Semua pesanan yang masuk hari ini.</p>
        </div>

        <div class="admin-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Menunggu Bayar</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sun-100 text-amber-600">
                    <x-icon name="clock" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-stone-900">{{ $stats['pending'] }}</p>
            <p class="mt-1 text-[11px] font-medium text-stone-400">Perlu dipantau oleh kasir.</p>
        </div>

        <div class="admin-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Menu Tersedia</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-stone-100 text-stone-600">
                    <x-icon name="cube" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 font-display text-2xl font-extrabold tracking-tight text-stone-900">{{ $stats['available_products'] }}</p>
            <p class="mt-1 text-[11px] font-medium text-stone-400">Menu aktif yang tampil di kiosk.</p>
        </div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_360px]">
        <section class="admin-card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Terbaru</p>
                    <h2 class="font-display text-base font-extrabold text-stone-900">Pesanan Masuk</h2>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn-ghost !px-3 !py-2 text-xs">
                    Semua pesanan
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="px-5 py-12 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                        <x-icon name="receipt" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-bold text-stone-700">Belum ada pesanan</p>
                    <p class="mt-1 text-xs text-stone-500">Pesanan dari kiosk akan muncul di sini.</p>
                </div>
            @else
                <ul class="divide-y divide-stone-100">
                    @foreach ($recentOrders as $order)
                        <li class="flex flex-wrap items-center gap-3 px-5 py-3.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-stone-900">
                                    {{ $order->customer_name }}
                                    <span class="ml-1 text-[11px] font-semibold text-stone-400">{{ $order->order_number }}</span>
                                </p>
                                <p class="mt-0.5 text-[11px] font-medium text-stone-500">
                                    {{ $order->order_type_label }} · {{ $order->payment_method_label }} · {{ $order->items->sum('quantity') }} item
                                </p>
                            </div>
                            <p class="text-sm font-extrabold text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <span @class([
                                'badge',
                                'bg-emerald-50 text-emerald-700' => $order->status === 'paid',
                                'bg-sun-100 text-amber-700' => $order->status === 'pending',
                                'bg-red-50 text-red-700' => $order->status === 'cancelled',
                            ])>{{ $order->status_label }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-stone-100 px-5 py-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Hari Ini</p>
                <h2 class="font-display text-base font-extrabold text-stone-900">Menu Terlaris</h2>
            </div>

            @if ($topProducts->isEmpty())
                <div class="px-5 py-12 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                        <x-icon name="chart" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-bold text-stone-700">Belum ada penjualan lunas</p>
                    <p class="mt-1 text-xs text-stone-500">Data muncul setelah pembayaran dikonfirmasi.</p>
                </div>
            @else
                <ol class="divide-y divide-stone-100">
                    @foreach ($topProducts as $index => $product)
                        <li class="flex items-center gap-3 px-5 py-3.5">
                            <span @class([
                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-extrabold',
                                'bg-brand-600 text-white' => $index === 0,
                                'bg-stone-100 text-stone-500' => $index !== 0,
                            ])>{{ $index + 1 }}</span>
                            <p class="min-w-0 flex-1 truncate text-sm font-semibold text-stone-800">{{ $product->product_name }}</p>
                            <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[11px] font-bold text-stone-600">
                                {{ (int) $product->total_quantity }} porsi
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
@endsection
