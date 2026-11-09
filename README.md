# Mini Shop: Small E-commerce Management System

A small e-commerce management system built with **Laravel 12** (API, Sanctum) and **Vue 3** (SPA, Vite).

- **Admin** manages shops, products (shared catalog) and shop owner accounts.
- **Shop owners** browse the shared catalog, add products to their own shop with shop-specific price, details and thumbnail, and create new products that become available to everyone.
- **Customers** (no login) see only products assigned to an active shop, with shop-specific prices and thumbnails.

## Requirements

- PHP 8.2+ (extensions: mbstring, xml, curl, pdo_sqlite or pdo_mysql, fileinfo; gd or imagick is not required)
- Composer 2
- Node.js 20.19+ or 22.12+ and npm (required by Vite 7)
- MySQL 8+ or MariaDB 10.4+ (optional; SQLite is the default)

## Setup (step by step)

### 1. Clone and install dependencies

```bash
git clone <REPOSITORY_URL> ecommerce-system
cd ecommerce-system
composer install
npm install
```

### 2. Environment file

```bash
cp .env.example .env        # Windows (cmd): copy .env.example .env
php artisan key:generate
```

The example environment uses SQLite, which is sufficient for a fresh local setup. To use MySQL instead, open `.env` and set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create the database (MySQL only)

If you configured MySQL in the previous step, create an empty database named `ecommerce_db`:

```bash
mysql -u root -p -e "CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

(or create it from phpMyAdmin)

### 4. Migrate and seed

```bash
php artisan migrate:fresh --seed
```

### 5. Link storage (needed for thumbnail images)

```bash
php artisan storage:link
```

### 6. Build the frontend and run

**Option A: production build (one terminal, recommended):**

```bash
npm run build
php artisan serve
```

**Option B: development mode (two terminals):**

```bash
# terminal 1
php artisan serve
# terminal 2
npm run dev
```

Open **http://localhost:8000**

> If you change frontend code while using Option A, run `npm run build` again.

## Demo accounts

| Role | Email | Password | Shop |
|---|---|---|---|
| Admin | admin@gmail.com | password | n/a |
| Shop owner | owner1@gmail.com | password | Tech Hub |
| Shop owner | owner2@gmail.com | password | Fashion Corner |

Seeded data: 3 users, 2 shops, 10 catalog products. 3 products (Sunglasses, Backpack, Smart Watch) are intentionally not assigned to any shop, so customers cannot see them.

## Features

**Admin** (`/admin`)
- Login / logout (Sanctum token auth)
- Shops: create, edit, delete, activate/deactivate, assign owner, search, pagination
- Products (shared catalog): create, edit, delete, thumbnail upload, search, pagination
- Shop owners: create, edit, delete, assign shop

**Shop owner** (`/owner`)
- Browse the shared catalog (products from admin and other owners), search, pagination
- Add a catalog product to own shop with own price, name, description and thumbnail
- Edit or remove own listings, show/hide to customers
- Create a new product: it goes into the shared catalog and is auto-listed in own shop
- Cannot touch other shops' listings (403)

**Customer** (no login)
- `/` list of active shops
- `/shops/{slug}` products of one shop with shop-specific price and thumbnail
- `/products` all visible products with shop name
- Products not assigned to a shop, hidden listings and inactive shops are never shown

## Design notes

| Table | Purpose |
|---|---|
| `users` | `role` = `admin` or `shop_owner` |
| `shops` | name, unique slug, `owner_id`, `is_active` |
| `products` | shared catalog: name, description, base_price, thumbnail, `created_by` |
| `shop_products` | a shop's listing of a product: price, optional name/description/thumbnail overrides, `is_active`. Unique on (`shop_id`, `product_id`) |

Customers read only from `shop_products`, so a product with no listing is automatically invisible. If a listing has no custom name, description or thumbnail, the catalog values are used as fallback.

## API overview

| Area | Endpoints |
|---|---|
| Auth | `POST /api/login`, `POST /api/logout`, `GET /api/me` |
| Admin (`role:admin`) | `/api/admin/shops`, `/api/admin/products`, `/api/admin/shop-owners` (apiResource) |
| Owner (`role:shop_owner`) | `GET/POST /api/owner/catalog`, `/api/owner/listings` (apiResource) |
| Public | `GET /api/shops`, `GET /api/shops/{slug}/products`, `GET /api/products` |

File uploads on update use `POST` with `_method=PUT` (multipart form-data).

## Troubleshooting

- **Images not showing:** run `php artisan storage:link`.
- **Blank page / Vite manifest error:** run `npm run build` (or keep `npm run dev` running).
- **SQLSTATE connection refused / unknown database:** check MySQL is running and `.env` DB values are correct, and the database exists.
- **Seeder fails with duplicate email:** use `php artisan migrate:fresh --seed` (not `--seed` alone).
- **Changed `.env`:** run `php artisan config:clear`.