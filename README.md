# Adidas Store

Adidas-themed shoe e-commerce website, built using Laravel.

## Features

- Login & Register
- Product Catalog + Category Filter
- Selective Checkout (Select Items from Cart)
- Payment via Midtrans (Snap, QRIS, BCA VA, Alfamart, Indomaret)
- Check Shipping Costs Using RajaOngkir Commerce API
- Order Status: Pending → Paid → Shipped → Delivered
- Auto-Complete Orders (Scheduled Command)
- Admin Panel

## Tech Stack

- Laravel
- MySQL
- Midtrans
- RajaOngkir Commerce API

## Installation

```bash
git clone https://github.com/username/adidas.git
cd adidas

composer install
npm install

cp .env.example .env
php artisan key:generate

# Setup Database in .env, then:
php artisan migrate --seed
npm run build
php artisan serve
```

Add to `.env`:

```env
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false

RAJAONGKIR_API_KEY=
```

For the auto-complete order scheduler:

```bash
php artisan schedule:work
```

To test the Midtrans webhook locally, use ngrok:

```bash
ngrok http 8000
```

## Author

Gilang Arya Leksana
