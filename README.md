# Vending Machine System

## Overview

This project is a PHP 8.2 vending machine system with two application surfaces:

- a server-rendered web application for session-authenticated users
- a JSON REST API for JWT-authenticated clients

The web experience is split by role:

- `Admin` users manage products from `/products`
- `User` users browse products from `/catalog`
- product purchases use SEO-friendly web URLs in the form `/products/{id}-{slug}/purchase`

## Core Features

- Web login/logout with PHP sessions
- JWT login/register for the API
- Admin product CRUD
- User product catalog with card-style UI
- Catalog filter by product name
- Catalog sorting by name and price
- Catalog pagination
- Purchase workflow with stock deduction and transaction logging
- Transaction and user monitoring for admins
- PHPUnit unit and integration coverage

## Roles And Access

### Admin

- Access `/products` for product management
- Create, update, and delete products
- Access `/users`
- Access `/transactions`
- Use admin-only API routes under `/api/users`, `/api/products`, and `/api/transactions`

### User

- Access `/dashboard`
- Access `/catalog`
- Filter, sort, and paginate the product catalog
- Purchase products from `/products/{id}-{slug}/purchase`
- Purchase products through `POST /api/products/{id}/purchase`

### Guest

- Can access `/` and `/login`
- Is redirected to `/login` from protected web routes

## Web Routes

### Public

- `GET /`
- `GET /login`
- `POST /login`

### Authenticated

- `POST /logout`
- `GET /dashboard`

### User-only Web Routes

- `GET /catalog`
- `GET /products/{id}-{slug}/purchase`
- `POST /products/{id}-{slug}/purchase`

Catalog query parameters:

- `name` for product-name filtering
- `sort` with `name_asc`, `name_desc`, `price_asc`, `price_desc`
- `page` for pagination

### Admin-only Web Routes

- `GET /users`
- `GET /transactions`
- `GET /products`
- `GET /products/create`
- `POST /products`
- `GET /products/{id}`
- `GET /products/{id}/edit`
- `POST /products/{id}/update`
- `POST /products/{id}/delete`

## API Routes

### Public API

- `GET /api/health`
- `POST /api/auth/login`
- `POST /api/auth/register`

### JWT-Protected API

- `GET /api/dashboard`
- `POST /api/products/{id}/purchase`

### Admin-only JWT API

- `GET /api/users`
- `GET /api/products`
- `GET /api/products/{id}`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`
- `GET /api/transactions`

The API purchase endpoint remains `/api/products/{id}/purchase`, but it is now protected for authenticated `User` tokens instead of `Admin` tokens. The SEO-friendly slugged purchase URL change still applies only to the web application.

## Web Purchase Flow

1. Sign in as a `User`.
2. Open `/catalog`.
3. Optionally filter by name or change the sort order.
4. Click `Buy now` for an in-stock item.
5. The application routes to `/products/{id}-{slug}/purchase`.
6. Submit the quantity.
7. On success, stock is reduced and a transaction is recorded.

Out-of-stock products render a disabled purchase state instead of an active purchase button.

## Postman Collection

The collection is stored at `postman/VendingMachineAPI.postman_collection.json`.

It now includes:

- API requests for authentication, products, users, dashboard, and transactions
- API purchase requests using the standard user JWT token
- web reference requests for `/catalog`
- web reference requests for the SEO-friendly purchase URL `/products/{id}-{slug}/purchase`

Collection variables:

- `base_url` default `http://localhost:8000`
- `jwt_token` for a standard user API token
- `admin_jwt_token` for an admin API token
- `product_id` for slugged web purchase requests
- `product_slug` for slugged web purchase requests
- `web_session_cookie` for session-authenticated web requests in Postman

For the web reference requests, set `web_session_cookie` to a valid session cookie such as `PHPSESSID=...` after logging in through the browser or another web login flow.

## Local Setup

### 1. Install dependencies

```bash
composer install
```

### 2. Configure environment

Provide database and app settings through `.env`, `.env.example`, or your local environment variables.

Common keys:

- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `SESSION_NAME`
- `JWT_SECRET`
- `JWT_TTL`

### 3. Create and seed the database

Run:

1. `database/schema.sql`
2. `database/seed.sql`

For isolated integration testing, use `.env.testing` and the test schema/seed scripts.

### 4. Start the app

```bash
php -S localhost:8000 -t public
```

Then open:

- Web UI: `http://localhost:8000`
- API health: `http://localhost:8000/api/health`

## Running Tests

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

## Deploying To Render With Docker

This repository now includes a `Dockerfile` and `render.yaml` Blueprint for a Docker-based Render web service.

The Blueprint is configured for the Render free tier and uses the Dockerfile's existing startup command, which binds PHP's built-in server to Render's required `PORT` environment variable.

Use these settings when creating a Render web service:

- Environment: `Docker`
- Instance type: `free`
- Dockerfile path: `./Dockerfile`
- Health check path: `/api/health`

If you use the Blueprint flow, Render can read these settings directly from `render.yaml`.

Set these environment variables in Render:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` to your Render service URL
- `DB_HOST` for your external MySQL-compatible database
- `DB_PORT` usually `3306`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `DB_CHARSET` usually `utf8mb4`
- `SESSION_NAME`
- `JWT_SECRET`
- `JWT_TTL`

The Blueprint already generates `JWT_SECRET` and prompts for sensitive values such as database credentials during initial creation.

The container excludes your local `.env` from the image, so Render environment variables are the source of truth in production.

Free-tier notes:

- Render free web services can cold start after inactivity.
- Render free web services do not include MySQL, so this app requires an external MySQL-compatible database.
- PHP sessions are file-based in the container, so users can be logged out after instance restarts or sleeps.

Suggested deploy flow:

1. Push this repository with `Dockerfile`, `.dockerignore`, and `render.yaml` committed.
2. In Render, create a new Blueprint or Web Service from the repository.
3. Keep the detected Docker configuration and confirm the free instance plan.
4. Supply the external MySQL connection values when Render prompts for env vars.
5. After the first deploy completes, open `/api/health` to confirm the service is healthy.

## Current Validation State

The current implementation passes the PHPUnit suite.

## Notes

- The catalog UI is the intended product-browsing path for standard users.
- The admin product table is a management surface, not the shopper flow.
- Web purchase URLs are slugged for readability and SEO friendliness.
- API product browsing remains admin-only, while API purchase is now available to authenticated `User` tokens.