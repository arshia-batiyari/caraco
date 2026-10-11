# KARAKO technical architecture

Phase 1 records the audit and the foundation. The holding site publishes its public pages from the database. The tourism application now has its own catalog, consultation inquiries, customer feedback, surveys, medical tourism, attractions, and admin panel.

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

Holding tables now migrated:

- `settings` for the company name, contact email and phone, hero and sharing images, and the optional introduction video
- `profile_translations` for the localized company summary
- `homepage_translations` for hero, introduction, approach, and homepage SEO
- `divisions` and `division_translations` (nine divisions, ordered)
- `pages` and `page_translations` for about and other informational pages
- `projects` and `project_translations`
- `navigation_items` and `navigation_item_translations`
- `social_links`
- `contact_translations`
- `inquiries`

Tourism tables now migrated in `karako_tourism`:

- `settings` and `homepage_translations` for the public homepage, optional video, and contact details
- `destinations` and `destination_translations` (country, region, or city)
- `categories`, `category_translations`, `travel_styles`, and `travel_style_translations`
- `transport_options` and `transport_option_translations` (airplane, train, bus, ground, combined)
- `services` and `service_translations` (included or excluded)
- `hotels`, `hotel_translations`, `hotel_facilities`, `hotel_facility_translations`, and `hotel_images`
- `tours`, `tour_translations`, `tour_departures`, `tour_images`
- `tour_transport`, `tour_service`, `tour_hotels`, and `tour_hotel_translations`
- `itinerary_days`, `itinerary_day_translations`, `route_points`, and `route_point_translations`
- `faqs` and `faq_translations` (global, or attached to one tour)
- `pages` and `page_translations`
- `inquiries`
- `feedback_submissions`, `feedback_reviews`, and `feedback_follow_ups`
- `surveys`, `survey_translations`, `survey_questions`, `survey_question_translations`
- `survey_options`, `survey_option_translations`, `survey_responses`, and `survey_answers`
- `medical_pages`, `medical_categories`, `medical_services`, `medical_providers`, `medical_doctors`, `medical_steps`, `medical_faqs`, and `medical_inquiries`, each with translation rows where the text is localized
- `attraction_categories`, `attractions`, `attraction_images`, and `attraction_tour`

Accommodation uses a free-text category. The catalog does not store star ratings. There is no booking or payment table.

Medical tourism is a separate catalog: categories, services, clinics, optional doctor profiles, journey steps, questions, and consultation requests. The public form stores a name, contact details, an optional country, a requested service, and a question. It does not accept files or medical records. The page always states that the section is informational and is not medical advice. The application does not generate prices, credentials, or outcomes.

Attractions are editor-managed places linked to a destination and an optional category. The words “UNESCO World Heritage Site” are shown only when the designation is World Heritage and the verified box is checked. A cultural, natural, or mixed classification does not by itself mean a UNESCO listing. Inscription dates and reference numbers are shown only on that verified listing. Filtered attraction URLs are `noindex`; the unfiltered index and published detail translations are in the sitemap.

Customer feedback is stored separately from the catalog. A complaint never receives a public review row. Other submissions stay pending until an editor approves a review. Anonymous submissions discard the typed name. Contact details, internal notes, and recorded replies stay on the admin record. Surveys are reusable and localized. An anonymous survey does not store a name, email, or IP with the answers. A survey that already has answers keeps its questions and anonymity setting. Participation pages and filtered feedback URLs are `noindex`. The unfiltered feedback page is in the sitemap. No aggregate rating is added to JSON-LD.

Translations are rows, not JSON blobs: `locale`, `slug`, fields, and SEO columns (`meta_title`, `meta_description`). A unique index on `(parent_id, locale)` and a unique index on `(locale, slug)` keep localized URLs stable. Empty optional relations are not rendered.

## Languages

Default locale is `fa`. Fallback is `en`. Persian and Arabic use `dir="rtl"`. English and Russian use `dir="ltr"`.

Public routes are prefixed with the locale (`/fa/...`, `/en/...`). Slugs are stored per locale. Interface strings live in `lang/{fa,en,ru,ar}`. Editorial content lives in the translation tables. A missing translation falls back to English on listings, then is omitted if the English row is also empty. A detail page renders only the translation that belongs to that URL.

## Authentication and authorization

Each app has its own `users` table and session guard. There is no single sign-on in the first release. Sharing accounts would force a shared database or a custom token service, which makes separate deploys harder.

Roles, added with the admin phase:

- Holding: `admin`, `editor`
- Tourism: `admin`, `editor`

Policies gate each content model. Guests can submit inquiries. Authentication uses Laravel's session guard, hashed passwords, CSRF, and validated uploads. Admin routes sit behind `auth` and a role middleware. No API tokens until a client needs them.

## Frontend and assets

Pages are Blade. Vite compiles `resources/css/app.css` and `resources/js/app.js` into `public/build`. Tailwind 4 is the utility layer already in the Laravel 12 skeleton. It is build-time CSS, not a browser framework and not a CDN.

Production servers never run `npm` or Vite. Build locally or in CI, then upload `public/build` with the release. `public/build` stays gitignored.

Fonts are OFL families bundled by Vite from `@fontsource` (build time only): Vazirmatn for Persian and Arabic, Literata for Latin and Cyrillic headings, Source Sans 3 for Latin and Cyrillic text. Do not load Google Fonts or other third-party stylesheets at runtime.

Visual direction: charcoal and near-black surfaces, gold as an accent, ivory text areas, neutral gray. Tokens, components, and sample pages are documented in [DESIGN.md](DESIGN.md). The official logo file was not in the repository; the header uses a typographic wordmark until that file is added. Do not invent a geometric symbol. Gold is not a body-text color on ivory; small gold text uses `#7a5c32`.

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

`robots.txt` allows public locale pages and disallows `/admin`. The sitemap lists published translations. Filtered tour listings are `noindex`, and their canonical URL is the unfiltered listing.

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
