# KARAKO Tourism

Laravel 12 application for the tourism subdomain. Architecture and deploy notes: [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md).

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan test
```

Set `APP_URL` and the MySQL `DB_*` values for this app only. The database name in `.env.example` is `karako_tourism`.
