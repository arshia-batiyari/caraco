# KARAKO technical architecture

Phase 1 records the audit and the foundation. It does not implement the public websites or the admin panels.

## Audit

The checked-out branch contained only `README.md`. That workspace was not an existing Laravel application.

`origin/master` is a separate, unrelated tree named `holding/meydan` (a multi-vendor marketplace on Laravel 11). It has no `package.json`, no Vite config, no `.env.example`, and its Blade views are not in git. A `public/storage` symlink points at a production path outside the repository. That tree is left untouched. KARAKO is not built by editing it.

| Tool | Found on this machine |
| --- | --- |
| PHP | 8.3.6 CLI, with `mbstring`, `intl`, `gd`, `curl`, `zip`, `pdo_sqlite`, `pdo_mysql` |
| Composer | 2.7.1 |
| Node.js | 22.14.0 |
| npm | 10.9.9 |
| Database server | MariaDB 10.11.14, used here as the local MySQL-compatible server |

No logo file was present in the workspace. Later phases must use supplied logo files and must not redraw the mark.

## Version decision

| Choice | Version | Why |
| --- | --- | --- |
| PHP target | 8.2 | Required for the intended shared host. Composer `config.platform.php` is `8.2.28` in both apps. |
| Laravel | 12.69.3 (`^12.0`) | Last Laravel release that still runs on PHP 8.2. |
| MySQL | 8.0, or MariaDB 10.6+ | Separate schema per application, `utf8mb4_unicode_ci`. |
| Frontend | Blade, Vite 7, Tailwind CSS 4 | Compiled ahead of deploy. No React, Vue, or CDN assets. |

Laravel 13 requires PHP 8.3, so it is rejected while the host stays on 8.2. This machine's CLI is PHP 8.3.6, which can execute the apps, but new PHP code must stay valid on 8.2. Do not use 8.3-only syntax (`json_validate`, typed class constants, `#[\Override]`).

Support windows as of 10 October 2026:

- PHP 8.2 receives security fixes until 31 December 2026.
- Laravel 12 receives security fixes until 24 February 2027. Bug-fix support ended 13 August 2026.
- When the host offers PHP 8.3, plan a move to Laravel 13. That is a later upgrade, not part of Phase 1.

## Repository layout

```text
holding/     Laravel application for the corporate site
tourism/     Laravel application for the tourism subdomain
docs/        Architecture only. No application code.
```

Each application keeps the normal Laravel layout: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`. There is no shared `packages/` directory and no cross-app Composer path repository. Branding is copied as tokens and assets, not inherited through a common PHP package, so either app can be uploaded and upgraded alone.

`config/karako.php` in each app names the site (`holding` or `tourism`), reads the public URL from `APP_URL`, and lists `fa`, `en`, `ru`, and `ar` with direction.

## Database strategy

Two databases, two users if the host allows it:

- `karako_holding`
- `karako_tourism`

No foreign keys, views, or queries cross those databases. A record that must appear on both sites is entered twice or linked later by a public URL, not by a shared table.

Framework tables already shipped with Laravel 12 (`users`, `sessions`, `cache`, `jobs`) stay. Production configuration does not use the database for cache, sessions, or queues, so those extra tables are unused unless an operator opts in.

Planned holding tables, to be migrated in a later phase:

- `company_profiles` and `company_profile_translations`
- `divisions` and `division_translations` (nine divisions, fixed display order)
- `pages` and `page_translations` for informational division pages
- `media` for images and an optional introduction video
- `contact_details` and `social_links`
- `inquiries`

Planned tourism tables, also later:

- `tours`, `tour_translations`, `tour_departures`
- `hotels`, `hotel_translations`
- `attractions`, `attraction_translations`
- `itinerary_days`
- `transports`
- `medical_services`, `medical_service_translations`
- `media`
- `inquiries`

Translations are rows, not JSON blobs: `locale`, `slug`, fields, and SEO columns (`meta_title`, `meta_description`). A unique index on `(parent_id, locale)` and a unique index on `(locale, slug)` keep localized URLs stable. Empty optional relations are not rendered.

## Languages

Default locale is `fa`. Fallback is `en`. Persian and Arabic use `dir="rtl"`. English and Russian use `dir="ltr"`.

Public routes will be prefixed with the locale (`/fa/...`, `/en/...`). Slugs are stored per locale. Interface strings live in `lang/{fa,en,ru,ar}`. Editorial content lives in the translation tables. A missing translation falls back to English, then is omitted if the English row is also empty. Phase 1 only locks this contract in config and tests. It does not add the locale middleware or the tables.

## Authentication and authorization

Each app has its own `users` table and session guard. There is no single sign-on in the first release. Sharing accounts would force a shared database or a custom token service, which makes separate deploys harder.

Roles, added with the admin phase:

- Holding: `admin`, `editor`
- Tourism: `admin`, `editor`

Policies gate each content model. Guests can submit inquiries. Authentication uses Laravel's session guard, hashed passwords, CSRF, and validated uploads. Admin routes sit behind `auth` and a role middleware. No API tokens until a client needs them.

## Frontend and assets

Pages are Blade. Vite compiles `resources/css/app.css` and `resources/js/app.js` into `public/build`. Tailwind 4 is the utility layer already in the Laravel 12 skeleton. It is build-time CSS, not a browser framework and not a CDN.

Production servers never run `npm` or Vite. Build locally or in CI, then upload `public/build` with the release. `public/build` stays gitignored.

Fonts will be files under `public/fonts`, licensed for web use. Do not load Google Fonts or other third-party stylesheets at runtime.

Visual direction for later phases: charcoal and near-black surfaces, gold as an accent, ivory text areas, neutral gray. The supplied logo is placed, not recreated. Gold text on ivory must meet contrast; gold is not a body-text color.

## Images and video

The `public` disk (`storage/app/public`) stores uploads. `php artisan storage:link` creates `public/storage`. On shared hosting where symlinks are disabled, copy or map that path in the panel, or serve files through an authenticated controller for private originals and a public copy for published images.

Images: accept `jpg`, `png`, `webp`; limit size; store the original; generate a display derivative with GD, which is already installed. Do not add an image package until a phase needs it.

Video: an introduction video is optional. Store an uploaded `mp4` under the same disk or save an external URL. If neither is set, the template does not render the section. Do not assume FFmpeg or a video transcoding worker.

## Shared-hosting deployment

Each app is a normal PHP document root pointed at its `public/` directory.

```text
example.com            -> holding/public
travel.example.com     -> tourism/public
```

Those hostnames are examples. Set `APP_URL` per site. Keep the Laravel project root above the document root so `.env` and `vendor/` are not web-accessible.

Deploy steps:

1. `composer install --no-dev --optimize-autoloader` on PHP 8.2.
2. `npm ci && npm run build` before upload.
3. Upload the app, including `vendor/` and `public/build`, if the host has no Composer or Node.
4. Set `.env` (`APP_ENV=production`, `APP_DEBUG=false`, unique `APP_KEY`, MySQL credentials).
5. `php artisan migrate --force`
6. `php artisan storage:link`
7. Point the domain at `public/`.

Runtime choices for a host without root, Redis, or Supervisor:

- `QUEUE_CONNECTION=sync`
- `CACHE_STORE=file`
- `SESSION_DRIVER=file`
- `MAIL_MAILER` as provided by the host
- Scheduler: one cron entry, `php artisan schedule:run`, only after a phase adds scheduled work

`composer run dev` starts a queue listener and Vite. It is a local convenience, not a deploy command.

## Security

- `.env` is not committed. Each app has its own `APP_KEY`.
- `APP_DEBUG=false` in production.
- CSRF on every state-changing form.
- Form requests and policy checks for admin writes.
- Upload rules: extension, MIME type, and size. Store outside the document root until published.
- Inquiries are throttled.
- Passwords use the framework hasher.
- Security headers and HTTPS are set at the host. The apps trust `APP_URL` and do not hardcode scheme or host.
- Dependencies stay on the locked Laravel 12 set. No extra admin package until a phase needs one.

## SEO

Server-rendered HTML. Each localized URL has its own slug, title, meta description, canonical URL, and `hreflang` alternates for `fa`, `en`, `ru`, and `ar`. The document element carries the active `lang` and `dir`.

Division and tourism pages that are unpublished or have no translation are omitted from the sitemap and return 404. JSON-LD is added with real content only. No invented ratings, addresses, or organization facts.

`robots.txt` will allow public locale pages and disallow admin and inquiry-thank-you URLs. A sitemap is generated from published translations.

## Testing

PHPUnit 11, with `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` inside `phpunit.xml`. Tests do not need MySQL.

Phase 1 covers:

- The stock homepage responds with HTTP 200.
- `config/karako.php` exposes the four locales and the correct site key.

Later phases add feature tests for publishing, locale fallback, authorization, and inquiry validation. Run `php artisan test` in each app. Run `php artisan migrate` against MySQL or MariaDB before calling a schema change done. Check RTL and LTR in a browser when templates exist.

## Risks

| Risk | Mitigation |
| --- | --- |
| PHP 8.2 and Laravel 12 are in security-only maintenance | Stay on the locked 12.x line. Move to PHP 8.3 and Laravel 13 when the host allows it. |
| This machine runs PHP 8.3 while the host runs 8.2 | `config.platform.php` is 8.2.28. Do not adopt 8.3-only language features. |
| Shared hosts block symlinks, cron, or SSH | Document root is `public/`. File cache and file sessions. Queue is sync. Storage link has a non-symlink fallback. |
| `origin/master` looks like a Laravel app and is easy to reuse by mistake | Do not merge it. It is a different product and is missing its views. |
| Logo and company facts are not in the repo | Use labeled placeholders. Do not invent an address, a statistic, or a logo. |
| Two apps can drift visually | Repeat the same CSS tokens and font files. Do not extract a shared package until both designs have settled. |
| Built assets forgotten on deploy | `public/build` is required in the upload even though it is gitignored. |
