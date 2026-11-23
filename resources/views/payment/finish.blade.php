<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selesai Pembayaran - {{ config('app.frontend_name', 'KlikKluk') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/klikkluk_logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#2563eb', 600: '#1d4ed8', 700: '#1e40af' },
                        sun: { 50: '#fffbeb', 100: '#fef3c7', 300: '#fcd34d', 600: '#d97706', 800: '#92400e' },
                        stone: { 50: '#fafaf9', 100: '#f5f5f4', 200: '#e7e5e4', 300: '#d6d3d1', 400: '#a8a29e', 500: '#78716c', 600: '#57534e', 700: '#44403c', 800: '#292524', 900: '#1c1917' },
                    },
                    fontFamily: { display: ['Inter', 'system-ui', 'sans-serif'], body: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-brand-50 flex flex-col" x-data="paymentFinish()" x-init="init()">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -left-32 top-0 h-80 w-80 rounded-full bg-brand-500/10 blur-3xl"></div>
        <div class="absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-sun-300/20 blur-3xl"></div>
    </div>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="rounded-[2rem] bg-white p-6 sm:p-8 shadow-kiosk text-center">
                <template x-if="state === 'checking'">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                        <svg class="h-11 w-11 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-5 text-xl font-extrabold text-stone-900">Memeriksa Status Pembayaran</h1>
                    <p class="mt-2 text-stone-500">Mohon tunggu sebentar, kami sedang memeriksa status transaksi Anda...</p>
                </template>

                <template x-if="state === 'paid'">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-11 w-11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h1 class="mt-5 text-xl font-extrabold text-stone-900">Pembayaran Berhasil</h1>
                    <p class="mt-2 text-stone-500">Terima kasih, pesanan Anda telah lunas.</p>
                    <div class="mt-6 rounded-xl bg-emerald-50 p-4 text-left">
                        <p class="text-sm text-stone-600"><span class="font-semibold text-stone-900">Nomor Antrian: </span><span x-text="queueNumber"></span></p>
                        <p class="mt-1 text-sm text-stone-600"><span class="font-semibold text-stone-900">Nomor Pesanan: </span><span x-text="orderNumber"></span></p>
                    </div>
                </template>

                <template x-if="state === 'pending'">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-sun-100 text-amber-600">
                        <svg class="h-11 w-11 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h1 class="mt-5 text-xl font-extrabold text-stone-900">Menunggu Pembayaran</h1>
                    <p class="mt-2 text-stone-500">Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran di aplikasi e-wallet/banking Anda.</p>
                    <div class="mt-6 rounded-xl bg-sun-50 p-4 text-left">
                        <p class="text-sm text-stone-600"><span class="font-semibold text-stone-900">Nomor Pesanan: </span><span x-text="orderNumber"></span></p>
                    </div>
                </template>

                <template x-if="state === 'failed' || state === 'cancelled' || state === 'expired'">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                        <svg class="h-11 w-11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <h1 class="mt-5 text-xl font-extrabold text-stone-900" x-text="state === 'failed' ? 'Pembayaran Gagal' : (state === 'cancelled' ? 'Pembayaran Dibatalkan' : 'Pembayaran Kedaluwarsa')"></h1>
                    <p class="mt-2 text-stone-500" x-text="state === 'failed' ? 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi atau pilih bayar tunai di kasir.' : (state === 'cancelled' ? 'Pembayaran dibatalkan. Anda bisa memesan kembali kapan saja.' : 'Waktu pembayaran habis. Silakan buat pesanan baru.')"></p>
                </template>

                <template x-if="state === 'unknown'">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-stone-100 text-stone-600">
                        <svg class="h-11 w-11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h1 class="mt-5 text-xl font-extrabold text-stone-900">Pesanan Tidak Ditemukan</h1>
                    <p class="mt-2 text-stone-500">Kami tidak menemukan informasi pesanan. Silakan hubungi kasir jika Anda sudah melakukan pembayaran.</p>
                </template>

                <div class="mt-8 flex flex-col gap-3">
                    <button @click="goHome" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-brand-700 active:scale-[0.99]">
                        Kembali ke Beranda
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function paymentFinish() {
            return {
                state: 'checking',
                orderNumber: null,
                queueNumber: null,
                pollTimer: null,
                pollCount: 0,
                maxPolls: 30,

                init() {
                    if (!this.$refs.orderData) {
                        this.state = 'unknown';
                        return;
                    }

                    const order = JSON.parse(this.$refs.orderData.textContent);
                    if (!order) {
                        this.state = 'unknown';
                        return;
                    }

                    this.orderNumber = order.order_number;
                    this.queueNumber = order.queue_number;

                    if (order.status === 'paid') {
                        this.state = 'paid';
                        return;
                    }

                    this.startPolling();
                },

                startPolling() {
                    this.pollCount = 0;
                    this.poll();
                },

                async poll() {
                    if (this.pollCount >= this.maxPolls) {
                        this.state = 'pending';
                        return;
                    }

                    this.pollCount++;

                    try {
                        const response = await fetch('{{ route("payment.status") }}', {
                            method: 'GET',
                            headers: { Accept: 'application/json' },
                        });

                        if (!response.ok) {
                            throw new Error('Gagal memeriksa status');
                        }

                        const data = await response.json();

                        if (data.status === 'paid') {
                            this.state = 'paid';
                            this.queueNumber = data.queue_number;
                            this.orderNumber = data.order_number;
                            return;
                        }

                        if (['cancelled', 'expired', 'failed'].includes(data.status)) {
                            this.state = data.status;
                            return;
                        }

                        this.state = 'pending';
                    } catch (error) {
                        console.error('Polling error:', error);
                    }

                    setTimeout(() => this.poll(), 3000);
                },

                goHome() {
                    window.location.href = '/';
                },
            };
        }
    </script>
</body>
</html>