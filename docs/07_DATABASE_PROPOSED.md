# 07. PROPOSED DATABASE ARCHITECTURE

Rancangan arsitektur database baru (*Recreation Blueprint*) yang telah dinormalisasi, menggunakan foreign keys, tipe data yang tepat, dan naming convention standar.

---

## 7.1 Daftar Tabel Baru (Clean Schema)

```
Database: dishub_recreate

1.  users                      (Autentikasi & Akun Staf/Pengawas)
2.  districts                  (Lookup Kecamatan & Kelurahan Kota Malang)
3.  retribution_types          (Lookup Jenis Retribusi: TJU, TKP, E-Parkir)
4.  attendants                 (Master Juru Parkir & Koordinator Terdaftar)
5.  parking_spots              (Master Lokasi Fisik Titik Parkir & GIS)
6.  attendant_assignments      (Pivot Penugasan Jukir ke Titpar + Shift Kerja)
7.  virtual_accounts           (Rekening VA Bank Jatim Terbit)
8.  daily_potentials           (Matriks Potensi Target Harian per VA)
9.  transactions               (Mutasi Setoran Retribusi Sukses)
10. raw_transaction_logs       (Log Audit / Raw Webhook Callback dari Bank)
11. operational_issues         (Laporan Kendala Lapangan & Permohonan Keringanan)
12. ticket_distributions       (Distribusi Karcis Retribusi Fisik)
13. attendant_registrations    (Pengajuan Pendaftaran Jukir/Titpar Baru & Verifikasi SK)
14. registration_documents     (Berkas Lampiran Pendaftaran: KTP, KK, Denah, Surat)
```

---

## 7.2 Spesifikasi Detail Skema Tabel Baru

### 1. TABLE: `users`
```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('superadmin', 'admin', 'operator', 'supervisor') NOT NULL DEFAULT 'operator',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. TABLE: `districts` *(Refactor dari `kelurahan`)*
```sql
CREATE TABLE districts (
    id VARCHAR(20) PRIMARY KEY, -- Kode Wilayah Kelurahan
    subdistrict_id VARCHAR(10) NOT NULL, -- Kode Kecamatan
    subdistrict_name VARCHAR(100) NOT NULL, -- Nama Kecamatan (Klojen, Blimbing, dll)
    village_name VARCHAR(100) NOT NULL, -- Nama Kelurahan
    INDEX idx_subdistrict (subdistrict_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. TABLE: `retribution_types` *(Refactor dari `retritype`)*
```sql
CREATE TABLE retribution_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL, -- 'Tepi Jalan Umum (TJU)', 'Tempat Khusus Parkir (TKP)', 'E-Parkir'
    code VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. TABLE: `attendants` *(Refactor dari `dishub_anggota`)*
```sql
CREATE TABLE attendants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) NOT NULL UNIQUE, -- Nomor KTP 16 digit
    registration_number VARCHAR(30) NULL, -- No KTA / No Reg Resmi
    full_name VARCHAR(150) NOT NULL,
    birth_place VARCHAR(100) NULL,
    birth_date DATE NULL,
    gender ENUM('L', 'P') NOT NULL,
    address TEXT NOT NULL,
    phone_number VARCHAR(25) NOT NULL,
    photo_path VARCHAR(255) NULL,
    attendant_type ENUM('coordinator', 'member') NOT NULL DEFAULT 'member',
    coordinator_id INT UNSIGNED NULL, -- Jika member, menginduk ke attendant_id koordinator
    status ENUM('active', 'inactive', 'suspended', 'deceased') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_attendant_coordinator FOREIGN KEY (coordinator_id) REFERENCES attendants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5. TABLE: `parking_spots` *(Refactor dari `dishub_titpar`)*
```sql
CREATE TABLE parking_spots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spot_code VARCHAR(30) NULL UNIQUE, -- Nomor Registrasi Titik Parkir
    spot_name VARCHAR(150) NOT NULL, -- Nama Tempat Usaha / Objek Parkir
    address VARCHAR(255) NOT NULL, -- Nama Jalan / Alamat
    district_id VARCHAR(20) NULL, -- FK ke districts
    retribution_type_id INT UNSIGNED NOT NULL DEFAULT 1,
    owner_name VARCHAR(150) NULL, -- Nama pemilik usaha / persil
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    status ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_spot_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
    CONSTRAINT fk_spot_retri_type FOREIGN KEY (retribution_type_id) REFERENCES retribution_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 6. TABLE: `attendant_assignments` *(Refactor dari `dishub_titpargrup`)*
```sql
CREATE TABLE attendant_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parking_spot_id INT UNSIGNED NOT NULL,
    attendant_id INT UNSIGNED NOT NULL,
    role ENUM('coordinator', 'member') NOT NULL DEFAULT 'member',
    shift_name VARCHAR(50) NULL, -- 'Pagi', 'Siang', 'Sore', 'Malam'
    shift_start_time TIME NULL,
    shift_end_time TIME NULL,
    working_days_per_week TINYINT UNSIGNED DEFAULT 7,
    valid_from DATE NULL,
    valid_until DATE NULL,
    status ENUM('active', 'expired', 'transferred') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assign_spot FOREIGN KEY (parking_spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_attendant FOREIGN KEY (attendant_id) REFERENCES attendants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7. TABLE: `virtual_accounts` *(Refactor dari `va_owner`)*
```sql
CREATE TABLE virtual_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    va_number VARCHAR(20) NOT NULL UNIQUE, -- 16-digit Bank Jatim VA
    parking_spot_id INT UNSIGNED NOT NULL,
    coordinator_attendant_id INT UNSIGNED NOT NULL,
    news_line_1 VARCHAR(150) NULL, -- Nama Titik Parkir
    news_line_2 VARCHAR(150) NULL, -- Lokasi Jalan
    contact_phone VARCHAR(25) NULL,
    expired_at DATETIME NOT NULL,
    status ENUM('active', 'inactive', 'expired') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_va_spot FOREIGN KEY (parking_spot_id) REFERENCES parking_spots(id) ON DELETE RESTRICT,
    CONSTRAINT fk_va_coordinator FOREIGN KEY (coordinator_attendant_id) REFERENCES attendants(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 8. TABLE: `daily_potentials` *(Refactor dari `potensi`)*
```sql
CREATE TABLE daily_potentials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    virtual_account_id INT UNSIGNED NOT NULL UNIQUE,
    monday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    tuesday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    wednesday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    thursday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    friday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    saturday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    sunday_amount INT UNSIGNED NOT NULL DEFAULT 0,
    parking_space_unit INT UNSIGNED NULL DEFAULT 0, -- Satuan Ruang Parkir (SRP)
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_potential_va FOREIGN KEY (virtual_account_id) REFERENCES virtual_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 9. TABLE: `transactions` *(Refactor dari `transaksi`)*
```sql
CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    virtual_account_id INT UNSIGNED NOT NULL,
    amount INT UNSIGNED NOT NULL,
    transaction_time DATETIME NOT NULL,
    core_reference_no VARCHAR(100) NOT NULL UNIQUE, -- No referensi unik perbankan
    bank_response_code VARCHAR(20) NOT NULL DEFAULT '00',
    status_desc VARCHAR(100) NOT NULL DEFAULT 'Successful',
    wa_notification_status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    wa_notification_log TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trx_va FOREIGN KEY (virtual_account_id) REFERENCES virtual_accounts(id) ON DELETE RESTRICT,
    INDEX idx_trx_time (transaction_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 10. TABLE: `operational_issues` *(Refactor dari `kendala`)*
```sql
CREATE TABLE operational_issues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    virtual_account_id INT UNSIGNED NOT NULL,
    issue_date DATE NOT NULL,
    reason TEXT NOT NULL,
    photo_evidence_path VARCHAR(255) NOT NULL,
    deduction_amount INT UNSIGNED NOT NULL DEFAULT 0, -- Nilai potongan dispensasi yang disetujui
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    verified_by_user_id INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_issue_va FOREIGN KEY (virtual_account_id) REFERENCES virtual_accounts(id) ON DELETE RESTRICT,
    CONSTRAINT fk_issue_verifier FOREIGN KEY (verified_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 11. TABLE: `ticket_distributions` *(Refactor dari `tiket`)*
```sql
CREATE TABLE ticket_distributions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    virtual_account_id INT UNSIGNED NOT NULL,
    distribution_date DATE NOT NULL,
    motorcycle_qty INT UNSIGNED NOT NULL DEFAULT 0,
    motorcycle_serial_start VARCHAR(50) NULL,
    motorcycle_serial_end VARCHAR(50) NULL,
    car_qty INT UNSIGNED NOT NULL DEFAULT 0,
    car_serial_start VARCHAR(50) NULL,
    car_serial_end VARCHAR(50) NULL,
    truck_qty INT UNSIGNED NOT NULL DEFAULT 0,
    truck_serial_start VARCHAR(50) NULL,
    truck_serial_end VARCHAR(50) NULL,
    received_status ENUM('draft', 'handed_over', 'received') NOT NULL DEFAULT 'handed_over',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_va FOREIGN KEY (virtual_account_id) REFERENCES virtual_accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 7.3 Deviasi Desain yang Diterapkan Saat Migrasi (Realizado 15-Sep)

Saat migrasi riil ke `dishub_recreate`, beberapa modifikasi disengaja dari blueprint original untuk menyesuaikan dengan realitas data sumber (`dishub2026`):

| Tabel | Modifikasi | Alasan (data sumber real) |
| :--- | :--- | :--- |
| `transactions` | `core_reference_no` **bukan UNIQUE** | 4,063 duplikat legit di `transaksi` (retry bank) |
| `transactions` | `virtual_account_id` **nullable** | 79 transaksi dengan VA tak terdaftar |
| `virtual_accounts` | `coordinator_attendant_id` **nullable** | 145 VA tanpa koordinator ditugaskan |
| `operational_issues` | `photo_evidence_path` **nullable** | 4,699 rekord tanpa foto |
| `parking_spots` | `spot_code` duplikat diberi suffix `-<id>` | 88 `titpar` dengan kode sama (`0016` ×92) |
| `attendants` | NIK kosong/invalid diisi `P`+15 digit | 265 rekord tanpa NIK valid (garansi UNIQUE) |
| `parking_spots` | `district_id` set NULL jika invalid | 2 spot dengan kode distrik tak ada (`1105012007`) |
| `attendant_registrations` | Fusion `register`+`register_titpar`+`register_anggota` dengan kolom `registration_type` | Schema duplikat di sumber |
| `attendants` | `coordinator_id` selalu NULL di data | Relasi koordinator hidup di `attendant_assignments`, bukan di tabel anggota |
