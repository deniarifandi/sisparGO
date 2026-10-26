# 24. OPEN QUESTIONS & DECISION LOG (NEEDS CLARIFICATION)

Daftar poin-poin yang memerlukan konfirmasi dari pemilik sistem / stakeholder sebelum memulai fase coding implementasi ulang.

---

## 24.1 Pertanyaan Terbuka & Keputusan Arsitektur

| No | Topik / Modul | Temuan Eksisting (`CONFIRMED`) | Pertanyaan Konfirmasi (`NEEDS CLARIFICATION`) | Rekomendasi Teknis |
| :---: | :--- | :--- | :--- | :--- |
| **Q1** | **Prefix Virtual Account Bank Jatim** | Ditemukan dua prefix VA di kode: `15088` dan `10325` (`App\Libraries\Jatim.php`). | Apakah kedua prefix akun perbankan tersebut saat ini masih aktif digunakan, atau salah satunya merupakan akun testing/legacy? | Pertahankan dukungan multi-prefix di konfigurasi `.env`, namun prioritaskan prefix utama yang aktif di data `va_owner`. |
| **Q2** | **Karcis / Tiket Parkir Fisik** | Terdapat modul distribusi karcis fisik dengan nomor seri awal/akhir (`tiket`). | Apakah pencatatan karcis fisik masih dibutuhkan di sistem baru, atau sistem akan difokuskan 100% pada setoran VA non-tunai? | Pertahankan modul `ticket_distributions` sebagai pelengkap audit manual untuk titik parkir non-elektronik. |
| **Q3** | **Aplikasi Mobile Jukir / Android App** | Ditemukan endpoint `SisparmaApp.php` (`/api/listTransaksiApp`, `/api/detailVaApp`, dll.) dan tabel `api_tokens`. | Apakah API mobile harus mempertahankan nama endpoint & response format yang sama (*Strict Backward Compatibility*), atau aplikasi mobile juga akan ikut dibangun ulang? | Sediakan adapter layer API di rute `/api/*` untuk menjamin aplikasi mobile yang sedang berjalan di lapangan tidak mengalami gangguan (downtime). |
| **Q4** | **Fitur Internal Chat / Room** | Terdapat 10,000 data di tabel `chat_rooms` dan 208 pesan di `chat_messages`. | Apakah fitur chat internal admin vs jukir ini masih aktif digunakan, atau komunikasi operasional sudah beralih penuh ke WhatsApp? | Sebagian besar komunikasi sudah otomatis via WhatsApp Gateway. Fitur internal chat dapat dijadikan modul opsional (Phase berikutnya). |
| **Q5** | **Metode Verifikasi SNAP Signature Webhook** | Webhook `va_notify` saat ini belum memvalidasi signature header Bank Jatim. | Apakah Bank Jatim di lingkungan production menggunakan metode Asymmetric (SHA256withRSA) atau Symmetric (HMAC SHA512) untuk header `X-SIGNATURE`? | Implementasikan kedua verifikator di `BankJatimSnapService` sesuai standar SNAP BI v1.0. |

---

## 24.2 Catatan Transparansi & Log Analisis

Semua temuan di atas diperoleh dari audit langsung terhadap:
- **Konfigurasi & Rute**: `app/Config/Routes.php`, `app/Config/Database.php`, `.env`
- **Database Engine**: Koneksi live PDO MariaDB `dishub2026` (21 tabel dianalisis struktur dan sampel datanya)
- **Source Code**: Controller, Model, Library (`Jatim.php`, `Whatsapp.php`, `DataTable.php`), dan Views di `app/`
- **Dokumen Terkait**: Pola standar CodeIgniter 4 dan referensi arsitektur domain parkir daerah.
