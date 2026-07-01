# Adidas Store

Website e-commerce sepatu bertema Adidas, dibuat pakai Laravel.

## Fitur

- Login & register
- Katalog produk + filter kategori
- Checkout selektif (pilih item dari keranjang)
- Pembayaran via Midtrans (Snap, QRIS, BCA VA, Alfamart, Indomaret)
- Cek ongkir pakai RajaOngkir Komerce API
- Status order: pending → paid → shipped → delivered
- Auto-complete order (scheduled command)
- Panel admin

## Tech Stack

- Laravel
- MySQL
- Midtrans
- RajaOngkir Komerce API

## Instalasi

```bash
git clone https://github.com/username/adidas.git
cd adidas

composer install
npm install

cp .env.example .env
php artisan key:generate

# setup database di .env, lalu:
php artisan migrate --seed
npm run build
php artisan serve
```

Tambahkan di `.env`:

```env
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false

RAJAONGKIR_API_KEY=
```

Untuk scheduler auto-complete order:

```bash
php artisan schedule:work
```

Untuk testing webhook Midtrans di lokal, pakai ngrok:

```bash
ngrok http 8000
```

## Author

Gilang Arya Leksana
