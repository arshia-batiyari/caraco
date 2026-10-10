# KARAKO

Two independently deployable Laravel 12 applications for the KARAKO holding company and its tourism division.

| Application | Directory | Database | Public URL |
| --- | --- | --- | --- |
| Holding website | `holding/` | `karako_holding` | `APP_URL` |
| Tourism platform | `tourism/` | `karako_tourism` | `APP_URL` |

Domains are environment values. Nothing in the repository hardcodes a production host.

The architecture, shared-hosting limits, and the decisions for Phase 1 are in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

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

## What is not in this phase

Company pages, division content, the tourism catalog, admin panels, and visual design start in later phases. The stock Laravel welcome page is still the public homepage.
