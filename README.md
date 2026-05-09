# Vending Machine System Tester Overview

## Purpose

This project is a PHP 8.2 vending machine system with two entry points:

- A server-rendered web application for internal users.
- A JSON REST API for external or frontend clients.

For system testing, focus on authentication, role-based access control, product inventory changes, purchase transaction logging, validation behavior, pagination and sorting, and parity between web and API behavior.

## System Summary

- Language and runtime: PHP 8.2+
- Database: MySQL
- Data access: PDO
- Web authentication: PHP sessions
- API authentication: JWT bearer tokens
- Test framework: PHPUnit 11
- Frontend style: server-rendered PHP views under `views/`

The application bootstraps from `public/index.php`, loads routes from `routes/web.php` and `routes/api.php`, then dispatches requests through the custom router in `core/Router.php`.

## Functional Modules

### 1. Authentication

- Web login route: `POST /login`
- API login route: `POST /api/auth/login`
- API registration route: `POST /api/auth/register`
- Web login accepts either username or email, password.
- API login accepts `identifier` or `email`, password.

### 2. Product Management

- Create, list, view, edit, update, and delete products.
- Products store `name`, `price`, and `quantity_available`.
- Prices are stored as `DECIMAL(10,3)`.
- Product list supports pagination and sorting.

### 3. Purchase Flow

- A purchase reduces `products.quantity_available`.
- A successful purchase creates a row in `transactions`.
- Invalid quantity or insufficient stock should fail without partial updates.

### 4. User and Transaction Monitoring

- Admin users can review user listings and transaction listings.
- Transaction filtering supports `transaction_type`, `username`, and `product_name`.

## Roles And Access Expectations

Two roles exist in the system:

- `Admin`: can manage products, view users, and review transactions.
- `User`: can authenticate and browse dashboard.

Expected protection model from the codebase and tests:

- Guests should be redirected from protected web routes to `/login`.
- Admin-only web pages should render a forbidden view for authenticated non-admin users.
- Protected API routes should return `401` for missing or invalid tokens.
- Admin-only API routes should return `403` for authenticated users without the required role.

Important verification note:

- The current code and repository notes indicate web purchase should be available to authenticated users.
- The current `routes/web.php` file applies admin middleware to `/products/{id}/purchase` as well.
- Treat this as a high-value tester checkpoint because intended behavior and route configuration may diverge.

## Database Overview

The database schema defines three main tables:

### `users`

- `id`
- `username`
- `email`
- `password_hash`
- `role` with values `Admin` or `User`
- timestamps

### `products`

- `id`
- `name`
- `price`
- `quantity_available`
- timestamps

### `transactions`

- `id`
- `user_id`
- `product_id`
- `quantity`
- `unit_price`
- `total_amount`
- `transaction_type`
- `created_at`

Relationships:

- `transactions.user_id` references `users.id`
- `transactions.product_id` references `products.id`

## Web Routes For System Testing

### Public

- `GET /`
- `GET /login`
- `POST /login`

### Authenticated

- `POST /logout`
- `GET /dashboard`

### Admin-only web routes

- `GET /users`
- `GET /transactions`
- `GET /products`
- `GET /products/create`
- `POST /products`
- `GET /products/{id}`
- `GET /products/{id}/edit`
- `POST /products/{id}/update`
- `POST /products/{id}/delete`

### Purchase web routes to verify carefully

- `GET /products/{id}/purchase`
- `POST /products/{id}/purchase`

These routes are currently protected by admin middleware in routing, but the product UI and repository notes suggest they should be available to authenticated users. This is a likely functional discrepancy worth documenting during system test execution.

## API Routes For System Testing

### Public API

- `GET /api/health`
- `POST /api/auth/login`
- `POST /api/auth/register`

### JWT-protected API

- `GET /api/dashboard`

### Admin-only JWT API

- `GET /api/users`
- `GET /api/products`
- `GET /api/products/{id}`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`
- `POST /api/products/{id}/purchase`
- `GET /api/transactions`

## High-Value System Test Scenarios

### Authentication

- Verify guest access to protected web pages redirects to `/login`.
- Verify login fails when identifier or password is blank.
- Verify login succeeds with valid credentials and redirects to `/dashboard`.
- Verify logout clears session state.
- Verify API login returns a bearer token and user payload.
- Verify API register creates a new user with role `User`.

### Authorization

- Verify a `User` cannot open admin web pages such as `/products/create`, `/users`, and `/transactions`.
- Verify a `User` token receives `403` on admin-only API endpoints.
- Verify invalid or missing API bearer tokens receive `401`.

### Product Management

- Verify admin can create, edit, view, and delete products.
- Verify validation errors appear when `name` is empty, `price <= 0`, or `quantity_available < 0`.
- Verify product list sorting by `name`, `price`, and `quantity_available`.
- Verify pagination behavior on product list pages.

### Purchase And Inventory

- Verify successful purchase reduces stock and records a transaction.
- Verify out-of-stock items do not show an active purchase action in the web list.
- Verify purchase fails for non-positive quantity.
- Verify purchase fails when requested quantity exceeds stock.
- Verify transaction list reflects completed purchases.

### API Contract

- Verify API responses use JSON with `success`, `data`, and `message` shapes.
- Verify create and purchase endpoints return `201` on success.
- Verify validation failures return `422`.
- Verify missing resources return `404`.

## Local Setup For Testers

### 1. Configure environment

The application reads configuration from `.env` if present, otherwise `.env.example`.

Required keys:

- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `SESSION_NAME`
- `JWT_SECRET`
- `JWT_TTL`

### 2. Create and seed the database

Run the SQL scripts in this order:

1. `database/schema.sql`
2. `database/seed.sql`

For isolated integration testing, use `.env.testing` with:

- database name `vending_machine_test`
- separate session name
- separate JWT secret

### 3. Start the application

Example local server command:

```bash
php -S localhost:8000 -t public
```

Then open:

- Web UI: `http://example.com`
- API health check: `http://example.com/api/health`

## Running Automated Tests

Unit tests:

```bash
vendor/bin/phpunit -c phpunit.xml
```

Integration tests:

```bash
vendor/bin/phpunit -c phpunit.integration.xml
```

Composer shortcut:

```bash
composer test
```

## Known Testing Risks

- Seeded plaintext passwords are not documented; use the SQL reset above if needed.
- Web purchase authorization may not match intended role behavior.
- Integration tests depend on a separate MySQL database configured through `.env.testing`.
- API product access is currently admin-only, so API-based browsing as a standard user is not supported in the present route configuration.

## Suggested Test Evidence To Capture

- Screenshots of web access control behavior for guest, user, and admin sessions.
- Example API responses for `200`, `201`, `401`, `403`, `404`, and `422` cases.
- Before and after stock counts for a successful purchase.
- Matching transaction row created for the purchase.
- Any discrepancy between intended purchase access and actual route protection.