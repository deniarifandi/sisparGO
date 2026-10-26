# 06. DATABASE PROBLEM ANALYSIS

Analisis mendalam terhadap anomali, technical debt, dan kelemahan integritas pada skema database `dishub2026`.

---

## 6.1 Identifikasi 18 Kategori Masalah Database Existing

### 1. Missing Foreign Key Constraints (`CRITICAL`)
- **Fakta**: Seluruh 21 tabel di database dibuat tanpa foreign key constraints (`ON DELETE`, `ON UPDATE`).
- **Dampak**: Tingginya potensi *orphan records*. Jika sebuah titik parkir atau anggota dihapus, data di tabel `va_owner`, `potensi`, `transaksi`, dan `kendala` tetap tertinggal tanpa referensi induk.

### 2. Relasi Berbasis Plain String Virtual Account (`HIGH`)
- **Fakta**: Tabel `transaksi`, `potensi`, `kendala`, dan `tiket` berelasi ke `va_owner` menggunakan kolom varchar seperti `transaksi_va`, `potensi_va`, `kendala_va`, `tiket_va`.
- **Dampak**: Jika terjadi typo, penambahan spasi, atau perubahan nomor VA, semua riwayat pembayaran dan rekonsiliasi tagihan putus. Seharusnya menggunakan integer foreign key (`virtual_account_id`).

### 3. Duplikasi Skema Pendaftaran vs Master Data (`HIGH`)
- **Fakta**: Tabel `register` memiliki struktur kolom yang hampir identik dengan `dishub_anggota`. Demikian pula `register_titpar` dengan `dishub_titpar`, dan `register_anggota` dengan `dishub_anggota`.
- **Dampak**: Terjadi proses *copy-paste* data secara manual antar tabel saat admin menekan tombol *Approve*. Jika ada perubahan data setelah registrasi, sinkronisasi antar tabel menjadi tidak konsisten.

### 4. Tipe Data Kolom Foreign Key yang Tidak Konsisten (`MEDIUM`)
- **Fakta**: 
  - Pada `va_owner`, kolom `va_owner_anggotaid` bertipe `varchar(255)`, sedangkan di `dishub_anggota.anggota_id` bertipe `int(10) unsigned`.
  - Pada `dishub_titpar`, kolom `titpar_klrhnid` bertipe `varchar(10)`, sedangkan di tabel `kelurahan.id` bertipe `varchar(20)`.
- **Dampak**: Menghalangi pembuatan index yang optimal dan menyebabkan type casting implisit yang memperlambat query SQL saat JOIN.

### 5. Format Tanggal / Waktu dalam String (`MEDIUM`)
- **Fakta**: 
  - Kolom `va_owner_expired` bertipe `varchar(50)` (berisi string ISO seperti `2026-12-31T00:00:00+07:00`).
  - Kolom `tgl_lahir` di `register_anggota` bertipe `varchar(255)`.
- **Dampak**: Query rentang tanggal (`WHERE date BETWEEN ...`) tidak dapat memanfaatkan index B-Tree dan rentan terhadap format tanggal yang tidak valid.

### 6. Denormalisasi Ekstrem pada Master VA (`MEDIUM`)
- **Fakta**: Tabel `va_owner` menyimpan ulang `va_owner_nama`, `va_owner_hp`, `va_owner_berita_1` (nama titpar), dan `va_owner_berita_2` (alamat titpar).
- **Dampak**: Redundansi data. Jika nama jukir atau alamat titik parkir diubah di master table, data pada `va_owner` menjadi usang kecuali di-update di banyak tempat.

### 7. Penggunaan Field Angka & String Status Tanpa Standar (`MEDIUM`)
- **Fakta**:
  - Pada `dishub_anggota`, `anggota_status = 3` berarti aktif.
  - Pada `register`, `anggota_status = 0` (Pending), `1` (Approved).
  - Pada `dishub_titpargrup`, `titpargrup_staanggota` bertipe `varchar(20)` dengan nilai `'1'` (Koordinator) atau `'2'` (Anggota).
  - Pada `kendala`, `approval` bertipe `varchar(255)` dengan nilai `'1'` atau `'2'`.
- **Dampak**: Membingungkan developer baru dan rawan salah logika saat membuat filter status.

### 8. Kolom Legacy / Dead Columns
- **Fakta**: Kolom-kolom warisan seperti `anggota_cid`, `anggota_cdate`, `anggota_cip`, `anggota_uid`, `anggota_udate`, `anggota_uip`, `anggota_hiscol` di tabel `dishub_anggota`, `dishub_titpar`, dan `dishub_titpargrup` sebagian besar bernilai `0` atau string kosong `''`.
- **Dampak**: Menambah overhead ukuran tabel (row size) tanpa memberikan fungsi nyata.

### 9. Flat Potensi Table (Horizontal Columns)
- **Fakta**: Tabel `potensi` memiliki kolom per hari: `senin`, `selasa`, `rabu`, `kamis`, `jumat`, `sabtu`, `minggu`.
- **Analisis**: Untuk kasus bisnis parkir tetap di DISHUB Kota Malang, struktur horizontal ini memudahkan operator melihat matriks 7 hari sekaligus. Namun, kolom kalkulasi `mingguan`, `bulanan`, dan `tahunan` tidak perlu disimpan permanen di database karena dapat dikalkulasi secara virtual / query logic untuk mencegah desinkronisasi.

---

## 6.3 KOREKSI Fakta Real-Data (Verifikasi 15-Sep)

Dokumentasi di atas mengandung beberapa asumsi yang **tidak cocok dengan data aktual** (diverifikasi via query live pada `dishub2026`). KOREKSI penting saat reverse engineering:

1. **`dishub_anggota` TIDAK punya kolom `koor_id`** — relasi koordinator tidak disimpan sebagai FK di tabel anggota; koordinator diketahui via `attendant_assignments.role = 'coordinator'`. Di schema baru, `attendants.coordinator_id` tetap nullable, karena koordinator ditangkap di `attendant_assignments`.
2. **`dishub_titpargrup.titpargrup_staanggota` berisi string `'KOORDINATOR'` / `'ANGGOTA'`**, bukan `'1'`/`'2'`. Mapping di schema baru harus mengonversi `'KOORDINATOR'`→`'coordinator'`, lainnya `'member'`.
3. **`va_owner` punya kolom tambahan** tidak terdokumentasi: `bank_id`, `jenis`, `berita_3`, `va_owner_tagihan`.
4. **Jumlah baris jauh lebih besar** daripada dokumen: `transaksi`=33,933, `kendala`=6,968, `tiket`=1,867. Dokumen asli menulis 11,051 transaksi — meleset 3×.
5. **Data duplikat/minim** di sumber: 229 `dishub_anggota` tanpa NIK, 88 `dishub_titpar` dengan `spot_code` duplikat, NIK duplikat (mis. `0000000000000000`), dan `va_owner` dengan `anggotaid` kosong.

---

## 6.2 Ringkasan Rekomendasi Perbaikan Database

1. **Gunakan InnoDB dengan Relational Integrity**: Tambahkan Foreign Key dengan constraint `RESTRICT` atau `CASCADE` yang tepat.
2. **Standard Naming Convention**:
   - Nama tabel plural lowercase: `users`, `attendants`, `parking_spots`, `attendant_assignments`, `virtual_accounts`, `daily_potentials`, `transactions`, `operational_issues`, `ticket_distributions`, `districts`.
   - Primary key: `id` (`BIGINT UNSIGNED` atau `INT UNSIGNED`).
   - Foreign key: `<singular_table>_id` (contoh: `parking_spot_id`, `attendant_id`).
   - Timestamps: `created_at`, `updated_at`, `deleted_at` (`TIMESTAMP NULL`).
3. **Penyatuan Siklus Pendaftaran**:
   - Satukan data pendaftaran ke dalam tabel utama dengan kolom `registration_status` (`draft`, `submitted`, `verified`, `approved`, `rejected`), sehingga tidak perlu menduplikasi tabel.
