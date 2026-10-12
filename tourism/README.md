# KARAKO Tourism

Laravel 12 application for the tourism subdomain. Architecture: [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md). Shared-hosting steps: [../docs/DEPLOYMENT.md](../docs/DEPLOYMENT.md). This app: [DEPLOYMENT.md](DEPLOYMENT.md).

```bash
cp .env.example .env
php artisan karako:prepare
php artisan migrate
npm install
npm run build
php artisan test
```

Set `APP_URL` and the MySQL `DB_*` values for this app only. The database name in `.env.example` is `karako_tourism`.
