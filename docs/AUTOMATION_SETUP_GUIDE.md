# 🤖 Panduan Lengkap Automasi & Deployment LifeOS (Local & Server)

Dokumentasi ini merangkumi senarai penuh automasi yang terbina dalam LifeOS, logik jadualnya, serta panduan langkah-demi-langkah cara menjalankannya di persekitaran **Local** dan **Production Server**, menggunakan **Cara Biasa** mahupun **Docker**.

---

## 📑 Isi Kandungan
1. [Senarai Penuh Automasi LifeOS (Apa yang Di-automate?)](#1-senarai-penuh-automasi-lifeos)
2. [Pra-Syarat & Konfigurasi Telegram Bot](#2-pra-syarat--konfigurasi-telegram-bot)
3. [Cara Menukar & Menetapkan Masa Jadual Automasi (Cth: 07:30 Pagi ke Masa Lain)](#3-cara-menukar--menetapkan-masa-jadual-automasi)
4. [Setup di Mesin Tempatan (Local / Development)](#4-setup-di-mesin-tempatan-local)
   - [Kaedah A: Cara Biasa (Artisan CLI / Laragon)](#kaedah-a-cara-biasa-local)
   - [Kaedah B: Cara Docker (Docker Compose Local)](#kaedah-b-cara-docker-local)
5. [Setup di Production Server (VPS / Cloud Ubuntu)](#5-setup-di-production-server-vps)
   - [Kaedah A: Cara Biasa (Linux Crontab & Supervisor)](#kaedah-a-cara-biasa-server)
   - [Kaedah B: Cara Docker (Production Compose Stack)](#kaedah-b-cara-docker-server)
6. [Pengujian, Pemantauan & Troubleshooting](#6-pengujian-pemantauan--troubleshooting)

---

## 1. Senarai Penuh Automasi LifeOS

LifeOS dilengkapi dengan sistem automasi proaktif yang beroperasi mengikut zon masa `Asia/Kuala_Lumpur` yang dikonfigurasikan di dalam `routes/console.php`.

| Perintah Artisan | Jadual / Kekerapan | Zon Masa | Fungsi & Penerangan Proaktif |
| :--- | :--- | :--- | :--- |
| `meetings:send-reminders` | **Setiap Minit** (`everyMinute`) | Sistem / Local | Mengimbas mesyuarat berstatus `scheduled` yang bakal bermula dalam tempoh amaran (contoh: 15 minit sebelum) dan menghantar peringatan berserta pautan Google Meet / Zoom ke Telegram. Elak bertindih dengan `withoutOverlapping()`. |
| `lifeos:send-briefing` | **Setiap Hari @ 07:30 AM** (`dailyAt('07:30')`) | `Asia/Kuala_Lumpur` | **Morning Executive Briefing Hub**: Merangkumkan agenda hari ini merangkumi mesyuarat harian, 3 tugasan terpenting (*top urgent/high priority tasks*), tabiat yang belum dilog, baki bersih akaun semasa (*net balance*), dan amaran komitmen/bil bulanan. |
| `goals:send-reminders` | **Setiap Hari @ 08:00 AM** (`dailyAt('08:00')`) | `Asia/Kuala_Lumpur` | Mengimbas matlamat aktif (*in progress*). Sekiranya tarikh sasaran matlamat bakal tiba dalam tempoh 7 hari atau peratusan kemajuan berada di bawah sasaran, amaran proaktif dihantar untuk mengekalkan fokus. |
| `subscriptions:check-due` | **Setiap Hari @ 09:00 AM** (`dailyAt('09:00')`) | `Asia/Kuala_Lumpur` | Memeriksa langganan & komitmen aktif (Netflix, Sewa, Server, dll.) yang bakal diperbaharui dalam 3 hari atau hari ini bagi mengelakkan bayaran tertunggak atau caj tanpa sedar. |
| `money:check-budgets` | **Setiap Hari @ 08:00 PM** (`dailyAt('20:00')`) | `Asia/Kuala_Lumpur` | Menilai jumlah perbelanjaan semasa berbanding bajet bulanan setiap kategori. Menghantar amaran jika penggunaan bajet mencecah atau melebihi **80% (Amaran Kuning)** atau **100% (Amaran Merah/Overbudget)**. |
| `tasks:send-reminders` | **Setiap Hari @ 09:00 PM** (`dailyAt('21:00')`) | `Asia/Kuala_Lumpur` | **Evening Wrap-up**: Mengingatkan tugasan tertunggak yang belum diselesaikan hari ini dan menyenaraikan amalan harian (*habits*) yang belum ditanda sebelum hari berakhir. |
| `reviews:send-weekly-reminder` | **Setiap Ahad @ 08:00 PM** (`weeklyOn(0, '20:00')`) | `Asia/Kuala_Lumpur` | Mendorong pengguna membuat refleksi mingguan (*Wins, Challenges, Improvements*) dan mengimbas nota *Brain Dump* yang belum diproses. |

> [!NOTE]
> **Mod Simulasi Pintar**: Sekiranya token Telegram belum ditetapkan dalam fail `.env`, sistem **tidak akan rosak atau melontarkan ralat**. Sebaliknya, automasi akan bertukar secara automatik ke **Mod Simulasi**, di mana teks mesej akan disimpan ke dalam jadual `automation_logs` dan `storage/logs/laravel.log` untuk rujukan anda di `/automation`.

---

## 2. Pra-Syarat & Konfigurasi Telegram Bot

Bagi menerima mesej sebenar di Telegram, sediakan 2 pemboleh ubah persekitaran dalam fail `.env`:

```env
# .env
APP_TIMEZONE=Asia/Kuala_Lumpur

TELEGRAM_BOT_TOKEN="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
TELEGRAM_DEFAULT_CHAT_ID="987654321"
```

### Cara Mendapatkan Token & Chat ID:
1. **Dapatkan Bot Token**:
   - Cari `@BotFather` di Telegram.
   - Hantar `/newbot`, ikuti arahan untuk memilih nama dan username bot anda.
   - Salin token HTTP API yang diberikan (cth: `712345678:AAH...`).
2. **Dapatkan Chat ID Anda**:
   - Mulakan perbualan dengan bot baharu anda (klik **Start**).
   - Cari bot `@userinfobot` di Telegram dan tekan `/start`. Ia akan membalas dengan nombor **Id** anda (cth: `123456789`).
   - Masukkan nombor Id tersebut ke `TELEGRAM_DEFAULT_CHAT_ID`.

---

## 3. Cara Menukar & Menetapkan Masa Jadual Automasi

Masa asal bagi Morning Briefing adalah jam **07:30 AM**, namun anda boleh menukarnya ke mana-mana masa lain (contoh: 07:00 AM, 08:00 AM, dsb.) dengan 2 kaedah mudah:

### Kaedah A: Melalui Fail `.env` (Paling Mudah - Tanpa Perlu Ubah Kod)
Semua masa jadual telah dikonfigurasikan di [config/lifeos.php](file:///c:/laragon/www/z/life-os/config/lifeos.php) untuk membaca pemboleh ubah persekitaran `.env`. Anda hanya perlu masukkan atau ubah masa yang diingini dalam fail `.env` (format 24-jam `HH:MM`):

```env
# .env

# Zon masa automasi (Default: Asia/Kuala_Lumpur)
LIFEOS_TIMEZONE="Asia/Kuala_Lumpur"

# Tukar waktu Morning Briefing (cth: tukar ke 08:00 pagi)
SCHEDULE_BRIEFING_TIME="08:00"

# Anda juga boleh menetapkan waktu automasi lain mengikut keselesaan anda:
SCHEDULE_GOALS_TIME="08:30"
SCHEDULE_SUBSCRIPTIONS_TIME="09:15"
SCHEDULE_BUDGETS_TIME="20:30"
SCHEDULE_TASKS_TIME="21:30"
SCHEDULE_REVIEWS_TIME="20:00"
SCHEDULE_REVIEWS_DAY=0 # 0 = Ahad, 1 = Isnin, ...
```

> [!TIP]
> Jika anda telah menjalankan `config:cache` di pelayan, pastikan anda jalankan `php artisan optimize:clear` atau `php artisan config:clear` selepas menukar fail `.env`.

---

### Kaedah B: Mengubah Terus dalam Kod (`routes/console.php`)
Sekiranya anda ingin mengubah logik kekerapan atau kod jadual secara kekal, buka fail [routes/console.php](file:///c:/laragon/www/z/life-os/routes/console.php):

```php
// routes/console.php

Schedule::command('lifeos:send-briefing')
    ->dailyAt('08:00') // Gantikan '07:30' dengan waktu pilihan anda
    ->timezone('Asia/Kuala_Lumpur');
```

Contoh kekerapan lain yang disokong Laravel:
- `->dailyAt('06:45')` : Setiap hari jam 6:45 pagi
- `->weekdays()->dailyAt('07:30')` : Hari bekerja sahaja (Isnin hingga Jumaat)
- `->twiceDaily(8, 20)` : Dua kali sehari (jam 8:00 pagi & 8:00 malam)
- `->hourly()` : Setiap jam

---

### Cara Mengesahkan Masa Jadual Baharu:
Jalankan arahan ini di terminal untuk melihat bila jadual seterusnya akan di-trigger:
```bash
php artisan schedule:list
```
Paparan antaramuka web di `/automation` juga akan mengemas kini masa paparan secara automatik mengikut nilai yang telah anda tetapkan!

---

### ⚠️ Perkara Penting Semasa Menguji Tukar Waktu di Local:

1. **Trigger Berjalan Tepat Pada Saat Ke-:00**:
   - Laravel Scheduler mengimbas dan mencetuskan arahan **tepat pada saat ke-:00** (contoh: `04:17:00`).
   - Sekiranya anda menetapkan jam `04:17` dan hanya memulakan `schedule:work` pada saat `04:17:30`, saat ke-:00 telah pun terlepas. Laravel akan menganggap jadual hari ini telah berlalu dan menjadualkannya untuk esok hari (`Next Due: 23 hours from now`).
   - **Tip Pengujian**: Sentiasa tetapkan waktu **2 atau 3 minit ke hadapan** dari jam semasa komputer anda (contoh: jika sekarang jam 04:21, set `SCHEDULE_BRIEFING_TIME="04:24"`).

2. **Wajib Restart Terminal `schedule:work` Selepas Edit `.env`**:
   - `php artisan schedule:work` adalah proses berterusan (*long-running process*) yang memuatkan konfigurasi ke dalam memori semasa ia dimulakan.
   - Jika anda menukar fail `.env`, proses terminal tersebut tidak akan menyedari perubahan tersebut selagi ia tidak dimulakan semula.
   - **Langkah yang betul selepas tukar `.env`**:
     ```bash
     # 1. Hentikan proses schedule:work sedia ada (Tekan Ctrl + C)
     # 2. Kosongkan cache konfigurasi
     php artisan optimize:clear

     # 3. Jalankan semula scheduler
     php artisan schedule:work
     ```

---

## 4. Setup di Mesin Tempatan (Local)

### Kaedah A: Cara Biasa (Local)

Dalam persekitaran pembangunan tempatan (seperti Laragon / Windows / macOS / Linux), Laravel menyediakan *built-in scheduler runner* tanpa memerlukan persediaan cron sistem operasi yang rumit:

#### 1. Menjalankan Scheduler Secara Langsung
Buka terminal di direktori projek:
```bash
php artisan schedule:work
```
> Perintah ini akan terus berjalan di latar belakang (*foreground process*) dan menilai fail `routes/console.php` setiap saat, sama seperti crontab di pelayan sebenar.

> [!NOTE]
> **Adakah Perlu Jalankan Queue Worker (`queue:work`)?**
> **TIDAK PERLU.** Semua notifikasi Telegram dan ringkasan Morning Briefing di LifeOS dihantar secara terus (*direct synchronous HTTP request*) ke Telegram Bot API. Anda hanya perlu menjalankan `php artisan schedule:work` sahaja.

#### 2. Menjalankan Queue Worker (Hanya Jika Ada Heavy Jobs Masa Hadapan)
Sekiranya pada masa hadapan anda menambah pemprosesan latar belakang yang berat (seperti export fail bersaiz besar):
```bash
php artisan queue:listen --tries=1
```

#### 3. Shortcut Pembangunan Serentak
Projek LifeOS dilengkapi dengan skrip Concurrently:
```bash
composer dev
```
Perintah ini akan melancarkan pelayan PHP, Queue Listener, dan Vite serentak dalam satu terminal.

---

### Kaedah B: Cara Docker (Local)

Sekiranya anda ingin menjalankan keseluruhan persekitaran LifeOS dalam bekas Docker di mesin tempatan:

#### 1. Fail `Dockerfile`
Cipta fail `Dockerfile` di punca projek:

```dockerfile
FROM php:8.4-fpm-alpine

# Pasang dependencies sistem & extensions PHP
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    tzdata

ENV TZ=Asia/Kuala_Lumpur
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl opcache

# Pasang Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

#### 2. Fail `docker-compose.yml`
Cipta fail `docker-compose.yml` di punca projek:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: lifeos-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
    environment:
      - APP_ENV=local
      - APP_TIMEZONE=Asia/Kuala_Lumpur
    networks:
      - lifeos-network

  nginx:
    image: nginx:alpine
    container_name: lifeos-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    depends_on:
      - app
    networks:
      - lifeos-network

  # Container Khas untuk Automasi Berjadual (Scheduler)
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: lifeos-scheduler
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
    environment:
      - APP_TIMEZONE=Asia/Kuala_Lumpur
    command: ["sh", "-c", "while true; do php /var/www/artisan schedule:run --verbose --no-interaction & sleep 60; done"]
    depends_on:
      - app
    networks:
      - lifeos-network

  # Container untuk Pemprosesan Antrian (Queue Worker)
  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: lifeos-queue
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
    environment:
      - APP_TIMEZONE=Asia/Kuala_Lumpur
    command: ["php", "/var/www/artisan", "queue:work", "--tries=3"]
    depends_on:
      - app
    networks:
      - lifeos-network

networks:
  lifeos-network:
    driver: bridge
```

#### 3. Arahan Menjalankan Docker Local:
```bash
docker compose up -d
```
Container `lifeos-scheduler` akan mengeksekusi `php artisan schedule:run` secara automatik setiap 60 saat.

---

## 5. Setup di Production Server (VPS)

### Kaedah A: Cara Biasa (Server Tradisional Ubuntu/Debian)

Pada pelayan produksi Linux (seperti Ubuntu 22.04 / 24.04), cara standard Laravel adalah memasukkan satu entri sahaja ke dalam cron sistem operasi.

#### 1. Tetapkan Zon Masa Server
Pastikan masa pelayan selari dengan waktu Malaysia:
```bash
sudo timedatectl set-timezone Asia/Kuala_Lumpur
```

#### 2. Tambah Entri Crontab
Buka crontab pengguna web (contoh: `www-data` atau pengguna deploy anda):
```bash
sudo crontab -u www-data -e
```
Masukkan baris berikut di bahagian paling bawah (gantikan `/var/www/life-os` dengan laluan folder projek anda):
```cron
* * * * * cd /var/www/life-os && php artisan schedule:run >> /dev/null 2>&1
```

> [!IMPORTANT]
> Entri cron ini akan berjalan **setiap 1 minit**. Laravel Scheduler sendiri yang akan menyemak sama ada waktu semasa adalah 07:30 AM untuk menghantar Morning Briefing, 08:00 PM untuk Bajet, dsb.

#### 3. Menguruskan Queue Worker Menggunakan Supervisor
Untuk tugasan latar belakang yang stabil, pasang Supervisor:
```bash
sudo apt update && sudo apt install supervisor -y
```
Cipta fail konfigurasi di `/etc/supervisor/conf.d/lifeos-worker.conf`:
```ini
[program:lifeos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/life-os/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/life-os/storage/logs/worker.log
stopwaitsecs=3600
```
Muat semula konfigurasi:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

### Kaedah B: Cara Docker (Server Production)

Pada persekitaran produksi berasaskan Docker:

1. Gunakan fail `docker-compose.prod.yml` dengan konfigurasi optimum:
```yaml
services:
  scheduler:
    image: your-registry/lifeos:latest
    restart: always
    environment:
      - APP_ENV=production
      - APP_TIMEZONE=Asia/Kuala_Lumpur
    # Menggunakan crond Alpine atau gelung 60 saat
    entrypoint: ["sh", "-c", "while true; do php artisan schedule:run --verbose --no-interaction; sleep 60; done"]
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```
2. Jalankan perintah deploy:
```bash
docker compose -f docker-compose.prod.yml up -d
```
3. Semak log scheduler secara langsung di pelayan:
```bash
docker compose logs -f scheduler
```

---

## 6. Pengujian, Pemantauan & Troubleshooting

### 1. Papan Pemuka Command Center (`/automation`)
Anda boleh membuka pautan [http://127.0.0.1:8000/automation](http://127.0.0.1:8000/automation) di pelayar web anda untuk:
- Memeriksa status sambungan bot Telegram (**Connected** atau **Simulation Mode**).
- Melihat pratonton langsung mesej HTML Morning Briefing.
- Menekan butang **⚡ Dispatch Test Briefing to Telegram** untuk menguji penghantaran mesej serta-merta tanpa perlu menunggu jam 07:30 AM.
- Menjalankan sebarang daripada 7 perintah automasi secara manual dengan satu klik.
- Memeriksa jadual log rekod penghantaran (`automation_logs`).

### 2. Menjalankan Ujian Manual Melalui Terminal (CLI)
Anda boleh memicu mana-mana arahan automasi secara individu pada bila-bila masa:

```bash
# Uji Morning Briefing
php artisan lifeos:send-briefing

# Uji Peringatan Mesyuarat
php artisan meetings:send-reminders

# Uji Peringatan Matlamat & Milestone
php artisan goals:send-reminders

# Uji Amaran Pembaharuan Langganan & Komitmen
php artisan subscriptions:check-due

# Uji Amaran Melebihi Bajet Bulanan
php artisan money:check-budgets

# Uji Ringkasan Tugasan & Amalan Harian
php artisan tasks:send-reminders

# Uji Peringatan Refleksi Mingguan
php artisan reviews:send-weekly-reminder
```

### 3. Semakan Senarai Jadual Berdaftar
Untuk melihat semua jadual yang aktif dan bilakah giliran ia akan dieksekusi seterusnya:
```bash
php artisan schedule:list
```
Output paparan akan menunjukkan masa larian seterusnya berdasarkan waktu `Asia/Kuala_Lumpur`.

### 4. Lokasi Fail Log
- Log sistem umum: `storage/logs/laravel.log`
- Log rekod automasi pangkalan data: Jadual `automation_logs` (boleh diakses dan dipadam menerusi UI `/automation`).

---

### 5. Soalan Lazim & Troubleshooting (FAQ)

#### Q1: Saya dah hidupkan `schedule:work` tapi bot Telegram langsung tidak menghantar mesej pada waktu yang saya tetapkan?
1. **Periksa sama ada anda terlepas saat ke-:00**: Laravel Scheduler memeriksa giliran tepat pada saat `00`. Jika anda set `04:17` tapi memulakan terminal pada saat `04:17:30`, giliran minit tersebut sudah terlepas dan ia dijadualkan untuk esok hari.
2. **Adakah anda restart terminal selepas tukar `.env`?**: Proses `schedule:work` yang sedang berjalan tidak membaca perubahan fail `.env` secara langsung. Tekan `Ctrl + C`, jalankan `php artisan optimize:clear`, dan mulakan semula `schedule:work`.
3. **Uji terus secara manual**: Jalankan `php artisan lifeos:send-briefing` di terminal. Jika mesej masuk ke Telegram serta-merta, ini mengesahkan token Telegram & Chat ID anda 100% sah dan berfungsi!

#### Q2: Adakah saya perlu menghidupkan queue worker (`php artisan queue:work`) untuk automasi ini?
**Tidak perlu.** Semua mesej Telegram dan automasi LifeOS beroperasi secara langsung (*direct synchronous HTTP request*). Anda hanya memerlukan `schedule:work` di local atau satu entri crontab di pelayan produksi.

#### Q3: Bagaimana jika token Telegram saya salah atau tidak ditetapkan?
Sistem LifeOS direka dengan *failsafe* pintar: ia tidak akan melontarkan ralat atau merosakkan scheduler. Sebaliknya, mesej akan disimpan dengan status `simulated` di dalam jadual `automation_logs` dan anda boleh menyemak kandungannya di paparan web `/automation`.


