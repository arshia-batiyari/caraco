# Tourism site deployment

Shared-hosting steps that apply to both applications are in [../docs/DEPLOYMENT.md](../docs/DEPLOYMENT.md). This note is only the tourism site.

## Identity

| Item | Value |
| --- | --- |
| Directory | `tourism/` |
| Database | `karako_tourism` (the panel may add an account prefix) |
| Document root | `tourism/public`, or a copy of that directory’s contents |
| Sign-in | `/admin` |
| Locales | `/fa`, `/en`, `/ru`, `/ar` |

`APP_URL` is this site’s origin. `HOLDING_URL` is the holding site’s origin. `MAP_TILE_URL` is optional. Leave it empty to draw the itinerary without a tile service. A key inside that template stays on the server; the browser requests `/map-tiles/{z}/{x}/{y}`.

## First-time commands

Run these in `tourism/`, with PHP 8.2:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan karako:prepare
php artisan migrate --force
php artisan karako:admin you@example.com
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run `db:seed` for content. The tourism seeder does not insert tours, prices, clinics, or accounts. Create the administrator with `karako:admin`, then enter records at `/admin`. The command prints a generated password once. Keep it out of git and out of this file.

`karako:prepare` writes `APP_KEY` when it is empty and creates the relative `public/storage` link. The same two commands run in the browser at `/setup` after upload. If the host disables symlinks, set `FILESYSTEM_PUBLIC_ROOT` as described in the shared guide.

If the panel cannot point the domain at `public/`, copy only those public files into the web root, point `index.php` at this application, and set `KARAKO_PUBLIC_PATH` to that web root.

## What this site publishes

Tours, destinations, categories, accommodation, consultation requests, customer feedback, surveys, medical tourism, and attractions. Consultation and medical forms store a request. They do not reserve a seat, take payment, or accept medical records. Complaints stay private. An attraction is labeled a UNESCO World Heritage Site only when an editor marks that designation as verified.

Drafts and missing translations return 404 and stay out of `sitemap.xml`. Filtered listing URLs send `noindex`.
