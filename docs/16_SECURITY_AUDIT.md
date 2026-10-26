# 16. SECURITY AUDIT & HARDENING BLUEPRINT

Hasil audit keamanan lengkap (*Vulnerability Assessment*) pada sistem existing beserta panduan hardening untuk arsitektur baru.

---

## 16.1 Temuan Kerentanan Keamanan (Existing Vulnerabilities)

### 1. Hardcoded Bank API Credentials (`SEVERITY: CRITICAL`)
- **Lokasi**: `app/Libraries/Jatim.php`
- **Temuan**:
  ```php
  $this->clientID = "0b9fb46e-93cb-43a1-b36c-d16058109104";
  $this->clientSecret = "4f97ceb6-7f36-4af2-9898-4e4432202371";
  $this->partnerID = "382465f8-a134-43eb-bbe0-03a50683b5aa";
  ```
- **Skenario Serangan**: Jika source code bocor atau terekspos via git repository publik, penyerang dapat memanfaatkan kredensial ini untuk membuat Virtual Account palsu atau melakukan inquiry data perbankan Bank Jatim.
- **Rekomendasi Recreate**: Pindahkan seluruh kredensial ke environment variables (`.env`) dan jangan pernah menyertakan plain secrets di dalam source code repository.

---

### 2. Missing Webhook Signature Verification (`SEVERITY: HIGH`)
- **Lokasi**: `app/Controllers/Sisparma.php::va_notify`
- **Temuan**: Endpoint webhook `POST /api/v1.0/transfer-va/va-notify` menerima payload JSON dan langsung menyimpan transaksi tanpa memvalidasi signature header Bank Jatim (SNAP Header: `X-SIGNATURE`, `X-TIMESTAMP`, `X-PARTNER-ID`).
- **Skenario Serangan**: Penyerang dapat mengirimkan POST request buatan dengan nomor referensi palsu untuk memalsukan status pembayaran retribusi menjadi lunas.
- **Rekomendasi Recreate**: Terapkan middleware khusus `BankJatimSignatureFilter` yang memvalidasi Asymmetric RSA Signature / Symmetric HMAC SHA512 sesuai standar SNAP Bank Indonesia sebelum memproses payload.

---

### 3. Insecure Direct Object Reference (IDOR) & Endpoint Kendala Terbuka (`SEVERITY: HIGH`)
- **Lokasi**: `app/Config/Routes.php` (Grup rute `/kendala`)
- **Temuan**: Rute `POST /kendala/approve`, `POST /kendala/reject`, dan `POST /kendala/updatePotongan` berada di luar proteksi middleware `authGuard`.
- **Skenario Serangan**: Pengguna anonim tanpa login dapat mengirimkan POST request untuk menyetujui pengajuan kendala atau mengubah potongan tagihan.
- **Rekomendasi Recreate**: Semua endpoint administratif wajib berada di dalam grup middleware `authGuard` dengan pengecekan peran (RBAC).

---

### 4. Direct SQL Query / Raw Concatenation (`SEVERITY: MEDIUM`)
- **Lokasi**: Beberapa method di `Home.php` dan `Potensi.php`.
- **Temuan**: Masih ditemukan query langsung menggunakan `query("SELECT ... WHERE date = '" . $date . "'")`.
- **Rekomendasi Recreate**: Gunakan Prepared Statements atau Model Query Builder dengan parameter binding penuh.

---

### 5. File Upload Path Traversal & Unvalidated Mime Type (`SEVERITY: MEDIUM`)
- **Lokasi**: `KtaUploadController.php`
- **Temuan**: Validasi ekstensi file pada beberapa helper hanya mengecek string ekstensi akhir tanpa validasi MIME type sebenarnya (`finfo`) dan file disimpan dengan nama yang mudah ditebak.
- **Rekomendasi Recreate**: Simpan file upload di luar public directory yang tidak dapat dieksekusi langsung (non-executable storage), gunakan nama acak (UUID/hash), dan validasi ekstensi serta MIME type secara ketat (hanya izinkan `image/jpeg`, `image/png`, `application/pdf`).

---

## 16.2 Security Hardening Checklist untuk Proyek Baru

- [ ] Simpan seluruh API keys, Secret keys, dan DB credentials di `.env`.
- [ ] Implementasikan CSRF Protection pada seluruh form POST web backoffice (`csrf_field()`).
- [ ] Terapkan SNAP BI Signature Validation Middleware pada webhook perbankan.
- [ ] Hash password pengguna menggunakan `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])`.
- [ ] Terapkan Rate Limiting / Throttler pada endpoint login (`/login`) dan portal registrasi publik untuk mencegah brute force dan spam.
- [ ] Sanitasi seluruh output teks HTML menggunakan escaping engine (`esc()`) untuk mencegah Cross-Site Scripting (XSS).
