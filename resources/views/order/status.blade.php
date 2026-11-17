<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>Status Pesanan {{ $order->order_number }} · {{ config('app.frontend_name', 'KlikKluk') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-brand-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-20 h-[30rem] w-[30rem] rounded-full bg-sun-400/20 blur-3xl"></div>
    </div>

    <main class="relative mx-auto flex min-h-screen w-full max-w-xl flex-col justify-center px-4 py-10 sm:px-6">
        <div class="mb-6 flex items-center justify-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-sm">
                <x-icon name="utensils" class="h-6 w-6" />
            </div>
            <div class="text-left">
                <p class="font-display text-base font-extrabold tracking-tight text-stone-900">{{ config('app.frontend_name', 'KlikKluk') }}</p>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-400">Status Pesanan</p>
            </div>
        </div>

        @php($state = in_array($order->status, ['paid', 'cancelled'], true) ? $order->status : 'pending')

        @if ($state === 'pending')
        {{-- PENDING --}}
        <section
            data-state="pending"
            class="rounded-[2rem] border border-stone-200/80 bg-white p-6 text-center shadow-sm sm:p-8"
        >
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-sun-100 text-amber-600">
                <x-icon name="clock" class="h-11 w-11" />
            </div>
            <h1 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-stone-900">
                {{ match ($order->payment_method) {
                    'cash' => 'Menunggu Pembayaran di Kasir',
                    'edc' => 'Menunggu Pembayaran Kartu',
                    default => 'Pembayaran Belum Selesai',
                } }}
            </h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">
                @if ($order->payment_method === 'cash')
                    Silakan menuju kasir dan tunjukkan Nomor Antrean Anda untuk melakukan pembayaran tunai.
                @elseif ($order->payment_method === 'edc')
                    Pesananmu sudah tercatat. Selesaikan pembayaran dengan tap atau masukkan kartu pada terminal EDC kiosk.
                @else
                    Pesananmu sudah tercatat, tetapi pembayaran belum diterima. Selesaikan pembayaran agar pesanan diproses.
                @endif
            </p>

            <dl class="mt-6 space-y-2 rounded-2xl bg-stone-50 p-4 text-left text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Nomor pesanan</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->order_number }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Nomor antrean</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->queue_number }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Nama</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->customer_name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Metode</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->payment_method_label }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Total</dt>
                    <dd class="font-semibold text-stone-900">Rp {{ number_format((int) $order->total_amount, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-col gap-3">
                @if ($order->payment_url)
                    <a
                        href="{{ $order->payment_url }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-700 active:scale-[0.99]"
                    >
                        Lanjutkan Pembayaran QRIS
                        <x-icon name="arrow-right" class="h-5 w-5" />
                    </a>
                @endif

                <button
                    type="button"
                    data-action="cancel"
                    data-url="{{ route('order.cancel', $order->order_number) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white px-5 py-4 text-sm font-extrabold text-rose-600 transition hover:bg-rose-50 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batalkan Pesanan
                </button>

                <a
                    href="{{ route('kiosk.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold text-stone-500 transition hover:bg-stone-100 hover:text-stone-700"
                >
                    Kembali ke Beranda
                </a>
            </div>

            <p class="mt-5 text-[11px] font-medium leading-5 text-stone-400">
                Pembayaran yang sudah berhasil akan diperbarui otomatis di halaman ini.
            </p>
        </section>
        @endif

        @if ($state === 'paid')
        {{-- PAID --}}
        <section
            data-state="paid"
            class="rounded-[2rem] border border-stone-200/80 bg-white p-6 text-center shadow-sm sm:p-8"
        >
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <x-icon name="check-circle" class="h-11 w-11" />
            </div>
            <h1 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-stone-900">Pembayaran Lunas</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">Terima kasih! Pesananmu sedang disiapkan. Mohon tunggu nomor antrean dipanggil.</p>

            <div class="mt-6 rounded-2xl bg-emerald-50 p-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-700">Nomor Antrean</p>
                <p class="mt-1 font-display text-5xl font-extrabold tracking-tight text-emerald-700">{{ $order->queue_number }}</p>
            </div>

            <dl class="mt-5 space-y-2 rounded-2xl bg-stone-50 p-4 text-left text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Nomor pesanan</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->order_number }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Nama</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->customer_name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Tipe pesanan</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->order_type_label }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Metode</dt>
                    <dd class="font-semibold text-stone-900">{{ $order->payment_method_label }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Total dibayar</dt>
                    <dd class="font-semibold text-stone-900">Rp {{ number_format((int) $order->total_amount, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-col gap-3">
                <a
                    href="{{ route('kiosk.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-700 active:scale-[0.99]"
                >
                    Pesan Lagi
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </a>
            </div>
        </section>
        @endif

        @if ($state === 'cancelled')
        {{-- CANCELLED / EXPIRED --}}
        <section
            data-state="cancelled"
            class="rounded-[2rem] border border-stone-200/80 bg-white p-6 text-center shadow-sm sm:p-8"
        >
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <x-icon name="x-circle" class="h-11 w-11" />
            </div>
            <h1 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-stone-900">Pesanan Dibatalkan</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">Pesanan ini sudah dibatalkan. Silakan buat pesanan baru jika masih ingin memesan.</p>

            <div class="mt-6 flex flex-col gap-3">
                <a
                    href="{{ route('kiosk.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-700 active:scale-[0.99]"
                >
                    Kembali ke Beranda
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </a>
            </div>
        </section>
        @endif

        <p id="auto-return" class="mt-5 text-center text-[11px] font-medium text-stone-400">
            Halaman akan kembali ke beranda otomatis dalam <span id="auto-return-seconds">45</span> detik.
        </p>
    </main>

    <script>
        (function () {
            const order = {
                number: @json($order->order_number),
                status: @json($order->status),
                statusUrl: @json(url('/order/'.$order->order_number.'/status')),
            };

            // --- Auto kembali ke beranda setelah 45 detik tanpa aktivitas ---
            const IDLE_LIMIT = 45;
            const secondsEl = document.getElementById('auto-return-seconds');
            let remaining = IDLE_LIMIT;

            const goHome = () => { window.location.href = @json(route('kiosk.index')); };

            const tick = () => {
                remaining -= 1;
                if (secondsEl) secondsEl.textContent = String(Math.max(remaining, 0));
                if (remaining <= 0) {
                    goHome();
                }
            };

            setInterval(tick, 1000);

            const resetIdle = () => {
                remaining = IDLE_LIMIT;
                if (secondsEl) secondsEl.textContent = String(remaining);
            };

            ['pointerdown', 'keydown', 'touchstart', 'scroll', 'mousemove'].forEach((evt) => {
                window.addEventListener(evt, resetIdle, { passive: true });
            });

            // --- Batalkan pesanan ---
            const cancelBtn = document.querySelector('[data-action="cancel"]');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', async () => {
                    if (! window.confirm('Batalkan pesanan ini? Tindakan ini tidak bisa dibatalkan.')) {
                        return;
                    }

                    cancelBtn.disabled = true;
                    cancelBtn.textContent = 'Membatalkan...';

                    try {
                        const response = await fetch(cancelBtn.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (! response.ok) {
                            throw new Error(data.message || 'Gagal membatalkan pesanan.');
                        }

                        // Muat ulang agar tampilan beralih ke status dibatalkan.
                        window.location.reload();
                    } catch (error) {
                        window.alert(error.message || 'Gagal membatalkan pesanan.');
                        cancelBtn.disabled = false;
                        cancelBtn.textContent = 'Batalkan Pesanan';
                    }
                });
            }

            // --- Polling status selama masih menunggu pembayaran ---
            const pollStatus = async () => {
                if (order.status !== 'pending') {
                    return;
                }

                try {
                    const response = await fetch(order.statusUrl, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store',
                    });

                    if (! response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (data.status && data.status !== 'pending' && order.status === 'pending') {
                        order.status = data.status;
                        // Muat ulang agar nomor antrean dan detail lainnya ikut segar.
                        window.location.reload();
                    }
                } catch (error) {
                    // Abaikan gangguan jaringan; polling berikutnya akan mencoba lagi.
                }
            };

            setInterval(pollStatus, 5000);
        })();
    </script>
</body>
</html>
