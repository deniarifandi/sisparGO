# 10. ROUTE & ENDPOINT AUDIT

Audit menyeluruh terhadap seluruh rute pada `app/Config/Routes.php` (Existing vs Proposed Clean Architecture).

---

## 10.1 Inventarisasi Endpoint Existing

### A. Public & Authentication Routes
| Method | URI | Controller::Method | Auth Filter | Keterangan & Masalah |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/login` | `Home::login` | None | Form login web |
| `POST` | `/auth/login` | `Home::loginPost` | None | Proses login web session |
| `POST` | `/logout` | `Home::logout` | None | Hapus session |
| `GET` | `/publicdashboard` | `Home::publicdashboard` | None | Dashboard publik tanpa login |
| `GET` | `/titpar/public` | `Home::titparPublic` | None | JSON titik parkir publik |
| `GET` | `/anggota/public` | `Home::anggotaPublic` | None | JSON anggota publik |
| `GET` | `/whatsapp` | `whatsapp::send` | None | **Redundant / Test endpoint** |

---

### B. Public Registration (KTA / Perijinan Jukir)
| Method | URI | Controller::Method | Auth Filter | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/register` | `RegisterController::landing` | None | Landing page pendaftaran |
| `GET` | `/register/form` | `RegisterController::register` | None | Multi-step form registrasi |
| `POST` | `/register/save` | `RegisterController::registersave` | None | Simpan pemohon baru |
| `GET` | `/register/progress` | `RegisterController::progressView` | None | Cek status berkas via NIK |
| `POST` | `/register/progress` | `RegisterController::progress` | None | Submit pengecekan NIK |
| `POST` | `/register/titpar/save` | `TitparController::save` | None | Tambah titik parkir usulan |
| `POST` | `/register/anggota/save`| `AnggotaController::save` | None | Tambah jukir bawahan |

---

### C. Webhook & Banking Integration (Bank Jatim SNAP)
| Method | URI | Controller::Method | Auth Filter | Masalah Kritis |
| :--- | :--- | :--- | :---: | :--- |
| `POST` | `/api/v1.0/transfer-va/va-notify` | `Sisparma::va_notify` | None | **CRITICAL: Tidak ada verifikasi SNAP Signature HMAC/RSA** |
| `GET` | `/api/jatim/getaccesstoken` | `JatimController::get_access_token` | `authGuard` | Helper generate token B2B |
| `GET` | `/api/jatim/inquiry-va` | `Jatim::inquiryva` | `authGuard` | Manual inquiry VA tester |

---

### D. Backoffice Protected Routes (Filter: `authGuard`)
| Method | URI | Controller::Method | Middleware | Tanggung Jawab |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/` | `Home::index` | `authGuard` | Dashboard Ringkasan Utama |
| `GET` | `/commandcenter` | `Home::commandcenter` | `authGuard` | Live TV Command Center |
| `GET/POST`| `/user/*` | `Home::user*` | `authGuard` | CRUD Master Akun Staf |
| `GET/POST`| `/titpar/*` | `Titpar::*` | `authGuard` | Master Titik Parkir & Shift |
| `GET/POST`| `/anggota/*` | `Anggota::*` | `authGuard` | Master Anggota Jukir |
| `GET/POST`| `/va-owner/*` | `VaOwnerController::*` | `authGuard` | Master Virtual Account |
| `GET/POST`| `/potensi/*` | `Potensi::*` | `authGuard` | Setting Potensi & Tagihan |
| `GET/POST`| `/transaksi/*` | `Transaksi::*` | `authGuard` | Log Mutasi Setoran & Invoice |
| `GET/POST`| `/tiket/*` | `Tiket::*` | `authGuard` | Distribusi Karcis Parkir Fisik |
| `GET/POST`| `/admin/register/*` | `Admin\RegisterController::*`| `authGuard` | Verifikasi Berkas & Cetak SK |
| `GET/POST`| `/kendala/*` | `SisparmaApp::*` | None/Mixed | **Rute kendala berada di luar `authGuard`** |

---

## 10.2 Masalah Rute & Temuan Anomali
1. **Rute Kendala di Luar Filter AuthGuard**:
   - Rute `$routes->group('kendala', ...)` (`approve`, `reject`, `updatePotongan`) diletakkan di luar group `authGuard`, sehingga secara teknis siapapun yang menembak POST request dapat mengubah status kendala jika tidak dicek manual di dalam method.
2. **Duplikasi HTTP Verb & Path**:
   - Ditemukan rute `POST /titpar/delete/(:num)` sekaligus `GET /titpar/delete/(:num)` di beberapa controller. Standar penghapusan resource seharusnya konsisten menggunakan `DELETE` atau `POST`.
3. **Endpoint Debug / Testing yang Tertinggal**:
   - `GET /whatsapp`, `GET /updatingberita/(:num)`, `GET /api/jatim/getsignature`.

---

## 10.3 Proposed RESTful & Clean Route Structure

```php
// 1. PUBLIC & AUTHENTICATION
$routes->get('login', 'Auth\AuthController::loginView');
$routes->post('auth/login', 'Auth\AuthController::login');
$routes->post('logout', 'Auth\AuthController::logout');
$routes->get('public-dashboard', 'Public\PublicDashboardController::index');

// 2. PUBLIC REGISTRATION PORTAL
$routes->group('registration', function($routes) {
    $routes->get('/', 'Public\RegistrationController::landing');
    $routes->get('form', 'Public\RegistrationController::form');
    $routes->post('submit', 'Public\RegistrationController::submit');
    $routes->get('tracking', 'Public\RegistrationController::tracking');
});

// 3. SECURE BANK JATIM WEBHOOK (WITH SIGNATURE FILTER)
$routes->group('api/v1.0/transfer-va', ['filter' => 'bankJatimSnapFilter'], function($routes) {
    $routes->post('va-notify', 'Webhooks\BankJatimWebhookController::handlePaymentNotify');
});

// 4. BACKOFFICE PROTECTED ROUTES (WITH RBAC FILTER)
$routes->group('admin', ['filter' => 'authGuard'], function($routes) {
    // Dashboard & Command Center
    $routes->get('dashboard', 'Backoffice\DashboardController::index');
    $routes->get('command-center', 'Backoffice\DashboardController::commandCenter');

    // Master Data
    $routes->resource('parking-spots', ['controller' => 'Backoffice\ParkingSpotController']);
    $routes->resource('attendants', ['controller' => 'Backoffice\AttendantController']);
    $routes->resource('virtual-accounts', ['controller' => 'Backoffice\VirtualAccountController']);

    // Potensi & Realisasi
    $routes->group('revenue', function($routes) {
        $routes->get('potentials', 'Backoffice\PotentialRevenueController::index');
        $routes->post('potentials/update', 'Backoffice\PotentialRevenueController::update');
        $routes->get('realization', 'Backoffice\PotentialRevenueController::realization');
        $routes->get('export-excel', 'Backoffice\PotentialRevenueController::exportExcel');
    });

    // Transaksi & Tiket
    $routes->get('transactions', 'Backoffice\TransactionController::index');
    $routes->get('transactions/invoice/(:num)', 'Backoffice\TransactionController::invoice/$1');
    $routes->resource('ticket-distributions', ['controller' => 'Backoffice\PhysicalTicketController']);

    // Kendala Operasional
    $routes->group('operational-issues', function($routes) {
        $routes->get('/', 'Backoffice\DispensationController::index');
        $routes->post('approve/(:num)', 'Backoffice\DispensationController::approve/$1');
        $routes->post('reject/(:num)', 'Backoffice\DispensationController::reject/$1');
    });

    // Verifikasi Pendaftaran & SK
    $routes->group('verifications', function($routes) {
        $routes->get('/', 'Backoffice\VerificationController::index');
        $routes->get('detail/(:num)', 'Backoffice\VerificationController::detail/$1');
        $routes->post('approve/(:num)', 'Backoffice\VerificationController::approve/$1');
        $routes->get('print-letter/(:num)', 'Backoffice\VerificationController::printAppointmentLetter/$1');
    });
});
```
