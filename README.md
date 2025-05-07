# 👨‍💻 Laravel Multi-Role User Management

Proyek ini adalah sistem manajemen pengguna berbasis Laravel dengan autentikasi menggunakan **Laravel Breeze** dan manajemen peran menggunakan **Spatie Laravel Permission**.

---

## 🚀 Langkah Instalasi

Ikuti langkah-langkah berikut setelah clone project ini:

### 1. Install Dependency

```bash
composer install
```

### 2. Copy dan Konfigurasi `.env`

```bash
cp .env.example .env
```

Edit `.env` dan sesuaikan koneksi database:

```
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate App Key

```bash
php artisan key:generate
```

### 4. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 5. Jalankan Seeder Role & User Awal

```bash
php artisan db:seed --class=RoleSeeder
```

Seeder ini akan membuat 3 role (`admin`, `editor`, `guest`) dan masing-masing satu user default:

| Role  | Email             | Password |
|-------|-------------------|----------|
| Admin | admin@mail.com    | password |
| Editor| editor@mail.com   | password |
| Guest | guest@mail.com    | password |

---

## 📦 Fitur

- Laravel Breeze auth
- Multi-role dengan Spatie Laravel Permission
- Redirect otomatis berdasarkan role
- Panel dashboard per role
- Manajemen user khusus admin
- Toastr notification

---

## 🧑‍💻 Jalankan Aplikasi

```bash
php artisan serve
```

Buka di browser:

```
http://localhost:8000
```

---

## 🛠 Tech Stack

- Laravel 10
- Laravel Breeze
- Spatie Laravel Permission
- Bootstrap 5 + Toastr

---

## 📄 Lisensi

MIT License – bebas digunakan, dimodifikasi, dan dikembangkan.
