# KARAKO Holding

Laravel 12 application for the corporate site. Architecture and deploy notes: [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md).

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan test
```

Set `APP_URL`, `TOURISM_URL`, and the MySQL `DB_*` values for this app only. The database name in `.env.example` is `karako_holding`.

```bash
php artisan migrate
php artisan storage:link
php artisan karako:admin you@example.com
```

The last command creates an administrator and prints a password when one is not passed. Editorial content, the nine divisions, the optional homepage video, and contact inquiries are stored in the database. Sign in at `/admin`. Do not commit a password.
