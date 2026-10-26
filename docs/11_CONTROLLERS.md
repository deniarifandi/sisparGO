# 11. CONTROLLER AUDIT & REFACTORING BLUEPRINT

Analisis beban kerja, tanggung jawab, dan refactoring controller monolitik menjadi Clean Controller & Service Layer.

---

## 11.1 Audit Controller Monolitik Existing

### 1. `Home.php` (Dashboard, Auth, User & Revenue Aggregator)
- **Ukuran**: ~800 baris kode.
- **Tanggung Jawab**:
  - Autentikasi web backoffice (`login`, `loginPost`, `logout`).
  - Autentikasi mobile app (`loginApp`).
  - CRUD User backoffice (`userlist`, `userdata`, `userstore`, dll.).
  - Dashboard public dan aggregasi data real-time (`getPendapatanHariIni`, `targetSetoranBulanan`, `targetSetoranBulananKecamatan`).
- **Masalah Utama**:
  - Terlalu banyak tanggung jawab (Violation of Single Responsibility Principle).
  - Banyak method variasi testing yang tidak dibersihkan: `commandcenterEX`, `getPendapatanByDateOLD`, `targetSetoranBulananex1`, `targetSetoranBulanan1`.
- **Rekomendasi Refactoring**:
  - Pecah menjadi:
    1. `AuthController` (khusus login/logout).
    2. `UserController` (khusus CRUD staf backoffice).
    3. `DashboardController` (khusus agregasi statistik & command center).
    4. `RevenueAnalyticsService` (memindahkan query agregasi SQL rumit ke Service).

---

### 2. `Potensi.php` (Kalkulasi Target & Tagihan Retribusi)
- **Ukuran**: ~950 baris kode.
- **Tanggung Jawab**:
  - Setting matriks potensi harian per VA.
  - Perhitungan tagihan bulanan dan deviasi realisasi setoran.
  - Ekspor data CSV/Excel.
- **Masalah Utama**:
  - Terdapat banyak method dead-code / testing yang menumpuk: `datatagihaneueu`, `datatagihanuaeoua`, `datatagihanOLDSFIX`, `datatagihantest`, `datatagihan9`, `datatagihan50`, `datatagihan6`, `datatagihan3`, `datatagihan2`.
  - Logika penentuan jumlah hari dalam bulan dan perulangan rekonsiliasi dilakukan berulang di dalam controller.
- **Rekomendasi Refactoring**:
  - Pindahkan logika kalkulasi hari & formula tagihan ke `PotentialCalculationService` dan `ReconciliationService`.

---

### 3. `Titpar.php` vs `TitparController.php` & `Anggota.php` vs `AnggotaController.php`
- **Masalah Utama**:
  - Duplikasi fungsionalitas. `Titpar.php` melayani backoffice, sedangkan `TitparController.php` melayani form public.
  - Method penugasan koordinator dan shift di `Titpar.php` (`addKoordinator`, `assignAnggota`, `changeCoordinator`, `replaceKoordinator`, `updateShift`) melakukan update langsung ke tabel `dishub_titpargrup` tanpa database transaction (`transBegin / transCommit`).
- **Rekomendasi Refactoring**:
  - Satukan ke dalam `ParkingSpotService` dan `AttendantAssignmentService` yang dilengkapi Database Transaction untuk memastikan integritas data saat pergantian koordinator.

---

### 4. `Sisparma.php` (Bank Jatim Webhook Receiver)
- **Tanggung Jawab**:
  - Menerima payload callback notifikasi pembayaran VA.
  - Validasi duplikasi referensi bank.
  - Simpan transaksi dan trigger notifikasi WhatsApp.
- **Masalah Utama**:
  - Logic database dipanggil langsung (`store_payment`).
  - Tidak ada validasi header signature Bank Jatim (SNAP).
- **Rekomendasi Refactoring**:
  - Tangani via `BankJatimWebhookController` yang didelegasikan ke `BankJatimSnapService` dan `WhatsAppNotificationService`.

---

## 11.2 Rekomendasi Pemisahan Layer (Service & Validator)

```
[HTTP Request]
       │
       ▼
[Route & Filter / Middleware] (Cek Auth, Session, Validasi SNAP Signature)
       │
       ▼
[Clean Controller]            (Hanya validasi input DTO & kirim Response)
       │
       ▼
[Service Layer]               (Memuat seluruh Business Logic, Kalkulasi, Validasi Bisnis)
       │
       ├──► [Bank Jatim Service / WhatsApp Service] (External Integration)
       └──► [Model / Repository Layer]             (Query Builder / Database Transaction)
               │
               ▼
        [MySQL Database]
```
