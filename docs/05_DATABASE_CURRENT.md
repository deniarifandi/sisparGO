# 05. DATABASE CURRENT (REVERSE ENGINEERING)

Dokumentasi detail struktur skema dan tabel database existing `dishub2026` (21 tabel).

> ⚠️ **KOREKSI (15-Sep, setelah query live)**: Jumlah baris di bawah bersumber pada **query langsung** ke database live; dokumentasi pertama dihitung dari asumsi. Kolom `dishub_anggota.koor_id` **TIDAK ADA** — relasi koordinator terbentuk via `attendant_assignments.role`, bukan kolom FK di tabel anggota.

---

## 1. TABLE: `users`
**Purpose**: Menyimpan data akun login staf backoffice, operator tim wilayah, pengawas, dan admin sistem.  
**Total Rows**: 18

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `user_id` | `int(11)` | NO | NULL | **PRI** | Primary Key akun user |
| `username` | `varchar(255)` | YES | NULL | | Username untuk autentikasi login |
| `password` | `varchar(255)` | YES | NULL | | BCRYPT hash password |
| `nama` | `varchar(255)` | YES | NULL | | Nama lengkap staf/operator |
| `role` | `int(11)` | YES | NULL | | Kode peran: 0=Superadmin, 1=Admin, 2=Operator, 3=Pengawas |

---

## 2. TABLE: `dishub_anggota`
**Purpose**: Master profil data juru parkir (jukir) dan koordinator parkir resmi terdaftar.  
**Total Rows**: 4,652

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `anggota_id` | `int(10) unsigned` | NO | NULL | **PRI** | Primary Key |
| `anggota_noktp` | `varchar(20)` | YES | NULL | **UNI** | NIK / Nomor KTP Jukir |
| `anggota_nama` | `varchar(50)` | YES | NULL | | Nama lengkap jukir |
| `anggota_noreg` | `varchar(20)` | YES | NULL | | Nomor Registrasi Lama |
| `anggota_newnoreg` | `varchar(6)` | YES | NULL | | Nomor Registrasi / KTA Baru |
| `anggota_alamat` | `varchar(50)` | YES | NULL | | Alamat domisili |
| `anggota_tgllhr` | `date` | YES | NULL | | Tanggal lahir |
| `anggota_tmplhr` | `varchar(50)` | NO | `''` | | Tempat lahir |
| `anggota_jnsklm` | `enum('L','P')` | YES | NULL | | Jenis kelamin |
| `anggota_nohp` | `varchar(30)` | NO | `''` | | Nomor telepon/WhatsApp |
| `anggota_foto` | `varchar(100)` | YES | NULL | | Path file foto wajah jukir |
| `anggota_jukirtypeid` | `int(11)` | NO | `1` | | Tipe jukir (1=Koordinator, 2=Anggota, dll.) |
| `anggota_status` | `int(10) unsigned` | NO | `3` | | Status keaktifan (3=Aktif) |
| `koor_id` | `int(11)` | YES | NULL | | Referensi ID Koordinator jika berstatus anggota |
| `password` | `varchar(255)` | YES | `'jukirmalang'`| | Password default jukir untuk mobile app |
| `jukir_token` | `text` | YES | NULL | | Sesi token mobile app |
| `created_at` | `timestamp` | YES | NULL | | Timestamp pembuatan |
| `updated_at` | `timestamp` | YES | NULL | | Timestamp pembaruan |
| `deleted_at` | `timestamp` | YES | NULL | | Soft delete timestamp |
| *(Kolom Legacy Lain)* | `anggota_cid`, `anggota_uid`, `anggota_cip`, `anggota_hiscol` | | | | Kolom audit trail bawaan sistem lama (kebanyakan tidak aktif) |

---

## 3. TABLE: `dishub_titpar`
**Purpose**: Master lokasi fisik titik parkir tepi jalan umum dan tempat khusus parkir.  
**Total Rows**: 1,708

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `titpar_id` | `int(10) unsigned` | NO | NULL | **PRI** | Primary Key |
| `titpar_noreg` | `varchar(20)` | YES | NULL | | Nomor Registrasi Titik Lama |
| `titpar_newnoreg` | `varchar(4)` | YES | NULL | | Nomor Registrasi Titik Baru (4 digit) |
| `titpar_namatempat` | `varchar(26)` | YES | NULL | | Nama tempat usaha / lokasi titik parkir |
| `titpar_lokasi` | `varchar(255)` | YES | `''` | | Nama jalan / alamat detail lokasi |
| `titpar_pmlknama` | `varchar(100)` | NO | `''` | | Nama penanggung jawab / pemilik usaha |
| `titpar_retritypeid` | `int(11)` | NO | `1` | **MUL**| Jenis retribusi (FK ke `retritype`) |
| `titpar_klrhnid` | `varchar(10)` | YES | NULL | **MUL**| ID Kelurahan (FK ke `kelurahan`) |
| `titpar_latitude` | `double` | YES | NULL | | Koordinat Garis Lintang (GIS) |
| `titpar_longitude` | `double` | YES | NULL | | Koordinat Garis Bujur (GIS) |
| `zona_id` | `int(11)` | YES | NULL | | ID Zona retribusi |
| `pengawas_id` | `int(11)` | YES | NULL | | ID Petugas pengawas lapangan (`users.user_id`) |
| `titpar_status` | `int(10) unsigned` | NO | `3` | | Status keaktifan titik (3=Aktif) |

---

## 4. TABLE: `dishub_titpargrup`
**Purpose**: Pivot table relasi many-to-many antara Titik Parkir (`dishub_titpar`) dan Juru Parkir (`dishub_anggota`), dilengkapi informasi shift operasional.  
**Total Rows**: 4,825

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `titpargrup_id` | `int(10) unsigned` | NO | NULL | **PRI** | Primary Key |
| `titpargrup_titparid` | `int(10) unsigned` | NO | `0` | **MUL**| Relasi ke `dishub_titpar.titpar_id` |
| `titpargrup_anggotaid` | `int(10) unsigned` | NO | `0` | **MUL**| Relasi ke `dishub_anggota.anggota_id` |
| `titpargrup_staanggota` | `varchar(20)` | YES | NULL | | Status penugasan: `'1'` (Koordinator) / `'2'` (Anggota) |
| `titpargrup_namashift` | `varchar(255)` | YES | NULL | | Nama shift kerja (Pagi, Siang, Sore, Malam) |
| `titpargrup_shifthari` | `int(11)` | YES | NULL | | Jumlah hari kerja dalam seminggu |
| `titpargrup_shiftjammulai` | `varchar(255)` | YES | NULL | | Jam mulai shift kerja (contoh: `07:00`) |
| `titpargrup_shiftjamselesai` | `varchar(255)` | YES | NULL | | Jam selesai shift kerja (contoh: `15:00`) |
| `titpargrup_masaberlakustart`| `date` | YES | NULL | | Tanggal awal berlakunya penugasan |
| `titpargrup_masaberlakuend` | `date` | YES | NULL | | Tanggal akhir masa berlaku SK penugasan |
| `titpargrup_status` | `int(10) unsigned` | NO | `3` | **MUL**| Status aktif penugasan |

---

## 5. TABLE: `va_owner`
**Purpose**: Menampung master rekening Virtual Account Bank Jatim (16 digit) dan mapping terhadap pemegang VA (Koordinator Jukir).  
**Total Rows**: 880

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `va_owner_id` | `int(11)` | NO | NULL | **PRI** | Primary Key |
| `va_owner_va` | `varchar(50)` | NO | NULL | **UNI** | Nomor 16-digit Virtual Account Bank Jatim |
| `va_owner_anggotaid` | `varchar(255)` | NO | NULL | | ID Anggota Jukir (`dishub_anggota.anggota_id`) |
| `va_owner_titparid` | `varchar(255)` | YES | NULL | | ID Titik Parkir (`dishub_titpar.titpar_id`) |
| `va_owner_titpargrupid`| `varchar(255)` | YES | NULL | **MUL**| ID Grup Titpar |
| `va_owner_nama` | `varchar(255)` | YES | NULL | | Nama pemegang VA (redundant dengan anggota) |
| `va_owner_berita_1` | `varchar(255)` | YES | NULL | | Berita VA 1 (Nama Titik Parkir) |
| `va_owner_berita_2` | `varchar(255)` | YES | NULL | | Berita VA 2 (Nama Jalan / Lokasi) |
| `va_owner_hp` | `varchar(20)` | YES | NULL | | Nomor HP tujuan notifikasi WhatsApp |
| `va_owner_expired` | `varchar(50)` | YES | NULL | | Tanggal kedaluwarsa VA (format ISO 8601) |
| `va_owner_email` | `varchar(50)` | YES | NULL | | Keterangan jenis retribusi (TJU / TKP) |
| `created_at` | `timestamp` | YES | NULL | | Timestamp dibuat |
| `deleted_at` | `timestamp` | YES | NULL | | Soft delete timestamp |

---

## 6. TABLE: `potensi`
**Purpose**: Penetapan nominal target potensi retribusi harian (Senin–Minggu) untuk masing-masing rekening Virtual Account.  
**Total Rows**: 1,005

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `potensi_id` | `int(11)` | NO | NULL | **PRI** | Primary Key |
| `potensi_va` | `varchar(20)` | YES | NULL | **UNI** | Nomor VA (`va_owner.va_owner_va`) |
| `senin` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Senin (Rp) |
| `selasa` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Selasa (Rp) |
| `rabu` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Rabu (Rp) |
| `kamis` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Kamis (Rp) |
| `jumat` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Jumat (Rp) |
| `sabtu` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Sabtu (Rp) |
| `minggu` | `int(11)` | YES | NULL | | Potensi nominal setoran hari Minggu (Rp) |
| `mingguan` | `int(11)` | YES | NULL | | Total akumulasi potensi 1 minggu (Rp) |
| `bulanan` | `int(11)` | YES | NULL | | Estimasi target potensi 1 bulan (Rp) |
| `tahunan` | `int(11)` | YES | NULL | | Estimasi target potensi 1 tahun (Rp) |
| `srp` | `int(11)` | YES | NULL | | Satuan Ruang Parkir (kapasitas fisik) |

---

## 7. TABLE: `transaksi`
**Purpose**: Catatan mutasi setoran retribusi parkir yang sukses diterima melalui Bank Jatim.  
**Total Rows**: 33,933

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `transaksi_id` | `int(11)` | NO | NULL | **PRI** | Primary Key |
| `transaksi_va` | `varchar(255)` | YES | NULL | **MUL**| Nomor VA tujuan setoran |
| `transaksi_nominal` | `int(11)` | YES | NULL | | Nominal rupiah yang disetorkan |
| `transaksi_tanggal` | `timestamp` | YES | NULL | | Waktu transaksi terjadi di bank |
| `coreReferenceNo` | `varchar(250)` | YES | NULL | | Nomor referensi unik transaksi Bank Jatim |
| `latestTransactionStatus`| `varchar(255)`| YES | NULL | | Kode status transaksi (contoh: `'00'`) |
| `transactionStatusDesc`| `varchar(255)`| YES | NULL | | Deskripsi status (`'Successful'`) |
| `wa_konfirmasi` | `text` | YES | NULL | | Status / Log pengiriman pesan WhatsApp |

---

## 8. TABLE: `dump_transaksi`
**Purpose**: Log transaksi mentah atau notifikasi callback perbankan yang terdeteksi duplikat `coreReferenceNo`.  
**Total Rows**: 8,691

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `transaksi_id` | `int(11)` | NO | NULL | **PRI** | Primary Key Auto Increment |
| `transaksi_va` | `varchar(255)` | YES | NULL | **MUL**| Nomor VA |
| `transaksi_nominal` | `int(11)` | YES | NULL | | Nominal setoran |
| `transaksi_tanggal` | `timestamp` | YES | NULL | | Tanggal transaksi |
| `coreReferenceNo` | `varchar(250)` | YES | NULL | | Nomor referensi bank |
| `latestTransactionStatus`| `varchar(255)`| YES | NULL | | Status bank |
| `transactionStatusDesc`| `varchar(255)`| YES | NULL | | Deskripsi |

---

## 9. TABLE: `kendala`
**Purpose**: Permohonan dispensasi/keringanan setoran harian dari jukir dengan lampiran foto bukti di lapangan.  
**Total Rows**: 6,968

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `kendala_id` | `int(11)` | NO | NULL | **PRI** | Primary Key |
| `kendala_va` | `varchar(255)` | YES | NULL | | Nomor VA pemohon |
| `kendala_foto` | `varchar(255)` | YES | NULL | | Path foto bukti kondisi lapangan |
| `kendala_alasan` | `varchar(255)` | YES | NULL | | Keterangan alasan (hujan, tutup, proyek) |
| `approval` | `varchar(255)` | YES | NULL | | Status persetujuan: `'1'`=Approved, `'2'`=Rejected |
| `approved_by` | `int(11)` | YES | NULL | | ID Admin yang menyetujui (`users.user_id`) |
| `kategori` | `int(11)` | YES | NULL | | Kategori kendala operasional |
| `created_at` | `timestamp` | YES | NULL | | Waktu pelaporan kendala |
| `updated_at` | `timestamp` | YES | NULL | | Waktu verifikasi approval |

---

## 10. TABLE: `tiket`
**Purpose**: Distribusi karcis/tiket retribusi fisik berbasis nomor seri awal dan akhir untuk motor, mobil, dan truk.  
**Total Rows**: 1,867

| Column | Type | Nullable | Default | Key | Purpose |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `tiket_id` | `int(11)` | NO | NULL | **PRI** | Primary Key |
| `tiket_va` | `varchar(255)` | YES | NULL | | Nomor VA penerima karcis |
| `tiket_tanggal` | `timestamp` | YES | CURRENT | | Tanggal penyerahan bendel tiket |
| `tiket_motor` | `int(11)` | YES | NULL | | Jumlah lembar tiket motor |
| `tiket_nomor_motor_awal` | `varchar(255)` | YES | NULL | | Nomor seri awal tiket motor |
| `tiket_nomor_motor_akhir`| `varchar(255)` | YES | NULL | | Nomor seri akhir tiket motor |
| `tiket_mobil` | `int(11)` | YES | NULL | | Jumlah lembar tiket mobil |
| `tiket_nomor_mobil_awal` | `varchar(255)` | YES | NULL | | Nomor seri awal tiket mobil |
| `tiket_nomor_mobil_akhir`| `varchar(255)` | YES | NULL | | Nomor seri akhir tiket mobil |
| `tiket_truk` | `int(11)` | YES | NULL | | Jumlah lembar tiket truk |
| `tiket_nomor_truk_awal` | `varchar(255)` | YES | NULL | | Nomor seri awal tiket truk |
| `tiket_nomor_truk_akhir` | `varchar(255)` | YES | NULL | | Nomor seri akhir tiket truk |
| `status_terima` | `int(11)` | YES | NULL | | Konfirmasi serah terima jukir |

---

## 11. TABLE: `register`, `register_anggota`, `register_titpar`
**Purpose**: Tabel penampung data pendaftaran online sebelum disetujui (KTA Online).  
**Total Rows**: 5 (register), 3 (register_anggota), 3 (register_titpar)

- `register`: Data profil pemohon koordinator (NIK, nama, alamat, foto, KK, surat kesanggupan).
- `register_titpar`: Data usulan titik parkir baru beserta foto denah & dokumen penunjukan.
- `register_anggota`: Data usulan jukir pembantu di bawah koordinator pemohon.

---

## 12. TABEL PENDUKUNG LAINNYA
- **`kelurahan`** (57 baris): Lookup ID Kelurahan (`id`), Nama Kelurahan (`kelurahan_nama`), dan Kecamatan (`kecamatan_nama`).
- **`retritype`** (4 baris): Lookup jenis retribusi parkir (TJU, TKP, E-Parkir).
- **`chat_rooms`** (10,000 baris) & **`chat_messages`** (208 baris): Komunikasi pesan instan admin - jukir.
- **`whatsapp`** (2 baris): Log pesan WhatsApp Gateway.
- **`api_tokens`** (1 baris): Autentikasi Bearer Token API mobile.
- **`history_register`** (0 baris): Tabel kosong / dead table.
- **`dump_inquiry`** (1,824 baris): Raw JSON log inquiry dari perbankan.
