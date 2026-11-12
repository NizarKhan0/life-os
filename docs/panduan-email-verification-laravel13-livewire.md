# Panduan Email Verification & Log Emel (Laravel 13 + Livewire Starter Kit)

Nota rujukan apabila tersekat di halaman `http://127.0.0.1:8000/email/verify` (*"Please verify your email address by clicking on the link we just emailed to you"*) dan emel pengesahan tidak muncul di dalam `storage/logs/laravel.log`.

---

## 1. Kenapa Laravel 13 Livewire Starter Kit Wajib Verify Emel?

Secara lalai (*default*), **Laravel 13 Livewire Starter Kit** menggunakan **Laravel Fortify** yang mengaktifkan fungsi wajib sahkan emel (*Email Verification*) di 3 tempat:

1. **`app/Models/User.php`**
   Model `User` melaksanakan kontrak `MustVerifyEmail`:
   ```php
   class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
   ```
2. **`config/fortify.php`**
   Senarai `features` mengaktifkan `Features::emailVerification()`:
   ```php
   'features' => [
       Features::registration(),
       Features::resetPasswords(),
       Features::emailVerification(),
       ...
   ],
   ```
3. **`routes/web.php`**
   Laluan utama dilindungi oleh middleware `verified`:
   ```php
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::view('dashboard', 'dashboard')->name('dashboard');
   });
   ```

---

## 2. Kenapa Tekan "Resend Verification Email" Tak Keluar Dalam `storage/logs/laravel.log`?

Walaupun `.env` menetapkan `MAIL_MAILER=log`, emel tidak terus ditulis ke dalam `storage/logs/laravel.log` kerana tetapan queue di dalam `.env`:

```env
QUEUE_CONNECTION=database
```

### Apa yang berlaku di belakang tabir?
- Apabila `QUEUE_CONNECTION=database`, setiap emel (termasuk emel pengesahan) dimasukkan ke dalam jadual **`jobs`** di dalam pangkalan data terlebih dahulu.
- Jika anda hanya menjalankan `php artisan serve` (atau membuka projek terus melalui Laragon) **tanpa** menjalankan *queue worker*, emel tersebut tersangkut di dalam jadual `jobs` dan belum diproses ke dalam `storage/logs/laravel.log`.

---

## 3. Kenapa Keluar Error `Database file at path [life_os_dev] does not exist. (Connection: sqlite)`?

Error ini berlaku apabila anda menjalankan **`composer run dev`** (atau `php artisan queue:listen` / `php artisan serve`) **sebelum** menukar `.env` daripada `DB_CONNECTION=sqlite` kepada `DB_CONNECTION=mysql`:

```text
Database file at path [life_os_dev] does not exist. Ensure this is an absolute path to the database.
(Connection: sqlite, Database: life_os_dev, SQL: select * from "cache" where "key" in (laravel-cache-illuminate:queues:paused))
```

### Punca Sebenar:
- Proses induk `php artisan queue:listen` (di dalam `composer run dev`) telah menyimpan `DB_CONNECTION=sqlite` di dalam memori *environment variable* semasa ia mula-mula dijalankan.
- Apabila anda menukar `.env` kepada `DB_CONNECTION=mysql` dan `DB_DATABASE=life_os_dev` tanpa mematikan terminal `composer run dev`, proses `queue:listen` tadi masih memegang `DB_CONNECTION=sqlite` tetapi membaca `DB_DATABASE=life_os_dev`!

### Cara Selesaikan Serta-Merta:
1. Pergi ke terminal yang sedang menjalankan `composer run dev` / `php artisan serve` / `php artisan queue:listen`.
2. Tekan **`Ctrl + C`** untuk hentikan proses lama sepenuhnya.
3. Kosongkan cache konfigurasi:
   ```bash
   php artisan optimize:clear
   ```
4. Jalankan semula:
   ```bash
   composer run dev
   ```

---

## 4. Cara Penyelesaian Email Verification (Pilih Mengikut Keperluan)

### Cara A: Supaya Emel Terus Masuk `storage/logs/laravel.log` Serta-Merta (Disyorkan untuk Local Dev)
1. Buka fail `.env`.
2. Tukar `QUEUE_CONNECTION=database` kepada `sync`:
   ```env
   QUEUE_CONNECTION=sync
   ```
3. Restart `composer run dev` atau `php artisan serve`.

---

### Cara B: Verify Akaun Secara Terus Melalui Terminal (Pantas Tanpa Buka Log)
Jika malas membuka log untuk menyalin pautan pengesahan, jalankan arahan `tinker` ini di terminal:

```bash
php artisan tinker --execute="\App\Models\User::whereNull('email_verified_at')->update(['email_verified_at' => now()]);"
```
Selepas itu, terus *refresh* pelayar anda.

---

### Cara C: Matikan Terus Fungsi Wajib Verify Emel (Jika Projek Tidak Perlukan Pengesahan Emel)
Jika anda membina aplikasi dalaman/peribadi dan tidak mahu pengguna disekat oleh skrin `/email/verify`:

1. Buka `app/Models/User.php` dan buang `implements MustVerifyEmail`:
   ```php
   // Sebelum:
   class User extends Authenticatable implements MustVerifyEmail, PasskeyUser

   // Selepas:
   class User extends Authenticatable implements PasskeyUser
   ```
2. Buka `config/fortify.php` dan komenkan baris `Features::emailVerification()`:
   ```php
   'features' => [
       Features::registration(),
       Features::resetPasswords(),
       // Features::emailVerification(),
   ],
   ```
3. Buka `routes/web.php` dan buang `'verified'` daripada middleware (jika mahu):
   ```php
   Route::middleware(['auth'])->group(function () {
       Route::view('dashboard', 'dashboard')->name('dashboard');
   });
   ```
