# KARAKO

Two independently deployable Laravel 12 applications for the KARAKO holding company and its tourism division.

| Application | Directory | Database | Public URL |
| --- | --- | --- | --- |
| Holding website | `holding/` | `karako_holding` | `APP_URL` |
| Tourism platform | `tourism/` | `karako_tourism` | `APP_URL` |

Domains are environment values. Nothing in the repository hardcodes a production host.

The architecture, shared-hosting limits, and the decisions for Phase 1 are in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md). The design system is in [docs/DESIGN.md](docs/DESIGN.md).

## Requirements

- PHP 8.2 (this repository resolves dependencies for 8.2.28)
- Composer 2
- MySQL 8 or MariaDB 10.6+, `utf8mb4`
- Node.js 22 and npm, used only to build assets before deploy

## Local setup

```bash
cd holding
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan test
```

Repeat the same steps in `tourism/`. Use a different `APP_URL`, `DB_DATABASE`, and application key for each app.

`QUEUE_CONNECTION=sync`, `CACHE_STORE=file`, and `SESSION_DRIVER=file` are intentional. Shared hosting is not assumed to provide Redis, Supervisor, or a queue worker.

## Sample pages

Both apps redirect `/` to `/fa`. The other locales are `/en`, `/ru`, and `/ar`.

The holding site publishes the homepage, divisions, about, contact, projects, and custom pages from the database. Unpublished or empty sections are omitted. Create an administrator with `php artisan karako:admin you@example.com` inside `holding/`, then sign in at `/admin`.

The tourism site publishes tours, destinations, categories, accommodation, consultation requests, and customer feedback from its own database. Empty homepage sections stay hidden. Create an administrator with `php artisan karako:admin you@example.com` inside `tourism/`, then sign in at `/admin`. Public foundations and credits pages remain available by direct URL and are kept out of the index. The public feedback page is `/{locale}/feedback`.

## Current limits

Tourism inquiries are consultation requests. The application does not confirm a reservation, take payment, or track seat inventory. Map tiles load only when `MAP_TILE_URL` is set on the server; otherwise the itinerary map is a drawn route plus the written day list. Inquiry mail is not sent. Feedback replies are stored for the team and are not emailed. Complaints are never published. Anonymous surveys do not store a name, email, or IP address. The admin interface is Persian, with English as the fallback for missing admin strings.
