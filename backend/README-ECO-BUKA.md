# Eco Buka Laravel Backend

This backend is a simple Laravel CMS/API for the Vue Eco Buka frontend.

## What It Provides

- Admin login
- Categories management
- Products management
- Product image uploads
- Hero banners
- Promo cards
- Showcase sections with selected products
- Navigation cards
- Feature banners
- Public API endpoints for the Vue frontend

It does not include cart, checkout, orders, payments, shipping, customer accounts, coupons, or inventory.

## Install Dependencies

Composer install was started during setup. If you need to reinstall:

```bash
cd backend
composer install --no-dev --prefer-dist
```

If Composer cache/disk space is a problem on Windows, use:

```powershell
$env:COMPOSER_CACHE_DIR="C:\Users\marin\OneDrive\Documents\New project\.composer-cache"
composer install --no-dev --prefer-dist
```

## Environment

Copy `.env.example` to `.env`, then set MySQL:

```env
APP_NAME="Eco Buka CMS"
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://127.0.0.1:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eco_buka
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

Create the MySQL database:

```sql
create database eco_buka character set utf8mb4 collate utf8mb4_unicode_ci;
```

Then run:

```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Admin URL:

```text
http://127.0.0.1:8000/admin
```

Default admin:

```text
Email: admin@ecobuka.test
Password: password
```

Change this password before going live.

## Vue Frontend Connection

In the Vue project `.env`, set:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

For live:

```env
VITE_API_BASE_URL=https://your-domain.com/api
```

Restart Vite after changing env values.

## API Endpoints

- `GET /api/categories`
- `GET /api/categories/{slug}`
- `GET /api/products`
- `GET /api/products/featured`
- `GET /api/products/search?query=delta`
- `GET /api/products/category/{slug}`
- `GET /api/products/{slug}`
- `GET /api/home/hero-banners`
- `GET /api/home/promo-cards/{section_key}`
- `GET /api/home/showcase/{section_key}`
- `GET /api/home/navigation-cards/{section_key}`
- `GET /api/home/feature-banners/{section_key}`
- `GET /api/homepage`

## Image Storage

Images are uploaded to Laravel public storage and served from:

```text
/storage/...
```

Run this once:

```bash
php artisan storage:link
```
