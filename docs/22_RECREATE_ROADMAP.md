# 22. RECREATE ROADMAP & IMPLEMENTATION PHASES

Panduan tahapan implementasi bertahap untuk membangun ulang sistem dari nol (*Step-by-Step Implementation Roadmap*).

> **Status (15-Sep)**: Database baru `dishub_recreate` **telah dibuat & diisi lengkap** — 13 tabel terhubung dengan FK, ~54,800 row dimigrasi dari `dishub2026`. Fase 1 (database) **SELESAI**. Sisa kerja fokus pada layer aplikasi (Controller/Service/Model/View).

---

## Phase 1: Foundation, Infrastructure & Database (Minggu 1) — ✅ SELESAI
- **05_DATABASE_CURRENT, 06_PROBLEMS, 07_PROPOSED, 08_MAPPING, 09_ERD** sudah diperbarui dengan data migrasi aktual.
- Database `dishub_recreate` siap dengan skema clean & FK constraints.
- **Yang tersisa di fase ini:** Inisialisasi framework / skeleton aplikasi, config `.env`, Auth Controller & middleware.
- **Objective**: Menyiapkan project skeleton, koneksi database, skema migrasi tabel baru, dan autentikasi dasar.
- **Modul**:
  - Inisialisasi framework & dependency (`composer.json`).
  - Database Migrations & Seeds: `users`, `districts` (`kelurahan`), `retribution_types` (`retritype`).
  - Auth Controller & Middleware (`AuthGuard`, `RoleFilter`).
  - Setup Environment Variables (`.env`).
- **Definition of Done**: User dapat login ke backoffice dengan role masing-masing (Superadmin, Admin, Operator, Pengawas) dan session diamankan.

---

## Phase 2: Master Data & Penugasan Lapangan (Minggu 2)
- **Objective**: Mengelola data fisik titik parkir, data jukir, koordinator, dan penugasan shift kerja.
- **Modul**:
  - CRUD Master Juru Parkir & Koordinator (`attendants`).
  - CRUD Master Titik Parkir (`parking_spots`) + Peta GIS Leaflet.
  - Modul Penugasan & Shift Kerja Jukir (`attendant_assignments`).
  - Master Virtual Account (`virtual_accounts`).
- **Definition of Done**: Admin dapat mendaftarkan titik parkir baru, menetapkan koordinator dan anggota shift, serta memetakan nomor VA Bank Jatim.

---

## Phase 3: Banking Integration & Webhook Setoran (Minggu 3)
- **Objective**: Mengintegrasikan sistem dengan Bank Jatim SNAP API untuk pembayaran retribusi real-time.
- **Modul**:
  - `BankJatimSnapService`: Generate Token B2B, Signature Asymmetric/Symmetric, Create VA, Inquiry VA.
  - Middleware `BankJatimSignatureFilter` untuk validasi keaslian webhook bank.
  - Webhook Endpoint `POST /api/v1.0/transfer-va/va-notify` + Idempotency check.
  - `WhatsAppNotificationService`: Pengiriman otomatis tanda terima setoran ke nomor koordinator.
- **Definition of Done**: Saat Bank Jatim mengirimkan callback notifikasi pembayaran, transaksi tercatat secara aman di tabel `transactions` dan jukir menerima pesan WhatsApp bukti setoran.

---

## Phase 4: Potensi Retribusi & Engine Rekonsiliasi Tagihan (Minggu 4)
- **Objective**: Penetapan target potensi harian dan monitoring realisasi setoran vs target bulanan.
- **Modul**:
  - Setting Matriks Potensi Harian (`daily_potentials`).
  - `PotentialCalculationService`: Formula kalender hari riil dalam bulan.
  - `ReconciliationService`: Perhitungan deviasi / kurang setor / piutang retribusi.
  - Export Laporan Excel (.xlsx) Realisasi Bulanan dan Tahunan.
- **Definition of Done**: Admin dan Kadis dapat melihat laporan realisasi penerimaan retribusi per titik parkir dan per kecamatan secara akurat.

---

## Phase 5: Kendala Operasional & Distribusi Karcis Fisik (Minggu 5)
- **Objective**: Mengelola pelaporan kendala/dispensasi jukir dan pencatatan tiket karcis parkir.
- **Modul**:
  - Modul Pengajuan Kendala Lapangan + Upload Foto Bukti (`operational_issues`).
  - Backoffice Approval Kendala & Penyesuaian Nilai Potongan Tagihan.
  - Modul Distribusi Karcis Parkir Seri Motor, Mobil, Truk (`ticket_distributions`).
- **Definition of Done**: Jukir dapat melaporkan kendala dan potongan yang disetujui otomatis mengurangi tagihan retribusi pada periode berjalan.

---

## Phase 6: Portal Registrasi Publik & Command Center (Minggu 6)
- **Objective**: Portal publik pendaftaran mandiri jukir baru, verifikasi berkas SK, dan visualisasi Command Center.
- **Modul**:
  - Portal Pendaftaran Publik multi-step (`attendant_registrations`).
  - Verifikasi Berkas Admin & Generator Cetak Surat Keputusan (SK Penunjukan & Persetujuan).
  - Live Command Center TV Display (Real-time charts, total revenue, peta sebaran titik parkir).
- **Definition of Done**: Seluruh siklus operasional dari pendaftaran, operasional, setoran bank, hingga pelaporan eksekutif selesai teruji end-to-end.
