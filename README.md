# حروف — Sistem Hafalan Huruf Arab

Aplikasi web interaktif untuk belajar dan menghafal 28 huruf hijaiyah Arab menggunakan kaedah mnemonic, kuiz, dan sistem ulang kaji (spaced repetition).

Dibina dengan **Laravel 13**, **Alpine.js**, **Tailwind CSS v4**, dan SQLite.

---

## Ciri-ciri

- Paparan huruf Arab lengkap dengan bentuk (isolated / initial / medial / final)
- Bunyi sebutan melalui Laravel TTS proxy (Google Translate, tanpa API key)
- Sistem mnemonic — cara ingat visual untuk setiap huruf
- Kuiz interaktif — mod Teka Nama, Teka Huruf, atau Campuran
- Statistik kemajuan & streak harian (disimpan di localStorage)
- Spaced repetition — peringatan ulang kaji berdasarkan prestasi
- Responsive — mobile-first dengan bottom nav, desktop dengan top nav

---

## Keperluan Sistem

| Perisian | Versi minimum |
|----------|---------------|
| PHP      | 8.2+          |
| Composer | 2.x           |
| Node.js  | 18+           |
| npm      | 9+            |
| SQLite   | 3.x (built-in PHP) |

---

## Setup Tempatan (Development)

### 1. Clone projek

```bash
git clone <repo-url> arabic-hafalan
cd arabic-hafalan
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` jika perlu (default sudah cukup untuk development):

```env
APP_NAME="Hafal Huruf Arab"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
```

### 4. Buat database dan seed data

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 5. Build assets

```bash
npm run dev
```

> Biarkan `npm run dev` berjalan di terminal berasingan semasa development.

### 6. Jalankan server

```bash
php artisan serve
```

Buka browser: **http://127.0.0.1:8000**

---

## Struktur Projek

```
arabic-hafalan/
├── app/
│   ├── Http/Controllers/
│   │   ├── HafalanController.php     # Halaman utama, belajar, kuiz, statistik
│   │   ├── TtsController.php         # Laravel TTS proxy (/tts?text=...)
│   │   └── Api/LetterController.php  # API endpoints
│   └── Models/
│       └── ArabicLetter.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── ArabicLetterSeeder.php    # 29 huruf dengan mnemonic & contoh kata
├── resources/
│   ├── css/app.css                   # Tailwind v4 @theme + animations
│   ├── js/
│   │   ├── app.js                    # Entry point — Alpine + hafalan
│   │   ├── bootstrap.js              # Axios setup
│   │   └── hafalan.js                # Audio engine + localStorage progress
│   └── views/
│       ├── home.blade.php
│       ├── learn/
│       ├── quiz/
│       ├── stats/
│       └── components/
│           ├── layouts/app.blade.php
│           ├── letter-card.blade.php
│           ├── progress-bar.blade.php
│           ├── stat-card.blade.php
│           └── bottom-nav.blade.php
└── routes/web.php
```

---

## Deploy ke Production

### Persediaan server (Ubuntu/Debian)

```bash
# PHP 8.3 + extensions
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-fpm php8.3-sqlite3 php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# Nginx
sudo apt install nginx
```

### 1. Upload projek ke server

```bash
# Contoh menggunakan git
git clone <repo-url> /var/www/arabic-hafalan
cd /var/www/arabic-hafalan
```

### 2. Install dependencies (production mode)

```bash
composer install --no-dev --optimize-autoloader
npm ci
```

### 3. Konfigurasi `.env` untuk production

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME="Hafal Huruf Arab"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=sqlite
```

### 4. Setup database

```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite

php artisan migrate --seed --force
```

### 5. Build assets untuk production

```bash
npm run build
```

### 6. Optimasi Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Set permission folder

```bash
sudo chown -R www-data:www-data /var/www/arabic-hafalan
sudo chmod -R 755 /var/www/arabic-hafalan
sudo chmod -R 775 /var/www/arabic-hafalan/storage
sudo chmod -R 775 /var/www/arabic-hafalan/bootstrap/cache
```

### 8. Konfigurasi Nginx

Buat fail `/etc/nginx/sites-available/arabic-hafalan`:

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/arabic-hafalan/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/arabic-hafalan /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. SSL dengan Certbot (HTTPS)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

---

## Update Production

Apabila ada perubahan kod:

```bash
cd /var/www/arabic-hafalan
git pull

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

---

## Routes

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `/` | Halaman utama |
| GET | `/belajar` | Senarai kumpulan huruf |
| GET | `/belajar/{id}` | Detail satu huruf |
| GET | `/kuiz` | Tetapan kuiz |
| GET | `/kuiz/main` | Main kuiz |
| GET | `/keputusan` | Keputusan kuiz |
| GET | `/statistik` | Statistik kemajuan |
| GET | `/tts?text={arab}` | Proxy bunyi Arab (audio/mpeg) |
| GET | `/api/letters` | API senarai huruf |
| GET | `/api/letters/{id}` | API satu huruf |
| POST | `/api/quiz/save` | Simpan sesi kuiz |

---

## Teknologi

| Lapisan | Teknologi |
|---------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Database | SQLite |
| Frontend CSS | Tailwind CSS v4 (via @tailwindcss/vite) |
| Frontend JS | Alpine.js v3 |
| Build tool | Vite |
| Font Arab | Amiri (Google Fonts) |
| TTS | Google Translate proxy (Laravel) |
| Progress | localStorage (tanpa login) |

---

## Nota Bunyi (TTS)

Bunyi sebutan huruf menggunakan route `/tts?text=...` — Laravel fetch audio dari Google Translate secara server-side dan stream balik `audio/mpeg` ke browser. Ini mengelakkan isu CORS. Tiada API key diperlukan.

Jika server tiada akses internet, bunyi akan fallback ke Web Speech API (bergantung pada sokongan browser).

---

## Lesen

MIT License
