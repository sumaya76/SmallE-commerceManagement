# Mini Shop

A small e-commerce management application built with **Laravel 12**, **Laravel Sanctum**, **Vue 3**, and **Vite**. Administrators manage shops, product catalog entries, and shop-owner accounts. Shop owners build their shop listings from a shared catalog. Customers can browse active shops and products without signing in.

**Public repository:** https://github.com/sumaya76/SmallE-commerceManagement

## What the application does

- **Administrators:** sign in to create, edit, activate, deactivate, and delete shops; manage the shared product catalog; and create shop-owner accounts and assign shops.
- **Shop owners:** browse products created by administrators and other owners; add products to their own shop with a shop-specific price and optional name, description, and image; create new catalog products that are automatically listed in their shop; and edit or hide their own listings.
- **Customers:** browse active shops, view products assigned to each shop, and see shop-specific prices and product details. Unlisted products, hidden listings, and listings in inactive shops are not shown.

## Requirements

- PHP **8.2 or newer** with Composer 2
- PHP extensions required by Laravel, including `mbstring`, `openssl`, `fileinfo`, `xml`, and **one of** `pdo_sqlite` or `pdo_mysql`
- Node.js **20.19+** or **22.12+**, and npm (Vite 7 requirement)
- A browser
- A database:
  - **SQLite** is the default and is the simplest option for local evaluation.
  - **MySQL 8+** or **MariaDB 10.4+** can be used instead.

Check installed versions before setup:

```bash
php -v
composer --version
node --version
npm --version
```

## Setup from a fresh clone

### 1. Clone the repository and install dependencies

```bash
git clone https://github.com/sumaya76/SmallE-commerceManagement.git
cd SmallE-commerceManagement
composer install
npm install
```

### 2. Create and configure the environment file

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux/Git Bash:**

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 3. Configure the database

#### Option A: SQLite (recommended for local setup)

The example `.env` is configured to use SQLite. A fresh clone needs an empty database file before migrations can run.

**Windows PowerShell:**

```powershell
if (-not (Test-Path database\database.sqlite)) {
    New-Item database\database.sqlite -ItemType File | Out-Null
}
```

**macOS/Linux/Git Bash:**

```bash
touch database/database.sqlite
```

If you change database settings or already have a Laravel configuration cache, clear it:

```bash
php artisan config:clear
```

#### Option B: MySQL or MariaDB

Create an empty database, for example `ecommerce_db`, using phpMyAdmin or a MySQL client:

```sql
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update the database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=
```

Use the username and password configured on your own database server. Then clear cached configuration:

```bash
php artisan config:clear
```

### 4. Run database migrations and load demo data

```bash
php artisan migrate:fresh --seed
```

This creates all tables and seeds the demo administrator, shop owners, shops, and sample catalog products. **Warning:** `migrate:fresh` deletes all existing tables and data in the configured database. Use it only with a new or disposable database.

### 5. Enable uploaded product images

```bash
php artisan storage:link
```

### 6. Build the frontend

```bash
npm run build
```

### 7. Start the application

Open a terminal in the project directory and run:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000** in your browser.

For frontend development with hot reload, use two terminals instead:

**Terminal 1 — Laravel:**

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 — Vite:**

```bash
npm run dev
```

Keep both processes running while using development mode. Stop them with `Ctrl+C`. For a normal local run, use the production build from step 6 and do not leave `npm run dev` running.

## Demo sign-in accounts

All seeded demo accounts use the password `password`.

| Role | Email | Shop |
|---|---|---|
| Administrator | `admin@gmail.com` | Not applicable |
| Shop owner | `owner1@gmail.com` | Tech Hub |
| Shop owner | `owner2@gmail.com` | Fashion Corner |

### Try the main workflows

1. Open the home page to browse active shops, or select **All Products** to see customer-visible listings.
2. Sign in as the administrator and visit `/admin` to manage shops, products, and shop owners.
3. Sign in as a shop owner and visit `/owner/catalog` to add a catalog product to that owner's shop.
4. Visit `/owner/create` to create a product. It is added to the shared catalog and automatically listed in the owner's shop.
5. Visit `/owner/products` to update a listing's price, details, image, or visibility.
6. Sign out and open a shop page to confirm customers only see active listings for that active shop.

## Features and screenshots

The screenshots below are real browser captures of the running application. They use local demo data; names, products, and counts may differ after you seed or edit your database. The create/edit dialogs are shown for documentation only; no records were created or changed to capture them. Shop-owner names and email addresses in screenshots are blurred.

### Product image and deletion behavior

- Catalog product thumbnails are shared defaults; a shop listing can have its own thumbnail override. When a listing has no override, it uses the catalog thumbnail.
- Thumbnail uploads accept JPG, JPEG, PNG, or WebP images up to 2 MB. Replacing or removing a thumbnail deletes its previous file.
- Removing a listing deletes its listing-specific thumbnail but leaves the shared catalog product intact.
- Deleting a catalog product also removes its shop listings. Their listing-specific thumbnails are deleted unless another product or listing still references the same file.

### Customer experience

- Browse active shops and search the shop directory.
- Browse customer-visible products across shops or open one shop to see its own listings.
- See shop-specific prices and details; products without a visible listing are excluded.
- Sign in to an administrator or shop-owner account.

<table>
<tr>
<td width="50%" valign="top"><strong>Shop directory</strong><br><img src="docs/screenshots/01-public-shop-directory.jpg" alt="Customer shop directory with active shops, search, product counts, and pagination" width="100%"><br><sub>Browse and search active shops, see visible listing counts, and use pagination.</sub></td>
<td width="50%" valign="top"><strong>All customer-visible products</strong><br><img src="docs/screenshots/02-public-products.jpg" alt="Public product catalog showing product images, prices, and shop names" width="100%"><br><sub>Product cards show images, taka prices, descriptions, and the shop selling each product.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Shop product page</strong><br><img src="docs/screenshots/03-public-shop-details.jpg" alt="Tech Hub public shop page with its assigned products" width="100%"><br><sub>Each shop page shows only its active listings and shop-specific prices.</sub></td>
<td width="50%" valign="top"><strong>Sign in</strong><br><img src="docs/screenshots/04-sign-in.jpg" alt="Mini Shop sign-in page" width="100%"><br><sub>Shared sign-in page for administrator and shop-owner accounts.</sub></td>
</tr>
</table>

### Administrator workspace

- Manage shops: create, edit, assign an owner, change customer visibility, and delete.
- Manage the shared product catalog: create, edit, search, upload thumbnails, and delete products.
- Manage shop-owner accounts: create accounts, assign shops, edit details, and remove accounts.

<table>
<tr>
<td width="50%" valign="top"><strong>Administrator overview</strong><br><img src="docs/screenshots/05-admin-overview.jpg" alt="Administrator overview with links to shop, product, and owner management" width="100%"><br><sub>Shortcuts to all administrator management areas.</sub></td>
<td width="50%" valign="top"><strong>Shop management</strong><br><img src="docs/screenshots/06-admin-shops.jpg" alt="Administrator shop list with owner information blurred" width="100%"><br><sub>Search shops, inspect listing counts and active status, and open shop edit/delete actions.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Create a shop</strong><br><img src="docs/screenshots/07-admin-create-shop.jpg" alt="New shop dialog with name, description, owner, and active controls" width="100%"><br><sub>Set a shop's details, assign an owner, and control customer visibility.</sub></td>
<td width="50%" valign="top"><strong>Shared product catalog management</strong><br><img src="docs/screenshots/08-admin-product-catalog.jpg" alt="Administrator product catalog table with price, creator, listing count, and actions" width="100%"><br><sub>Review catalog prices, product creators, shop assignments, and available actions.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Create a catalog product</strong><br><img src="docs/screenshots/09-admin-create-product.jpg" alt="New product dialog with product details, base price, and thumbnail upload" width="100%"><br><sub>Add catalog details, a base price, and an optional product thumbnail.</sub></td>
<td width="50%" valign="top"><strong>Shop-owner accounts</strong><br><img src="docs/screenshots/10-admin-shop-owners.jpg" alt="Shop-owner account table with personal account fields blurred" width="100%"><br><sub>Search and manage owner accounts and their shop assignments.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Create and assign an owner</strong><br><img src="docs/screenshots/11-admin-create-owner.jpg" alt="New shop-owner dialog with account details and shop assignment" width="100%"><br><sub>Create login credentials and optionally assign an available shop.</sub></td>
<td width="50%" valign="top"><strong>Role-based administration</strong><br><sub>Administrator screens are protected and available only to authenticated administrator accounts.</sub></td>
</tr>
</table>

### Shop-owner workspace

- Browse the shared catalog, including products created by administrators and other owners.
- Add a catalog product to the owner's own shop and choose its price, optional details, image, and visibility.
- Review, search, edit, hide, or remove the owner's shop listings without changing another shop's listings.
- Create a new product that becomes part of the shared catalog and is automatically listed in the owner's shop.

<table>
<tr>
<td width="50%" valign="top"><strong>Shop-owner overview</strong><br><img src="docs/screenshots/12-owner-overview.jpg" alt="Shop-owner overview with catalog, listing, and product-creation shortcuts" width="100%"><br><sub>Quick access to shared catalog browsing, shop listings, and product creation.</sub></td>
<td width="50%" valign="top"><strong>Shared catalog</strong><br><img src="docs/screenshots/13-owner-shared-catalog.jpg" alt="Shop owner browsing the shared catalog and seeing which products are already in their shop" width="100%"><br><sub>Catalog cards identify the creator and indicate whether the product is already in this shop.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Add a catalog product</strong><br><img src="docs/screenshots/14-owner-add-catalog-product.jpg" alt="Add-to-shop dialog with shop price, custom details, image, and visibility" width="100%"><br><sub>Set a shop-specific price and optional name, description, thumbnail, and visibility.</sub></td>
<td width="50%" valign="top"><strong>My shop listings</strong><br><img src="docs/screenshots/15-owner-my-products.jpg" alt="Shop owner's listing table with images, prices, visibility, and edit/remove actions" width="100%"><br><sub>Manage this shop's prices and visibility, or remove an item from the shop.</sub></td>
</tr>
<tr>
<td width="50%" valign="top"><strong>Edit a shop listing</strong><br><img src="docs/screenshots/16-owner-edit-listing.jpg" alt="Edit listing dialog for a wireless mouse with custom price, details, and visibility" width="100%"><br><sub>Update a listing without changing the shared catalog product.</sub></td>
<td width="50%" valign="top"><strong>Create a catalog product</strong><br><img src="docs/screenshots/17-owner-create-product.jpg" alt="Shop-owner create-product page with name, description, price, and thumbnail fields" width="100%"><br><sub>New products are shared with other owners and automatically listed in the creator's shop.</sub></td>
</tr>
</table>

## Application pages

| Page | URL | Access |
|---|---|---|
| Shop directory | `/` | Public |
| All customer-visible products | `/products` | Public |
| Products in a shop | `/shops/{shop-slug}` | Public |
| Sign in | `/login` | Public |
| Administrator overview | `/admin` | Administrator |
| Manage shops | `/admin/shops` | Administrator |
| Manage shared products | `/admin/products` | Administrator |
| Manage shop-owner accounts | `/admin/shop-owners` | Administrator |
| Shop-owner overview | `/owner` | Shop owner |
| Browse shared catalog | `/owner/catalog` | Shop owner |
| Manage own listings | `/owner/products` | Shop owner |
| Create a catalog product | `/owner/create` | Shop owner |

## API overview

All API routes use the `/api` prefix. Authenticated requests use a Laravel Sanctum bearer token.

| Area | Routes | Access |
|---|---|---|
| Sign in | `POST /api/login` | Public |
| Current user / sign out | `GET /api/me`, `POST /api/logout` | Authenticated |
| Shop directory and products | `GET /api/shops`, `GET /api/shops/{slug}/products`, `GET /api/products` | Public |
| Shop management | `/api/admin/shops` | Administrator |
| Product catalog management | `/api/admin/products` | Administrator |
| Shop-owner account management | `/api/admin/shop-owners` | Administrator |
| Browse/create catalog products | `GET/POST /api/owner/catalog` | Shop owner |
| Manage shop listings | `/api/owner/listings` | Shop owner |

The admin and owner endpoints use Laravel resource routes where applicable (`GET`, `POST`, `GET /{id}`, `PUT/PATCH /{id}`, and `DELETE /{id}`). Uploaded image updates use multipart `POST` requests with `_method=PUT`.

## Project structure

| Path | Purpose |
|---|---|
| `app/Http/Controllers/Api` | Authentication, admin, owner, and public API controllers |
| `app/Models` | User, shop, catalog product, and shop listing models |
| `database/migrations` | Database table definitions |
| `database/seeders/DatabaseSeeder.php` | Demo users, shops, catalog products, and listings |
| `resources/js` | Vue application, routes, shared components, and pages |
| `resources/css/app.css` | Application styles |
| `resources/views/app.blade.php` | Laravel entry point for the Vue single-page application |
| `public/images` | Public brand images |
| `storage/app/public` | Uploaded product thumbnails |
| `tests/Feature` | API workflow and pagination feature tests |

The `products` table stores the shared catalog. The `shop_products` table connects a catalog product to a shop and stores that shop's price, optional detail overrides, and visibility. Customers are served from active shop listings, not directly from the catalog.

## Tests and rebuilding

Run the backend test suite:

```bash
php artisan test
```

Rebuild frontend assets after changing Vue or CSS:

```bash
npm run build
```

## Project author

**Sumaya Akter**

Full-Stack Software Engineer

- **Portfolio:** [sumayaportfolio.lovable.app](https://sumayaportfolio.lovable.app/)
- **GitHub:** [github.com/sumaya76](https://github.com/sumaya76)
- **LinkedIn:** [linkedin.com/in/sumaya-1920921a5](https://www.linkedin.com/in/sumaya-1920921a5/)
- **Phone:** 01690253986
- **Email:** [sumaya0akter45@gmail.com](mailto:sumaya0akter45@gmail.com)

---

*Designed and developed with a focus on scalable architecture, secure APIs, and seamless user experiences.*

## Troubleshooting

- **`could not find driver`**: enable `pdo_sqlite` for SQLite or `pdo_mysql` for MySQL in the PHP configuration used by the command line; restart the terminal after changing `php.ini`.
- **SQLite database file missing**: create `database/database.sqlite` using the command in step 3.
- **MySQL connection refused / unknown database**: start MySQL/MariaDB, verify `.env` credentials, and create the database before migrating.
- **Environment changes not taking effect**: run `php artisan config:clear`.
- **Images do not appear**: run `php artisan storage:link` and confirm the uploaded files exist under `storage/app/public`.
- **Blank page or Vite manifest error**: run `npm run build`, or keep `npm run dev` running in a separate terminal.
- **Port 8000 is already in use**: start Laravel on another port, for example `php artisan serve --port=8080`, and open `http://127.0.0.1:8080`.
- **Seeder reports duplicate email addresses**: use a fresh development database and run `php artisan migrate:fresh --seed`. This deletes all data in that database.
