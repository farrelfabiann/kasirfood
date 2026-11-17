@php
    $statusStyles = [
        'paid' => 'bg-emerald-50 text-emerald-700',
        'pending' => 'bg-sun-100 text-amber-700',
        'cancelled' => 'bg-red-50 text-red-600',
    ];

    $paymentStyles = [
        'qris' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',
        'edc' => 'bg-violet-50 text-violet-700 ring-1 ring-violet-200',
        'cash' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
    ];
@endphp

<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="admin-card p-4">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-stone-400">Pendapatan Lunas</p>
        <p class="mt-2 font-display text-xl font-extrabold text-stone-900">Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}</p>
    </div>
    <div class="admin-card p-4">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-stone-400">Pesanan Hari Ini</p>
        <p class="mt-2 font-display text-xl font-extrabold text-stone-900">{{ $stats['orders_today'] }}</p>
    </div>
    <div class="admin-card p-4">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-stone-400">Menunggu Bayar</p>
        <p class="mt-2 font-display text-xl font-extrabold text-amber-600">{{ $stats['pending'] }}</p>
    </div>
    <div class="admin-card p-4">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-stone-400">Kasir / EDC</p>
        <p class="mt-2 font-display text-xl font-extrabold text-brand-600">{{ $stats['cash_pending'] }}</p>
    </div>
</div>

@if ($orders->isEmpty())
    <div class="admin-card mt-4 px-6 py-16 text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
            <x-icon name="receipt" class="h-7 w-7" />
        </span>
        <p class="mt-4 font-display text-base font-extrabold text-stone-800">Belum ada pesanan</p>
        <p class="mt-1 text-sm text-stone-500">Pesanan baru dari kiosk akan otomatis muncul di sini.</p>
    </div>
@else
    <div class="mt-4 grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
        @foreach ($orders as $order)
            <article class="admin-card flex flex-col overflow-hidden">
                <div class="flex items-start justify-between gap-3 border-b border-stone-100 px-4 py-3.5">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Nomor Antrean</p>
                        <p class="font-display text-2xl font-extrabold leading-tight text-stone-900">{{ $order->queue_number }}</p>
                        <p class="mt-0.5 text-[11px] font-semibold text-stone-400">{{ $order->order_number }}</p>
                    </div>

                    <span class="badge {{ $statusStyles[$order->status] ?? 'bg-stone-100 text-stone-500' }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 border-b border-stone-100 bg-stone-50/60 px-4 py-2.5">
                    <span class="badge bg-white text-stone-600 ring-1 ring-stone-200">{{ $order->order_type_label }}</span>
                    <span class="badge {{ $paymentStyles[$order->payment_method] ?? 'bg-stone-100 text-stone-600' }}">
                        {{ $order->payment_method_label }}
                    </span>
                    <span class="badge bg-white text-stone-600 ring-1 ring-stone-200">{{ $order->items->sum('quantity') }} item</span>
                    <span class="ml-auto text-[11px] font-medium text-stone-400">{{ $order->created_at?->translatedFormat('d M · H:i') }}</span>
                </div>

                <div class="flex-1 px-4 py-3">
                    <p class="flex items-center gap-1.5 text-sm font-bold text-stone-900">
                        <x-icon name="user" class="h-4 w-4 text-stone-400" />
                        {{ $order->customer_name }}
                    </p>

                    <ul class="mt-2.5 space-y-2">
                        @foreach ($order->items as $item)
                            @php
                                $optionNames = collect($item->options ?? [])->pluck('name')->filter()->implode(', ');
                            @endphp
                            <li class="text-xs">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="min-w-0 font-semibold text-stone-700">
                                        <span class="text-stone-400">{{ $item->quantity }}×</span>
                                        {{ $item->product_name ?: 'Menu' }}
                                    </p>
                                    <p class="shrink-0 font-bold text-stone-700">
                                        Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </p>
                                </div>
                                @if ($optionNames !== '')
                                    <p class="mt-0.5 pl-4 text-[11px] leading-4 text-stone-400">{{ $optionNames }}</p>
                                @endif
                                @if ($item->note)
                                    <p class="mt-0.5 flex items-start gap-1 pl-4 text-[11px] italic leading-4 text-brand-600">
                                        <x-icon name="info" class="mt-0.5 h-3 w-3 shrink-0" />
                                        {{ $item->note }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-stone-100 px-4 py-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-stone-400">Total</p>
                        <p class="font-display text-lg font-extrabold text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    </div>

                    @if ($order->status === 'pending')
                        <form method="POST" action="{{ route('admin.orders.confirm', $order) }}" onsubmit="return confirm('Konfirmasi pembayaran {{ $order->order_number }} sebagai lunas?')">
                            @csrf
                            <button type="submit" class="flex h-11 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-xs font-extrabold text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                                <x-icon name="check-circle" class="h-4 w-4" />
                                Konfirmasi Lunas
                            </button>
                        </form>
                    @elseif ($order->status === 'paid')
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            {{ $order->paid_at?->translatedFormat('H:i') ?? 'Lunas' }}
                        </p>
                    @else
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold text-stone-400">
                            <x-icon name="x-circle" class="h-4 w-4" />
                            Dibatalkan
                        </p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    @if ($orders->hasPages())
        <div class="mt-4 admin-card px-5 py-4">
            {{ $orders->links() }}
        </div>
    @endif
@endif
