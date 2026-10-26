# 18. TECHNICAL DEBT & REDUNDANCY INVENTORY

Daftar inventarisasi Technical Debt, kode mati (*Dead Code*), dan komponen redundant yang harus dibersihkan pada arsitektur baru.

---

## 18.1 Daftar Technical Debt Berdasarkan Kategori

| ID | Kategori | Lokasi / Komponen | Deskripsi Masalah | Tindakan Rekomendasi |
| :---: | :--- | :--- | :--- | :---: |
| **TD-01** | Duplicate Controller | `Titpar.php` vs `TitparController.php` | Fungsionalitas manajemen titik parkir terpisah menjadi 2 controller berbeda namespace dan style. | **MERGE & REFACTOR** |
| **TD-02** | Duplicate Controller | `Anggota.php` vs `AnggotaController.php` | Fungsionalitas master anggota jukir terduplikasi. | **MERGE & REFACTOR** |
| **TD-03** | Dead Code / Backup Files | `RegisterController - Copy.php`, `Sisparma - Copy.php`, `JatimOLD.php` | File duplikat/backup manual tertinggal di folder controller aktif. | **REMOVE** |
| **TD-04** | Dead Views / Old Backups | `app/Views/kta/backup check progress.html`, `app/Views/register/admin - Copy.php`, dll. | File backup template HTML/PHP lama tidak digunakan oleh rute aktif. | **REMOVE** |
| **TD-05** | Redundant Database Schema | Tabel `register`, `register_anggota`, `register_titpar` | Struktur tabel duplikat dengan `dishub_anggota` dan `dishub_titpar`. | **MERGE INTO MASTER SCHEMA** |
| **TD-06** | Dead Database Table | Tabel `history_register` | Tabel kosong (0 baris) dan tidak pernah diakses oleh controller manapun. | **REMOVE** |
| **TD-07** | Duplicate Methods / Test Code | Method `datatagihan*` di `Potensi.php` (`datatagihantest`, `datatagihanOLDSFIX`, dll.) | Method variasi uji coba perhitungan tagihan tertumpuk di controller. | **REMOVE** |
| **TD-08** | Inconsistent Role Representation | Kolom `users.role` (integer `0`, `1`, `2`, `3`) | Nilai integer magic numbers tanpa enum atau konstanta yang jelas. | **REFACTOR TO ENUM / RBAC** |
| **TD-09** | Architecture Debt | Controller langsung memanggil Query Builder `$db->table(...)` | Tidak ada isolasi data layer, query SQL tersebar di dalam controller. | **REFACTOR TO SERVICE & MODEL** |
| **TD-10** | Unused / Static Token Auth | Tabel `api_tokens` (hanya 1 baris token static) | Token otentikasi API mobile tidak memiliki expiration atau refresh token. | **REFACTOR TO JWT / SANCTUM** |

---

## 18.2 Action Matrix: Keep, Merge, Remove

- **REMOVE**:
  - Semua file `* - Copy.php`, `*OLD.php`, `backup *.*`.
  - Method uji coba: `datatagihaneueu`, `datatagihanuaeoua`, `commandcenterEX`, `targetSetoranBulananex1`.
  - Tabel: `history_register`.
- **MERGE**:
  - `Titpar.php` & `TitparController.php` → `ParkingSpotController` + `ParkingSpotService`.
  - `Anggota.php` & `AnggotaController.php` → `AttendantController` + `AttendantService`.
  - `register` tables → `attendants` / `attendant_registrations`.
- **REFACTOR**:
  - `Sisparma::va_notify` → `BankJatimWebhookController` + SNAP Signature Filter.
  - Perhitungan tagihan & potensi di `Potensi.php` → `PotentialCalculationService`.
  - Notifikasi WhatsApp di `Transaksi.php` → `WhatsAppNotificationService`.
