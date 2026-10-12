# KARAKO

Two independently deployable Laravel 12 applications for the KARAKO holding company and its tourism division. Each one has its own database, `APP_KEY`, admin users, and domain. They do not share a Composer package or a users table.

| Application | Directory | Database | Document root |
| --- | --- | --- | --- |
| Holding website | `holding/` | `karako_holding` | `holding/public` |
| Tourism platform | `tourism/` | `karako_tourism` | `tourism/public` |

Public pages live at `/{locale}` for `fa`, `en`, `ru`, and `ar`. Persian and Arabic are right to left. English and Russian are left to right. `/` redirects permanently to `/fa`.

Domains are environment values. Nothing in the repository hardcodes a production host or a password.

| Document | Contents |
| --- | --- |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Version choices, layout, and the rules the code follows |
| [docs/DESIGN.md](docs/DESIGN.md) | Visual tokens and components |
| [docs/ADMIN.md](docs/ADMIN.md) | How editors use the admin |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Shared-hosting deployment for both apps |
| [holding/DEPLOYMENT.md](holding/DEPLOYMENT.md) | Holding-only notes |
| [tourism/DEPLOYMENT.md](tourism/DEPLOYMENT.md) | Tourism-only notes |

## Requirements

- PHP 8.2 with `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `zip`. Composer resolves dependencies for platform 8.2.28. Do not use PHP 8.3-only syntax.
- Composer 2
- MySQL 8 or MariaDB 10.6+, `utf8mb4_unicode_ci`
- Node.js 22 and npm, used only to compile assets

Production does not need Redis, Supervisor, a queue worker, or Node on the server.

## Local development

Create two databases and a user that can use them. Copy `.env.example` in each application, then set `APP_URL`, `DB_DATABASE`, and `DB_PASSWORD` for that app only. The example password is empty on purpose.

```bash
cd holding
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8090
```

Repeat the setup in `tourism/` with a different database, key, and port (`8091`). Skip `db:seed` there; the tourism seeder does not insert content.

`php artisan test` uses SQLite in memory, as set in `phpunit.xml`. It does not use the MySQL databases above.

`QUEUE_CONNECTION=sync`, `CACHE_STORE=file`, and `SESSION_DRIVER=file` match the shared host. `composer run dev` starts Vite and a queue listener for local use. It is not a deploy command.

## Environment

Each app has its own `.env`, created from its `.env.example`. Values that must differ:

| Variable | Holding | Tourism |
| --- | --- | --- |
| `APP_NAME` | KARAKO Holding | KARAKO Tourism |
| `APP_KEY` | its own key | its own key |
| `APP_URL` | this site’s origin | this site’s origin |
| `DB_DATABASE` | `karako_holding` | `karako_tourism` |
| Related site | `TOURISM_URL` | `HOLDING_URL` |
| Map | — | `MAP_TILE_URL`, optional |

Production additionally sets `APP_ENV=production` and `APP_DEBUG=false`. When `APP_URL` starts with `https://`, generated links and the session cookie use HTTPS.

`KARAKO_PUBLIC_PATH` and `FILESYSTEM_PUBLIC_ROOT` stay empty when the domain points at `public/` and `php artisan karako:prepare` has created the storage link. The deployment guide explains when to set them.

## Migrations, seeders, and the first administrator

```bash
php artisan migrate
```

Holding’s seed creates the nine division titles and the default navigation. It does not create a user. Tourism’s seed is a placeholder.

```bash
php artisan karako:admin you@example.com
```

Run that inside `holding/` and again inside `tourism/`. The command hashes a generated password and prints it once. Do not pass `--password` on a shared shell if you can avoid it, and do not commit the password.

Sign in at `/admin`. An administrator can manage users. An editor can manage content and inquiries and cannot open user management. Policies enforce that, in addition to hiding the menu.

## Assets

```bash
npm install
npm run build
```

Vite writes `public/build`. Pages are Blade. The public JavaScript covers navigation, the optional video, admin tabs, and the tourism map. Fonts come from `@fontsource` at build time (Vazirmatn, Literata, Source Sans 3). Do not add a font CDN.

## Features

Holding: homepage, nine divisions, about, contact form, projects, custom pages, and inquiries.

Tourism: tour catalog with filters and pagination, discounts, itineraries, hotels, and transport; consultation requests; customer feedback, complaints, and surveys; medical tourism pages and inquiries; attraction listings. Map tiles load only when `MAP_TILE_URL` is set.

Both: four-language content, drafts, image upload with a re-encoded JPEG, XML sitemaps, `robots.txt`, canonical URLs, and `hreflang`. Admin and filtered URLs are kept out of the index. Drafts and missing translations return 404.

Editorial rules that the code enforces: no invented medical credentials, prices, outcomes, or UNESCO facts. The phrase “UNESCO World Heritage Site” is shown only when the designation is world heritage and an editor has marked it verified.

## Production

Follow [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md), then the note in `holding/DEPLOYMENT.md` or `tourism/DEPLOYMENT.md`.

The document root is `public/`, or a copy of that directory with `index.php` pointed at an application that lives outside the web root. `.env`, `vendor/`, and `storage/` (other than published images) are not web-accessible.

Before replacing a live release, dump the database and copy `storage/app/` and `.env`. Restore both the files and the dump together if you roll back.

## Tests

```bash
cd holding && php artisan test
cd tourism && php artisan test
```

## Limits

These are absent on purpose, or waiting on a host upgrade:

- Inquiry and feedback email. Replies are stored for the team.
- Booking confirmation, payment, and seat inventory.
- A Russian or Arabic admin chrome. Those languages are content. The admin screens are Persian, with English as the fallback.
- Drag-and-drop ordering, and an audit log of edits.
- Medical-record uploads on the consultation form.
- A queue worker. Work runs inside the request (`QUEUE_CONNECTION=sync`).
- A required cron entry. Nothing is scheduled yet.
- PHP 8.2 security fixes continue until 31 December 2026, and Laravel 12 security fixes until 24 February 2027. A move to Laravel 13 waits on PHP 8.3 on the host.

Search rankings are not a property of this repository. Titles, descriptions, canonical URLs, `hreflang`, sitemaps, and status codes are rendered so a crawler can read the public pages. Indexing still depends on the host, the content editors enter, and the search engine.
