# 02. TECHNOLOGY STACK

## 2.1 Existing Technology Stack Inventory

| Kategori | Komponen / Library | Versi Terdeteksi | Status | Catatan Analisis |
| :--- | :--- | :--- | :---: | :--- |
| **Runtime Environment** | PHP | 8.2.12 (CLI & Apache) | `CONFIRMED` | Requirement di `composer.json` adalah `^8.1` |
| **Web Framework** | CodeIgniter 4 | 4.x (Standard App layout) | `CONFIRMED` | Menggunakan routing controller & BaseController CI4 |
| **Database Server** | MariaDB / MySQL | 10.4.x / MySQL 8.x | `CONFIRMED` | Driver: `MySQLi` pada database `dishub2026` |
| **HTTP Web Server** | Apache (XAMPP) | 2.4.x | `CONFIRMED` | Document root diarahkan ke folder `/public` |
| **Frontend Framework** | Bootstrap | 5.3.x | `CONFIRMED` | Bundled via CDN / local assets |
| **Table & Grid UI** | DataTables | 1.13.x | `CONFIRMED` | Ditemukan custom library `App\Libraries\DataTable.php` |
| **Map Engine (GIS)** | Leaflet JS / OpenStreetMap | 1.9.x | `CONFIRMED` | Digunakan untuk plotting titik parkir & polygon zona |
| **Captcha / Anti-bot** | Cloudflare Turnstile | v0 API | `CONFIRMED` | Kunci site terkonfigurasi di file `.env` |
| **Banking Gateway** | Bank Jatim SNAP API | SNAP BI v1.0 REST | `CONFIRMED` | Implementasi custom cURL di `App\Libraries\Jatim.php` |
| **Notification Engine** | WhatsApp HTTP Gateway | Custom API Endpoint | `CONFIRMED` | Endpoint cURL di `App\Libraries\Whatsapp.php` |

---

## 2.2 Dependency Analysis (`composer.json`)

File `composer.json` pada sistem existing:
```json
{
    "name": "codeigniter4/framework",
    "description": "The CodeIgniter framework v4",
    "license": "MIT",
    "type": "project",
    "require": {
        "php": "^8.1",
        "ext-intl": "*",
        "ext-mbstring": "*",
        "laminas/laminas-escaper": "^2.17",
        "psr/log": "^3.0"
    },
    "require-dev": {
        "codeigniter/coding-standard": "^1.7",
        "fakerphp/faker": "^1.24",
        "friendsofphp/php-cs-fixer": "^3.47.1",
        "kint-php/kint": "^6.0",
        "mikey179/vfsstream": "^1.6.12",
        "nexusphp/cs-config": "^3.6",
        "phpunit/phpunit": "^10.5.16 || ^11.2",
        "predis/predis": "^3.0"
    }
}
```

### Temuan Analisis Dependency:
1. **Zero External Packages di Composer**:
   - Integrasi perbankan Bank Jatim, WhatsApp Gateway, dan generator dokumen diimplementasikan secara murni dengan *hand-crafted script* atau native PHP functions (cURL, OpenSSL, GD/Image).
   - Tidak ada library HTTP client resmi (seperti `guzzlehttp/guzzle`) yang mempermudah retry mechanism atau logging.
2. **Missing Export/Reporting Libraries**:
   - Fitur export Excel (`Potensi::export`) dilakukan via format CSV manual atau HTML table rendering, bukan library native spreadsheet (`phpoffice/phpspreadsheet`).

---

## 2.3 Proposed Target Technology Stack (Recreation Blueprint)

Untuk implementasi ulang yang lebih kokoh, aman, dan mudah di-maintain:

| Layer | Rekomendasi Teknologi | Alasan Pemilihan |
| :--- | :--- | :--- |
| **Backend Framework** | CodeIgniter 4 (Clean Architecture) / Laravel 11 | Konsisten dengan ekosistem yang dikuasai user, ringan, dan cepat di hosting lokal/server DISHUB. |
| **Database** | MySQL 8.0+ / MariaDB 10.6+ | Dukungan penuh InnoDB, Foreign Key Constraints, Window Functions, dan JSON query. |
| **HTTP Client** | GuzzleHTTP / CodeIgniter CURLRequest Service | Menangani timeout, SSL verification, dan structured error handling untuk Bank Jatim & WhatsApp API. |
| **Security & Crypto** | PHP OpenSSL + Sodium (`ext-sodium`) | Validasi tanda tangan digital SNAP BI Asymmetric (SHA256withRSA) dan Symmetric (HMAC SHA512). |
| **Export/Import Engine** | PhpSpreadsheet (`phpoffice/phpspreadsheet`) | Ekspor laporan setoran bulanan/tahunan berformat Excel (.xlsx) resmi pemerintah daerah dengan template dinas. |
| **Frontend UI** | Bootstrap 5.3 + Tabler Admin / AdminLTE + Leaflet JS | Antarmuka dashboard responsif, modern, dan clean untuk operator serta pimpinan. |
