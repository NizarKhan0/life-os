# Panduan Elak Error GitHub Actions (CI) & Versi PHP Laravel

Nota rujukan bila buat projek Laravel baru (terutamanya bila guna Starter Kit seperti Livewire / Vue / React) dan `push` ke GitHub.

---

## 1. Adakah Wajib Ada GitHub Actions (`tests.yml`)?

**Tidak wajib.**

Laravel Starter Kit versi terkini datang sekali dengan fail `.github/workflows/tests.yml` secara automatik. Fungsi fail ini adalah untuk menjalankan pemeriksaan automatik (Laravel Pint, PHPStan, dan PHPUnit/Pest) setiap kali kod di-`push` ke GitHub.

---

## 2. Punca Error `Your lock file does not contain a compatible set of packages`

Error seperti di bawah berlaku apabila versi PHP di komputer (Laragon) **tidak sama** dengan versi PHP yang ditetapkan di dalam GitHub Actions:

```text
Your lock file does not contain a compatible set of packages. Please run composer update.
Problem 1 - symfony/clock is locked to version v8.1.9 ... requires php >=8.4.1 -> your php version (8.3.36) does not satisfy that requirement.
```

### Kenapa ia berlaku?
1. **Di PC (Laragon):** Menggunakan versi PHP yang lebih baru (contohnya **PHP 8.5**).
2. Semasa menjalankan `laravel new` atau `composer install/update`, Composer mengesan PHP 8.5 lalu memuat turun pakej `symfony/*` versi **v8.1** (yang memerlukan **PHP >= 8.4.1**) dan menguncinya di dalam `composer.lock`.
3. **Di GitHub Actions:** Fail asal `.github/workflows/tests.yml` daripada template Laravel masih menggunakan `php-version: '8.3'`.
4. Apabila kod di-`push`, GitHub Actions cuba memasang pakej daripada `composer.lock` (PHP 8.4+) menggunakan PHP 8.3 lalu gagal dengan `exit code 2`.

---

## 3. Step-by-Step Cara Elak Error Ini Pada Masa Akan Datang

Pilih **salah satu** daripada 3 cara di bawah setiap kali memulakan projek Laravel baru:

### Cara 1: Samakan Versi PHP di GitHub Actions (Disyorkan jika mahu guna CI)

1. Semak versi PHP semasa di Laragon:
   ```bash
   php -v
   ```
2. Buka fail `.github/workflows/tests.yml`.
3. Kemas kini `php-version` daripada `'8.3'` kepada `'8.4'` (atau `'8.5'` mengikut keperluan projek):
   ```yaml
   - name: Setup PHP
     uses: shivammathur/setup-php@eb7c497e18156a6bbabfef1d3a82760b9eda3962 # v2
     with:
       php-version: '8.4'
       tools: composer:v2
       coverage: none
   ```
4. Buka fail `composer.json` dan selaraskan syarat versi PHP di bahagian `require`:
   ```json
   "require": {
       "php": "^8.4",
   ```
5. Kemas kini hash pada `composer.lock`:
   ```bash
   composer update --lock
   ```

---

### Cara 2: Kunci `platform.php` dalam `composer.json` (Jika server/hosting guna PHP 8.3)

Jika PC (Laragon) menggunakan PHP 8.5 tetapi server produksi atau CI wajib menggunakan PHP 8.3:

1. Buka `composer.json` dan tambah tetapan `"platform"` di dalam blok `"config"`:
   ```json
   "config": {
       "optimize-autoloader": true,
       "preferred-install": "dist",
       "sort-packages": true,
       "platform": {
           "php": "8.3.16"
       },
       "allow-plugins": {
           "pestphp/pest-plugin": true,
           "php-http/discovery": true
       }
   }
   ```
2. Jalankan semula:
   ```bash
   composer update
   ```
   > **Nota:** Dengan tetapan `platform.php`, Composer akan memuat turun pakej yang serasi dengan PHP 8.3 sahaja walaupun Laragon sedang menjalankan PHP 8.5.

---

### Cara 3: Padam GitHub Actions (Jika tidak mahu auto-test di GitHub)

Jika projek hanyalah untuk simpanan kod dan anda tidak mahu GitHub menjalankan ujian automatik setiap kali `git push`:

1. Padam fail `.github/workflows/tests.yml` (atau keseluruhan folder `.github`).
2. `git add .`, `git commit`, dan `git push` seperti biasa.

