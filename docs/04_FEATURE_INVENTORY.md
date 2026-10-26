# 04. FEATURE INVENTORY

Dokumentasi lengkap seluruh fitur sistem `dishub2026` hasil reverse engineering terhadap controller, route, view, dan model.

---

## 4.1 Modul Authentication & User Management

### 1. Web Backoffice Login
- **Feature Name**: Backoffice User Login
- **Purpose**: Autentikasi staf, pengawas, operator, dan admin DISHUB.
- **Actor/User**: Superadmin, Admin, Operator Tim Wilayah, Pengawas.
- **Entry Point**: `GET /login`, `POST /auth/login`
- **Controller/Method**: `App\Controllers\Home::login`, `Home::loginPost`
- **Database Tables**: `users`
- **Input**: `username`, `password`
- **Output**: Redirect ke `/` (Dashboard) atau kembali dengan flash message error.
- **Business Rules**:
  - Menggunakan BCRYPT `password_verify()`.
  - Simpan session: `user_id`, `username`, `nama`, `role`, `isLoggedIn = true`.
- **Known Problems / Security**:
  - Belum ada mekanisme rate limiting (brute force login risk).

---

### 2. User CRUD Management
- **Feature Name**: User Account Management
- **Purpose**: Mengelola akun staf pengguna backoffice.
- **Actor/User**: Superadmin (Role 0).
- **Entry Point**: `GET /user/list`, `POST /user/data`, `GET /user/create`, `POST /user/store`, `GET /user/edit/(:num)`, `POST /user/update/(:num)`, `POST /user/userdelete/(:num)`
- **Controller/Method**: `Home::userlist`, `Home::userdata`, `Home::usercreate`, `Home::userstore`, `Home::useredit`, `Home::userupdate`, `Home::userdelete`
- **Database Tables**: `users`
- **Role Code Mapping (`CONFIRMED`)**:
  - `0`: Superadmin
  - `1`: Admin Bidang
  - `2`: Operator Wilayah (Klojen, Blimbing, Sukun, dll.)
  - `3`: Pengawas / Umum

---

## 4.2 Modul Master Data (Titik Parkir, Anggota Jukir & Penugasan)

### 3. Master Titik Parkir (Titpar)
- **Feature Name**: Parking Spot Master Management
- **Purpose**: Pendataan lokasi fisik titik parkir tepi jalan umum dan tempat khusus parkir.
- **Actor/User**: Admin, Operator.
- **Entry Point**: `GET /titpar`, `POST /titpar/data`, `GET /titpar/create`, `POST /titpar/store`, `GET /titpar/edit/(:num)`, `POST /titpar/update/(:num)`, `GET /titpar/delete/(:num)`, `GET /titpar/undelete/(:num)`
- **Controller/Method**: `App\Controllers\Titpar::*`
- **Database Tables**: `dishub_titpar`, `kelurahan`, `retritype`
- **Business Rules**:
  - Menyimpan koordinat Latitude & Longitude untuk plotting peta GIS.
  - Menampung data pemilik tempat/kantong parkir (`titpar_pmlknama`, `titpar_pmlklhrtmp`).
  - Menampung jenis retribusi: Tepi Jalan Umum (TJU), Tempat Khusus Parkir (TKP), E-Parkir.

---

### 4. Manajemen Shift & Assignment Jukir ke Titik Parkir
- **Feature Name**: Parking Spot Attendant & Shift Management
- **Purpose**: Mengatur penugasan jukir ke titik parkir, menetapkan koordinator titik, dan mengatur jam kerja/shift.
- **Actor/User**: Admin, Operator Wilayah.
- **Entry Point**: 
  - `GET /titpar/manage/(:num)`
  - `POST /titpar/add-koordinator/(:num)`
  - `POST /titpar/add-anggota/(:num)`
  - `POST /titpar/remove-koordinator/(:num)`
  - `POST /titpar/remove-anggota/(:num)`
  - `POST /titpar/make-koordinator/(:num)`
  - `POST /titpar/change-coordinator/(:num)`
  - `POST /titpar/update-shift/(:num)`
- **Controller/Method**: `Titpar::manage`, `Titpar::addKoordinator`, `Titpar::addAnggota`, `Titpar::updateShift`, dll.
- **Database Tables**: `dishub_titpargrup`, `dishub_titpar`, `dishub_anggota`
- **Business Rules**:
  - Satu titik parkir memiliki 1 Koordinator dan bisa memiliki banyak Anggota Jukir.
  - Penugasan jukir memiliki atribut shift (`titpargrup_namashift`, `titpargrup_shiftjammulai`, `titpargrup_shiftjamselesai`, `titpargrup_shifthari`).

---

### 5. Master Data Anggota Jukir (Dishub Anggota)
- **Feature Name**: Parking Attendant Registry
- **Purpose**: Database master seluruh jukir terdaftar di Kota Malang.
- **Actor/User**: Admin.
- **Entry Point**: `GET /anggota`, `POST /anggota/data`, `GET /anggota/create`, `POST /anggota/store`, `GET /anggota/edit/(:num)`, `POST /anggota/update/(:num)`, `GET /anggota/delete/(:num)`
- **Controller/Method**: `App\Controllers\Anggota::*`
- **Database Tables**: `dishub_anggota`
- **Data Attributes**: NIK (No KTP), Nama, Tempat/Tgl Lahir, Alamat, No HP, No Reg / No KTA Baru & Lama, Foto Wajah, Status.

---

## 4.3 Modul Virtual Account & Integrasi Bank Jatim

### 6. Master Virtual Account Owner (VA Owner)
- **Feature Name**: Virtual Account Mapping Management
- **Purpose**: Mengatur nomor Virtual Account Bank Jatim (16 digit) yang diterbitkan untuk setiap koordinator titik parkir.
- **Actor/User**: Admin, Superadmin.
- **Entry Point**: `GET /va-owner`, `POST /va-owner/data`, `GET /va-owner/create`, `POST /va-owner/store`, `GET /va-owner/edit/(:num)`, `POST /va-owner/update/(:num)`, `GET /va-owner/getaccesstoken`
- **Controller/Method**: `VaOwnerController::*`
- **Database Tables**: `va_owner`, `dishub_anggota`, `dishub_titpar`
- **Business Rules**:
  - No VA memiliki prefix `15088` atau `10325`.
  - Berita transfer 1 menampung nama titik parkir, Berita 2 menampung lokasi jalan.
  - Sinkronisasi masa kedaluwarsa VA (`va_owner_expired`).

---

### 7. Bank Jatim Webhook Payment Notification
- **Feature Name**: Real-time VA Payment Callback Receiver
- **Purpose**: Menerima notifikasi otomatis dari Core Banking Bank Jatim saat jukir/koordinator melakukan setoran.
- **Actor/User**: External System (Bank Jatim API Server).
- **Entry Point**: `POST /api/v1.0/transfer-va/va-notify`
- **Controller/Method**: `App\Controllers\Sisparma::va_notify`, `Sisparma::store_payment`
- **Database Tables**: `transaksi`, `dump_transaksi`, `whatsapp`
- **Input Payload (JSON)**:
  ```json
  {
    "latestTransactionStatus": "00",
    "transactionStatusDesc": "Successful",
    "virtualAccountNo": "1508800001910202",
    "amount": { "value": "60000.00", "currency": "IDR" },
    "coreReferenceNo": "FT260902ABCD1234",
    "transactionDate": "2026-09-02T14:30:00+07:00"
  }
  ```
- **Business Rules**:
  1. Cek apakah kombinasi `coreReferenceNo` dan `DATE(transaksi_tanggal)` sudah ada di tabel `transaksi`.
  2. Jika **Belum Ada**: Insert ke tabel `transaksi`, lalu panggil WhatsApp Service untuk mengirim pesan konfirmasi setoran ke koordinator.
  3. Jika **Sudah Ada (Duplikat)**: Simpan ke tabel `dump_transaksi` sebagai catatan log audit.
  4. Berikan response JSON ke Bank: `{"responseCode": "2000000", "responseMessage": "Request has been processed successfully"}`.
- **Security Flaw Terdeteksi**: Belum ada verifikasi header SNAP signature (HMAC/RSA) pada endpoint ini.

---

## 4.4 Modul Potensi & Realisasi Retribusi

### 8. Penetapan Potensi Retribusi Harian
- **Feature Name**: Daily Potential Assessment
- **Purpose**: Menetapkan nilai kewajiban setoran harian untuk setiap nomor VA / Titik Parkir.
- **Actor/User**: Admin, Tim Penilai Potensi.
- **Entry Point**: `GET /potensi`, `POST /potensi/data`, `GET /potensi/create/(:num)`, `POST /potensi/store/(:num)`, `POST /potensi/update/(:num)`
- **Controller/Method**: `Potensi::*`
- **Database Tables**: `potensi`, `va_owner`
- **Business Rules**:
  - Kolom potensi mencakup: `senin`, `selasa`, `rabu`, `kamis`, `jumat`, `sabtu`, `minggu`.
  - Kolom kalkulasi turunan: `mingguan` (Sum Senin-Minggu), `bulanan` (Mingguan * 4 atau formula hari), `tahunan`.

---

### 9. Rekonsiliasi Realisasi Setoran & Tagihan
- **Feature Name**: Revenue Realization & Arrears Monitoring
- **Purpose**: Membandingkan akumulasi pembayaran setoran aktual (`transaksi`) terhadap target potensi (`potensi`) dan potongan dispensasi kendala (`kendala`).
- **Actor/User**: Admin, Pengawas, Kadis.
- **Entry Point**: `GET /potensi/realisasi`, `POST /potensi/datatagihan`, `GET /potensi/realisasi/tahunan`, `GET /potensi/export`
- **Controller/Method**: `Potensi::realisasi`, `Potensi::datatagihan`, `Potensi::export`
- **Formula Bisnis**:
  $$\text{Target Bulanan} = \sum (\text{Potensi Hari}_i \times \text{Jumlah Hari}_i \text{ dalam bulan})$$
  $$\text{Kurang Setor} = \text{Target Bulanan} - \text{Total Realisasi Setoran} - \text{Total Potongan Kendala Disetujui}$$

---

## 4.5 Modul Kendala & Dispensasi Operasional

### 10. Pengajuan & Approval Kendala Setoran
- **Feature Name**: Operational Issue & Fee Waiver Management
- **Purpose**: Memfasilitasi jukir yang tidak dapat memenuhi target setoran harian karena faktor eksternal (hujan deras, perbaikan jalan, hari libur toko/pasar) dengan mengunggah foto bukti lapangan.
- **Actor/User**: Jukir (via Mobile/Web), Admin Bidang Parkir (Approval).
- **Entry Point**: 
  - `GET /kendalaindex`, `POST /kendaladata`
  - `GET /kendalaadmin`, `POST /kendalaadminsave`
  - `POST /kendala/approve`, `POST /kendala/reject`, `POST /kendala/updatePotongan`
- **Controller/Method**: `SisparmaApp::kendalaindex`, `SisparmaApp::approve`, `SisparmaApp::reject`, `SisparmaApp::updatePotongan`
- **Database Tables**: `kendala`, `va_owner`
- **Business Rules**:
  - Pengajuan harus melampirkan foto bukti (`kendala_foto`) dan alasan (`kendala_alasan`).
  - Admin melakukan verifikasi: jika status di-*Approve*, nominal potongan dispensasi akan memotong kewajiban tagihan jukir pada tanggal tersebut.

---

## 4.6 Modul Pendaftaran Mandiri Jukir & KTA Online

### 11. Portal Registrasi Publik (KTA / Perijinan Jukir)
- **Feature Name**: Public Attendant Registration & Document Submission
- **Purpose**: Memungkinkan calon juru parkir dan koordinator mendaftar secara online dan mengunggah berkas persyaratan.
- **Actor/User**: Masyarakat / Calon Jukir.
- **Entry Point**: 
  - `GET /register` (Landing)
  - `GET /register/form`, `POST /register/save`
  - `GET /register/progress`, `POST /register/progress` (Tracking status via NIK/No KTP)
  - Sub-resource: `register/titpar/*`, `register/anggota/*`, `register/upload/*`
- **Controller/Method**: `RegisterController::*`, `KtaController::*`, `KtaUploadController::*`
- **Database Tables**: `register`, `register_titpar`, `register_anggota`
- **Persyaratan Dokumen**: Foto KTP, Kartu Keluarga (KK), Foto Tampak Depan/Samping Lokasi, Surat Pernyataan Kesanggupan, Surat Pernyataan Tidak Keberatan Warga Sekitar, Denah Lokasi.

---

### 12. Verifikasi Pendaftaran & Penerbitan Surat Keputusan (Admin)
- **Feature Name**: Registration Verification & Letter Generation
- **Purpose**: Admin memeriksa keabsahan berkas, memberikan catatan revisi, dan mencetak Surat Keputusan resmi.
- **Actor/User**: Admin Bidang Parkir.
- **Entry Point**: 
  - `GET /admin/register`, `GET /admin/register/detail/(:num)`
  - `POST /admin/register/approve`
  - `GET /admin/register/print-penunjukan/(:num)`
  - `GET /register/print_persetujuan`
- **Controller/Method**: `Admin\RegisterController::*`, `Admin\TitparController::*`
- **Business Rules**:
  - Cetak **Surat Perjanjian / Persetujuan Pengelolaan Parkir**.
  - Cetak **Surat Perintah Tugas / Penunjukan Juru Parkir**.
  - Setelah disetujui penuh, data siap di-generate ke rekening VA Bank Jatim.

---

## 4.7 Modul Distribusi Karcis / Tiket Fisik

### 13. Distribusi Tiket Retribusi Parkir
- **Feature Name**: Physical Ticket Allocation & Serial Tracking
- **Purpose**: Mencatat penerimaan dan penyaluran bendel karcis parkir fisik berporporasi untuk Motor, Mobil, dan Truk.
- **Actor/User**: Petugas Pengelola Karcis, Koordinator Jukir.
- **Entry Point**: `GET /tiket`, `POST /tiket/data`, `GET /tiket/pesan/(:any)`, `POST /tiket/store`, `GET /tiket/histori`, `GET /tiket/print/(:any)`
- **Controller/Method**: `Tiket::*`
- **Database Tables**: `tiket`, `va_owner`
- **Data Attributes**: Nomor seri awal & nomor seri akhir untuk motor (`tiket_nomor_motor_awal` s/d `tiket_nomor_motor_akhir`), mobil, dan truk.

---

## 4.8 Modul Dashboard, Command Center & Reporting

### 14. Live Command Center & Executive Dashboard
- **Feature Name**: Real-Time Revenue Command Center
- **Purpose**: Layar monitor ruang rapat/command center DISHUB untuk memantau pendapatan harian langsung, perbandingan per kecamatan, dan status keaktifan titik parkir.
- **Actor/User**: Kepala Dinas, Kabid Parkir, Publik.
- **Entry Point**: `GET /commandcenter`, `GET /publicdashboard`, `GET /pendapatanhariini`, `GET /targetsetoranbulanan`, `GET /targetsetoranbulanankecamatan/(:num)`
- **Controller/Method**: `Home::commandcenter`, `Home::publicdashboard`, `Home::getPendapatanHariIni`, `Home::targetSetoranBulananKecamatan`
- **Visualisasi**:
  - Live Total Revenue Hari Ini (Rupiah).
  - Peta Spasial Titik Parkir (Leaflet OpenStreetMap) dengan indikator warna status setor.
  - Grafik Capaian Target Kecamatan: Klojen, Blimbing, Lowokwaru, Sukun, Kedungkandang.
