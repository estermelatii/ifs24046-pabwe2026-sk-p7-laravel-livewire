# Stock Barang — Laravel 13 + Livewire 4 (Feature-First)

Studi kasus PABWE 2026 P7: aplikasi inventaris stok barang (domain Todo → Stock).

## Menjalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pabwe_p7_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# sesuaikan DB_* dan OPENAI_API_KEY di .env
php artisan migrate:fresh --seed
php artisan storage:link
npm install --ignore-scripts && npm run build
php artisan serve
```

Akun seeder: `admin` / `admin` dan `member` / `member`.

## Pengujian

```bash
composer test                       # semua test
php artisan test --coverage --min=100   # gate coverage (butuh pcov/xdebug)
composer audit
```

Test memakai SQLite in-memory (`phpunit.xml`), jadi tidak butuh MySQL.

## Struktur

Lihat `app/Features/{Admin,Ai,Auth,Profile,Stock}` — tiap fitur berisi Livewire, Services, dst.
Alias middleware: `role`, `owns.stock` (di `bootstrap/app.php`). Rate limiter: `1000-per-second`.
