# Among

Among adalah aplikasi Laravel untuk manajemen pengguna multi-role. Autentikasi menggunakan Laravel Breeze, sedangkan role dan permission menggunakan Spatie Laravel Permission.

## Tech Stack

- PHP 8.3+
- Laravel 13.13
- Laravel Breeze
- Laravel Sanctum
- Spatie Laravel Permission
- Tailwind CSS
- Alpine.js
- Vite

## Fitur

- Autentikasi login, register, reset password, dan verifikasi email
- Multi-role dengan Spatie Laravel Permission
- Redirect dashboard berdasarkan role
- Manajemen user untuk admin
- Struktur awal siap dikembangkan menjadi sistem operasional yang lebih besar

## Instalasi

Clone project, lalu jalankan dependency PHP dan frontend:

```bash
composer install
npm ci
```

Copy file environment:

```bash
cp .env.example .env
```

Sesuaikan konfigurasi database di `.env`:

```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Generate application key:

```bash
php artisan key:generate
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
```

Seeder role membuat user awal berikut:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@mail.com | password |
| Editor | editor@mail.com | password |
| Guest | guest@mail.com | password |

## Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite untuk development:

```bash
npm run dev
```

Buka aplikasi di browser:

```text
http://127.0.0.1:8000
```

Untuk build asset production:

```bash
npm run build
```

## Testing

Jalankan test:

```bash
php artisan test
```

Catatan saat upgrade Laravel 13.13:

- `laravel/tinker` belum dipakai karena versi stabil yang tersedia masih konflik dengan Laravel 13.
- Jika test view gagal karena `public/build/manifest.json` tidak ditemukan, jalankan `npm run build` terlebih dahulu.
- Test bawaan profile delete membutuhkan method `destroy` di `ProfileController`.

## Lisensi

MIT License. Bebas digunakan, dimodifikasi, dan dikembangkan.
