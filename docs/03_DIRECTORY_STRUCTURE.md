# 03. DIRECTORY STRUCTURE AUDIT

## 3.1 Existing Directory Structure & File Purpose

```
dishub2026/
├── app/
│   ├── Config/
│   │   ├── Routes.php          # Routing rules (Auth, Backoffice, API, Webhook, Mobile)
│   │   ├── Filters.php         # Filter middleware registration (AuthGuard, AuthToken)
│   │   └── Database.php        # DB connection settings
│   │
│   ├── Controllers/            # Total ~30 Controllers (Banyak duplikasi dan dead code)
│   │   ├── Admin/              # Namespace khusus alur verifikasi KTA / Pendaftaran
│   │   │   ├── AnggotaController.php   # Detail & foto update anggota terdaftar
│   │   │   ├── RegisterController.php  # Verifikasi pendaftaran, catatan, & approve member
│   │   │   └── TitparController.php    # Audit kondisi, potensi lapangan & foto titik parkir
│   │   ├── api/
│   │   │   └── ChatMessage.php         # API endpoint chat untuk mobile
│   │   ├── Anggota.php                 # [LEGACY/CRUD] Master data anggota jukir backoffice
│   │   ├── AnggotaController.php       # [PUBLIC] Handler add/edit anggota saat registrasi
│   │   ├── AppController.php           # Landing redirection/app view
│   │   ├── Chat.php / ChatRoom.php     # Sistem komunikasi room chat admin & klien
│   │   ├── ChatMessage.php             # Fetch & send pesan room
│   │   ├── Home.php                    # Login/Logout, Command Center, Dashboard Aggregator
│   │   ├── ImportController.php        # Import data batch CSV titpar/anggota & download foto
│   │   ├── JatimController.php         # Controller test akses token Bank Jatim
│   │   ├── JatimOLD.php                # [DEAD CODE] File lama integrasi Bank Jatim
│   │   ├── KtaController.php           # Controller monolitik pendaftaran KTA jukir (~1500 baris)
│   │   ├── KtaUploadController.php     # Upload berkas dokumen pendaftaran (KK, KTP, Denah, dll.)
│   │   ├── Potensi.php                 # Perhitungan potensi harian & realisasi setoran
│   │   ├── PublicApi.php               # API publik statistik pendapatan & target kecamatan
│   │   ├── RegisterController.php      # Form pendaftaran jukir mandiri
│   │   ├── RegisterController - Copy.php # [DEAD/BACKUP] File copy duplikat
│   │   ├── Sisparma.php                # Webhook receiver SNAP Bank Jatim (`va_notify`)
│   │   ├── Sisparma - Copy.php         # [DEAD/BACKUP] File copy duplikat
│   │   ├── SisparmaApp.php             # Backend API endpoint untuk aplikasi mobile jukir
│   │   ├── Tiket.php                   # Distribusi tiket/karcis fisik per VA
│   │   ├── Titpar.php                  # [LEGACY/CRUD] Master titik parkir, shift, assign koordinator
│   │   ├── TitparController.php        # [PUBLIC] Handler add/edit titpar saat registrasi
│   │   ├── Titpargrup.php              # Mapping titik parkir <-> jukir & status masa berlaku
│   │   ├── Transaksi.php               # Monitoring transaksi setoran & trigger WA konfirmasi
│   │   └── VaOwnerController.php       # Master mapping VA Bank Jatim ke Jukir/Titpar
│   │
│   ├── Filters/
│   │   ├── AuthGuard.php       # Session check untuk Web Backoffice (`isLoggedIn`)
│   │   └── AuthToken.php       # Bearer token filter dari tabel `api_tokens`
│   │
│   ├── Libraries/
│   │   ├── DataTable.php       # Server-side DataTables query builder helper
│   │   ├── Jatim.php           # SNAP Bank Jatim integration library (Hardcoded keys)
│   │   └── Whatsapp.php        # WhatsApp Gateway HTTP sender
│   │
│   ├── Models/                 # Hanya 9 Model (Banyak logika query langsung di Controller)
│   │   ├── DishubAnggotaModel.php
│   │   ├── DishubTitparModel.php
│   │   ├── PotensiModel.php
│   │   ├── RegisterAnggotaModel.php
│   │   ├── RegisterModel.php
│   │   ├── RegisterTitparModel.php
│   │   ├── TagihanModel.php
│   │   ├── TransaksiModel.php
│   │   └── VaOwnerModel.php
│   │
│   ├── Services/
│   │   ├── MemberService.php   # Service parsial pembuatan member
│   │   └── UploadService.php   # Service parsial penanganan file upload
│   │
│   └── Views/
│       ├── layouts/            # Template admin.php dan register.php
│       ├── partials/           # ~30 file partials modal & tab form
│       ├── register/           # Views pendaftaran publik & cetak surat penunjukan/persetujuan
│       ├── titpar/             # Views master titik parkir
│       ├── anggota/            # Views master anggota jukir
│       ├── potensi/            # Views potensi & tagihan
│       ├── transaksi/          # Views transaksi & invoice
│       ├── tiket/              # Views distribusi karcis fisik
│       ├── va_owner/           # Views virtual account
│       └── commandcenter.php   # Layar pemantauan real-time Command Center DISHUB
│
├── public/
│   ├── assets/                 # CSS, JS, Vendor assets
│   ├── uploads/                # File uploads (foto KTP, foto titpar, KK, surat tugas)
│   └── index.php               # Web entry point
├── .env                        # Local environment configuration
└── composer.json               # PHP dependency definition
```

---

## 3.2 Directory Debt & Problem Analysis

1. **File Duplikat & File Cadangan di Production Workspace**:
   - Ditemukan file berakhiran ` - Copy.php`, `(OLD).php`, `backup index.php`, dan `backup progress.html`.
   - File-file ini tidak dieksekusi oleh rute aktif tetapi menimbulkan kebingungan saat reverse engineering dan memakan ruang disk.
2. **Penamaan Controller yang Bertabrakan**:
   - `Titpar.php` vs `TitparController.php` (satu di root namespace untuk admin lama, satu lagi untuk public register).
   - `Anggota.php` vs `AnggotaController.php` vs `Admin\AnggotaController.php`.
3. **Pencampuran Tanggung Jawab Antarmuka**:
   - `SisparmaApp.php` bertindak sebagai Controller Web Backoffice untuk Kendala (`kendalaindex`), sekaligus API JSON untuk Mobile App (`listTransaksiApp`, `detailVaApp`, dll.).

---

## 3.3 Proposed Directory Structure (Clean Architecture Blueprint)

```
app/
├── Config/                     # Framework Configuration
│   ├── App.php
│   ├── BankJatim.php           # [NEW] Dedicated config for SNAP API credentials
│   ├── Database.php
│   ├── Filters.php
│   └── Routes.php
│
├── Controllers/                # Thin HTTP Controllers (Hanya menerima input & delegasi ke Service)
│   ├── Auth/
│   │   └── AuthController.php          # Login, Logout, Session management
│   ├── Backoffice/
│   │   ├── DashboardController.php     # Executive KPI & Command Center
│   │   ├── ParkingSpotController.php   # Titik Parkir & Shift Management
│   │   ├── AttendantController.php     # Juru Parkir & Koordinator Management
│   │   ├── VirtualAccountController.php# VA Owner & Synchronization
│   │   ├── PotentialRevenueController.php # Target Potensi Harian/Bulanan
│   │   ├── TransactionController.php   # Monitoring Mutasi & Setoran
│   │   ├── PhysicalTicketController.php# Distribusi Karcis Parkir
│   │   ├── DispensationController.php  # Pengajuan & Approval Kendala Lapangan
│   │   └── VerificationController.php  # Verifikasi Pendaftaran & Cetak SK
│   ├── Public/
│   │   ├── RegistrationController.php  # Pendaftaran Jukir & Titpar Mandiri
│   │   └── PublicDashboardController.php # Portal Informasi Publik
│   ├── Webhooks/
│   │   └── BankJatimWebhookController.php # SNAP BI Notification Receiver
│   └── Api/
│       ├── MobileAuthController.php    # JWT/Sanctum Authentication untuk Mobile
│       └── MobileAttendantController.php # Mobile API Endpoints
│
├── Services/                   # Pure Business Logic Layer
│   ├── Banking/
│   │   ├── BankJatimSnapService.php    # Token B2B, Signature Validation, Create/Inquiry VA
│   │   └── ReconciliationService.php   # Match transaksi vs target tagihan
│   ├── Notification/
│   │   └── WhatsAppNotificationService.php # Kirim struk & notifikasi setoran
│   ├── Registration/
│   │   └── AttendantRegistrationService.php # Workflow verifikasi & approval KTA
│   ├── Revenue/
│   │   ├── PotentialCalculationService.php # Rumus potensi harian/bulanan/tahunan
│   │   └── RevenueAnalyticsService.php # Agregasi pendapatan harian per wilayah
│   └── Document/
│       └── LetterGenerationService.php # Template Surat Penunjukan & Persetujuan
│
├── Models/                     # Data Access Layer (Active Record / Eloquent style)
│   ├── User.php
│   ├── Attendant.php           # (Refactor from dishub_anggota)
│   ├── ParkingSpot.php         # (Refactor from dishub_titpar)
│   ├── AttendantAssignment.php # (Refactor from dishub_titpargrup)
│   ├── VirtualAccount.php      # (Refactor from va_owner)
│   ├── PotentialRevenue.php    # (Refactor from potensi)
│   ├── Transaction.php         # (Refactor from transaksi)
│   ├── RawTransactionDump.php  # (Refactor from dump_transaksi)
│   ├── TicketDistribution.php  # (Refactor from tiket)
│   ├── OperationalIssue.php    # (Refactor from kendala)
│   └── DistrictLocation.php    # (Refactor from kelurahan)
│
├── DTO/                        # Data Transfer Objects untuk Request/Response Valid
│   ├── BankJatimNotifyDTO.php
│   ├── AttendantRegisterDTO.php
│   └── PotentialUpdateDTO.php
│
├── Filters/                    # HTTP Middleware
│   ├── AdminAuthFilter.php     # RBAC Web Backoffice
│   ├── ApiTokenFilter.php      # Bearer / JWT Token API
│   └── BankJatimSignatureFilter.php # [NEW] Validasi HMAC/RSA Header SNAP
│
└── Views/                      # Clean Separated Blade/PHP Views
    ├── layouts/
    ├── backoffice/
    ├── registration/
    ├── reports/
    └── emails/
```
