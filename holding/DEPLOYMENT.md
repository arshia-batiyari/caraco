# Holding site deployment

Shared-hosting steps that apply to both applications are in [../docs/DEPLOYMENT.md](../docs/DEPLOYMENT.md). This note is only the holding site.

## Identity

| Item | Value |
| --- | --- |
| Directory | `holding/` |
| Database | `karako_holding` (the panel may add an account prefix) |
| Document root | `holding/public`, or a copy of that directory’s contents |
| Sign-in | `/admin` |
| Locales | `/fa`, `/en`, `/ru`, `/ar` |

`APP_URL` is this site’s origin. `TOURISM_URL` is the tourism site’s origin, used for the tourism division link. Leave `TOURISM_URL` empty until that host exists. The link is omitted when it is empty.

## First-time commands

Run these in `holding/`, with PHP 8.2:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan karako:prepare
php artisan migrate --force
php artisan db:seed --force
php artisan karako:admin you@example.com
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`db:seed` writes the nine division titles and the default navigation in Persian, English, Russian, and Arabic. Descriptions are left empty for editors. The seed does not create a password or an administrator. The `karako:admin` command prints a generated password once. Keep it out of git and out of this file.

`karako:prepare` writes `APP_KEY` when it is empty and creates the relative `public/storage` link. If the host disables symlinks, set `FILESYSTEM_PUBLIC_ROOT` as described in the shared guide.

If the panel cannot point the domain at `public/`, copy only those public files into the web root, point `index.php` at this application, and set `KARAKO_PUBLIC_PATH` to that web root.

## What this site publishes

Homepage, nine divisions, about, contact, projects, and custom pages. Contact inquiries are stored for the team and are not emailed. Editors manage content. Only an administrator can manage users.

Unpublished records and missing translations return 404. They are omitted from `sitemap.xml`.
