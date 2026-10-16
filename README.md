 # Phone E-Commerce — Backend Foundation

This is a **foundation overlay**, not the Laravel 12 framework source itself. Generate Laravel 12 into `backend/` first, then copy these foundation files on top.

## Requirements
Docker + Docker Compose, Git, and Composer or a temporary Composer container.

## Setup (Linux / WSL / macOS)

1. From repository root, generate a fresh Laravel 12 skeleton in an empty TEMP directory:
   `docker run --rm -v "$PWD:/workspace" -w /workspace composer:2 create-project laravel/laravel laravel-temp '^12.0'`
2. Copy Laravel files into `backend/` **without replacing this overlay's files**:
   `cp -an laravel-temp/. backend/` (on Linux GNU cp) then `rm -rf laravel-temp`
   Alternatively create Laravel first and then overlay this kit onto it, which is more portable.
3. `cp .env.example .env`
4. `cp backend/.env.foundation.example backend/.env`
5. Ensure `POSTGRES_PASSWORD` in root `.env` matches `DB_PASSWORD` in backend `.env`.
6. `docker compose build`
7. `docker compose run --rm --no-deps app composer install`
8. `docker compose up -d postgres redis`
9. `docker compose run --rm app php artisan key:generate`
10. `docker compose run --rm app php artisan migrate`
11. `docker compose up -d`
12. Visit `http://localhost:8000/api/v1/health`.

If using an existing Laravel 12 project, do not overwrite customized files blindly; merge `bootstrap/app.php` and `routes/api.php` into your existing code.

## Useful commands
- `docker compose ps`
- `docker compose logs -f app nginx queue`
- `docker compose exec app php artisan route:list --path=api`
- `docker compose exec app php artisan optimize:clear`
- `docker compose exec app php artisan migrate:status`
- `docker compose exec app php artisan tinker`

## Security / limitations
- Development-only credentials; replace before production. Do not commit `.env`.
- Enable HTTPS, disable `APP_DEBUG`, add secrets management, choose suitable permissions, restrict health endpoint details, and tune CORS before production.
- JWT and RBAC are Phase 2. Placeholder route files are not yet implemented.
- Queue should run after `composer install`, migrations and app key initialization.
- Nginx and app use bind mounts for development, not an optimized production image.
- For **JSON logs**, add `'tap' => [App\Logging\CustomizeJsonFormatter::class],` to the `stderr` channel array in `backend/config/logging.php`; the included tap class changes Monolog formatting. Use `LOG_CHANNEL=stderr`.
