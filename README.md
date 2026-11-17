# KlikKluk — Self Order Kiosk

Aplikasi POS restoran berbasis Laravel: pelanggan memesan sendiri lewat kiosk publik, sedangkan admin mengelola menu dan memantau pesanan dari panel admin. Pembayaran mendukung **QRIS lewat DOKU Checkout**, **kartu debit/kredit lewat EDC kiosk**, dan **tunai di kasir**.

## Fitur

- **Kiosk publik (`/`)**
  - Layar sambutan Dine In / Take Away.
  - Sidebar kategori vertikal permanen (portrait & landscape) dengan ikon, pencarian menu, dan grid produk ringkas.
  - Label "Habis" + tombol nonaktif untuk menu yang tidak tersedia.
  - Modal opsi tambahan (radio/checkbox), catatan per item, dan keranjang mengambang.
  - Checkout dengan 3 opsi touch-friendly: QRIS lewat DOKU Checkout, kartu debit/kredit pada EDC kiosk, atau tunai di kasir.
  - Halaman status pesanan `/order/{order_number}`: tombol lanjutkan pembayaran QRIS, nomor antrean setelah lunas, dan kembali ke beranda otomatis setelah 45 detik tanpa aktivitas.
- **Panel admin (`/admin`)**
  - Dashboard ringkasan penjualan, pesanan terbaru, dan menu terlaris.
  - Monitoring pesanan real-time (polling 5 detik) lengkap dengan item, opsi, badge metode bayar (`DOKU (Online)`, `EDC Kiosk (Kartu)`, `Tunai (Kasir)`), dan filter metode bayar.
  - Konfirmasi "Lunas" untuk pesanan tunai maupun EDC yang masih pending.
  - CRUD menu dengan foto, status ketersediaan, dan builder opsi tambahan.
  - CRUD kategori dengan ikon sidebar (pilih ikon bawaan atau upload gambar ikon promo).
- **Keamanan**
  - Semua total dan harga dihitung ulang di server; harga dari browser tidak dipercaya.
  - Webhook DOKU `POST /doku/notification` dikecualikan dari CSRF dan memverifikasi signature `HMAC-SHA256` DOKU.
  - Pembatalan pesanan hanya berlaku untuk pesanan pada sesi kiosk yang sama dan tidak bisa dilakukan setelah lunas.

## Stack

- Laravel 12 (PHP 8.2+)
- Tailwind CSS 3 + Alpine.js (Vite)
- MySQL / MariaDB
- DOKU Checkout (non-SNAP)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate

# Sesuaikan kredensial database di .env, lalu:
php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

Buka `http://localhost:8000`.

### Akun default hasil seeder

| Role  | Email                   | Password   |
| ----- | ----------------------- | ---------- |
| Admin | `admin@kasirfood.test`  | `password` |
| Kasir | `test@example.com`      | `password` |

> Ganti password akun default sebelum dipakai di production.

> Seeder tidak mengisi data menu. Katalog kiosk sepenuhnya dibaca dari database, jadi tambahkan kategori di `/admin/categories` dan produk di `/admin/products` sebelum kiosk dipakai.

## Konfigurasi DOKU

Isi variabel berikut di `.env`:

```env
DOKU_CLIENT_ID=BRN-xxxxxxxx
DOKU_SECRET_KEY=SK-xxxxxxxx
DOKU_IS_PRODUCTION=true
DOKU_PAYMENT_DUE_DATE=15
DOKU_PAYMENT_METHODS=
```

- `DOKU_CLIENT_ID` dan `DOKU_SECRET_KEY` diambil dari dashboard DOKU (Client ID produksi diawali `BRN`, secret key diawali `SK-`).
- `DOKU_IS_PRODUCTION=true` memakai `https://api.doku.com`; jangan diubah ke sandbox untuk produksi.
- `DOKU_PAYMENT_DUE_DATE` dalam menit, `DOKU_PAYMENT_METHODS=QRIS` agar halaman DOKU Checkout hanya menampilkan QRIS.
- Daftarkan URL di dashboard DOKU:
  - Notification URL: `https://domain-anda.com/doku/notification`
  - Callback/return URL: `https://domain-anda.com/order/{order_number}`
  - Aktifkan auto-redirect agar pelanggan kembali ke halaman status pesanan.
- Jika kredensial belum diisi, opsi QRIS otomatis dinonaktifkan dan pelanggan tetap bisa memakai kartu EDC atau membayar tunai di kasir.

## Alur pembayaran

1. Pelanggan memilih menu dan mengisi nama.
2. **QRIS** — server membuat order `pending` lalu meminta URL pembayaran DOKU Checkout. Kiosk mengalihkan pelanggan ke halaman pembayaran QRIS DOKU. Status berubah menjadi `paid` setelah webhook `/doku/notification` diterima.
3. Setelah menekan **Kembali ke Merchant**, pelanggan mendarat di `/order/{order_number}`. Jika belum dibayar, halaman menawarkan **Lanjutkan Pembayaran QRIS** (memakai URL DOKU yang tersimpan) dan **Batalkan Pesanan**. Jika sudah lunas, nomor antrean ditampilkan.
4. Halaman status memantau status tiap 5 detik dan kembali ke beranda otomatis setelah 45 detik tanpa aktivitas.
5. **Kartu Debit / Kredit (EDC Kiosk)** — order dibuat `pending` dengan metode `edc`, lalu kiosk menampilkan instruksi tap kartu pada terminal EDC beserta nominal total. Tombol **Simulasi Tap EDC Berhasil** (mode demo tanpa perangkat fisik) memanggil `POST /order/{order_number}/edc/simulate` dan langsung menampilkan struk nomor antrean lunas. Di produksi, kasir juga bisa menekan **Konfirmasi Lunas** di panel admin.
6. **Tunai di Kasir** — order dibuat `pending` dengan metode `cash` tanpa memanggil DOKU. Kiosk menampilkan nomor antrean besar dengan instruksi menuju kasir; kasir menekan **Konfirmasi Lunas** setelah uang diterima.

## Deployment (Railway)

1. Tambahkan environment variable: `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, kredensial database, dan kredensial DOKU (`DOKU_CLIENT_ID`, `DOKU_SECRET_KEY`, `DOKU_IS_PRODUCTION=true`).
2. Build command: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
3. Release/start command: `php artisan migrate --force && php artisan storage:link`
4. Pastikan `APP_TIMEZONE=Asia/Jakarta` agar format nomor antrean dan laporan harian sesuai.
5. Upload foto menu disimpan di `storage/app/public`. Pada Railway, pasang **Volume** yang di-mount ke `/app/storage/app/public` agar foto tidak hilang setiap deploy.

## Perintah berguna

```bash
php artisan migrate --seed   # migrasi + akun admin/kasir (tanpa data menu contoh)
npm run dev                  # Vite dev server
npm run build                # build asset production
php artisan test             # jalankan seluruh test
php artisan optimize:clear   # bersihkan cache config/route/view
```
