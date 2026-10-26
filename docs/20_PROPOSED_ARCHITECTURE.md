# 20. PROPOSED CLEAN ARCHITECTURE

Cetak biru arsitektur aplikasi baru untuk rekonstruksi proyek pengelolaan retribusi parkir DISHUB.

---

## 20.1 Layered Architecture Pattern

Aplikasi dibangun menggunakan pola **Layered Clean Architecture**:

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                       │
│  Controllers (HTTP/JSON), Views (Bootstrap 5), Middlewares  │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Calls)
┌──────────────────────────────▼──────────────────────────────┐
│                      SERVICE LAYER                          │
│   Pure Business Logic, Domain Calculations, Workflows       │
│  (PotentialCalculationService, ReconciliationService, etc.) │
└──────────────┬───────────────────────────────┬──────────────┘
               │ (Calls)                       │ (Calls)
┌──────────────▼──────────────┐ ┌──────────────▼──────────────┐
│     DATA ACCESS LAYER       │ │     INTEGRATION LAYER       │
│  ActiveRecord Models & DTOs │ │  Bank Jatim SNAP, WhatsApp  │
└──────────────┬──────────────┘ └─────────────────────────────┘
               │
┌──────────────▼──────────────┐
│       DATABASE LAYER        │
│   MySQL 8.0+ / MariaDB      │
│ (InnoDB with Foreign Keys)  │
└─────────────────────────────┘
```

---

## 20.2 Tanggung Jawab Per Layer

| Layer | Komponen | Tanggung Jawab Utama |
| :--- | :--- | :--- |
| **Presentation** | Controllers (`app/Controllers/*`) | - Menerima HTTP Request (GET/POST/JSON).<br>- Validasi input dasar via Form Validation / DTO.<br>- Memanggil Service yang sesuai.<br>- Mengembalikan Response (View HTML atau JSON).<br>- **Tidak boleh memuat query SQL langsung atau kalkulasi bisnis rumit.** |
| **Presentation** | Middlewares / Filters (`app/Filters/*`) | - Verifikasi sesi login pengguna backoffice (`AuthGuard`).<br>- Validasi Role / Hak Akses (RBAC).<br>- Validasi SNAP Signature Bank Jatim pada Webhook. |
| **Service Layer** | Services (`app/Services/*`) | - Menampung seluruh aturan bisnis dan formula perhitungan retribusi.<br>- Menangani workflow pendaftaran dan approval SK jukir.<br>- Menjalankan Database Transactions (`db->transBegin()`). |
| **Integration** | External Clients (`app/Services/Banking/*`, `app/Services/Notification/*`) | - Komunikasi HTTP ke Bank Jatim SNAP API (Token, Signature, Create VA).<br>- Komunikasi HTTP ke WhatsApp Gateway. |
| **Data Access** | Models (`app/Models/*`) | - Representasi entitas tabel MySQL.<br>- Pengaturan relasi, timestamps, dan validasi field fillable (`$allowedFields`). |

---

## 20.3 Struktur Folder Lengkap Proyek Baru

```
dishub_recreate/
├── app/
│   ├── Config/
│   │   ├── BankJatim.php          # Konfigurasi SNAP API Bank Jatim
│   │   ├── WhatsApp.php           # Konfigurasi WhatsApp Gateway
│   │   ├── Database.php           # Konfigurasi Koneksi Database
│   │   └── Routes.php             # RESTful Routes
│   │
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── Backoffice/
│   │   │   ├── DashboardController.php
│   │   │   ├── ParkingSpotController.php
│   │   │   ├── AttendantController.php
│   │   │   ├── VirtualAccountController.php
│   │   │   ├── PotentialRevenueController.php
│   │   │   ├── TransactionController.php
│   │   │   ├── DispensationController.php
│   │   │   ├── PhysicalTicketController.php
│   │   │   └── VerificationController.php
│   │   ├── Public/
│   │   │   ├── RegistrationController.php
│   │   │   └── PublicDashboardController.php
│   │   └── Webhooks/
│   │       └── BankJatimWebhookController.php
│   │
│   ├── Services/
│   │   ├── Banking/
│   │   │   ├── BankJatimSnapService.php
│   │   │   └── ReconciliationService.php
│   │   ├── Revenue/
│   │   │   ├── PotentialCalculationService.php
│   │   │   └── RevenueAnalyticsService.php
│   │   ├── Notification/
│   │   │   └── WhatsAppNotificationService.php
│   │   ├── Registration/
│   │   │   └── AttendantRegistrationService.php
│   │   └── Document/
│   │       └── LetterGenerationService.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── District.php
│   │   ├── RetributionType.php
│   │   ├── Attendant.php
│   │   ├── ParkingSpot.php
│   │   ├── AttendantAssignment.php
│   │   ├── VirtualAccount.php
│   │   ├── DailyPotential.php
│   │   ├── Transaction.php
│   │   ├── OperationalIssue.php
│   │   └── TicketDistribution.php
│   │
│   ├── Filters/
│   │   ├── AuthGuard.php
│   │   ├── RoleFilter.php
│   │   └── BankJatimSignatureFilter.php
│   │
│   └── Views/
│       ├── layouts/
│       │   ├── backoffice.php
│       │   └── public.php
│       ├── backoffice/
│       │   ├── dashboard/
│       │   ├── parking_spots/
│       │   ├── attendants/
│       │   ├── potentials/
│       │   ├── transactions/
│       │   ├── operational_issues/
│       │   └── tickets/
│       └── registration/
│           ├── landing.php
│           ├── form.php
│           └── tracking.php
│
├── public/
│   ├── assets/
│   └── index.php
├── .env
└── composer.json
```
