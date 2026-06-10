# Among

Among adalah baseline aplikasi Laravel untuk autentikasi, role, dan manajemen user. Proyek ini sengaja dibuat ringan sebagai titik awal sebelum modul bisnis baru dibangun.

## Tech Stack

- PHP 8.3+
- Laravel 13.13
- Laravel Breeze
- Laravel Sanctum
- Spatie Laravel Permission
- Tailwind CSS
- Alpine.js
- Vite

## Fitur Saat Ini

- Login, register, reset password, dan verifikasi email
- Dashboard berdasarkan role
- Role awal: `admin`, `editor`, dan `guest`
- Manajemen user untuk admin
- Edit profile dan hapus akun
- Primary key user memakai UUID
- Form auth/profile tidak memakai autofocus agar keyboard mobile tidak langsung terbuka

## Instalasi

Install dependency PHP dan frontend:

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

Jalankan migrasi dan seeder role/user:

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
```

Jika database lokal sebelumnya sudah pernah dibuat dengan integer ID, reset database agar schema UUID aktif:

```bash
php artisan migrate:fresh --seed
```

User awal:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@mail.com | 123456 |
| Editor | editor@mail.com | 123456 |
| Guest | guest@mail.com | 123456 |

## Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite untuk development:

```bash
npm run dev
```

Buka aplikasi:

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

## Catatan

- `laravel/tinker` belum dipakai karena versi stabil yang tersedia masih konflik dengan Laravel 13 saat upgrade dilakukan.
- Folder `public/build/` diabaikan oleh git karena merupakan hasil build Vite.

## Lisensi

MIT License. Bebas digunakan, dimodifikasi, dan dikembangkan.
