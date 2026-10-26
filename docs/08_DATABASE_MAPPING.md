# 08. DATABASE MIGRATION & DATA MAPPING

Panduan transformasi data dari skema database lama (`dishub2026`) ke skema database baru (`dishub_recreate`).

---

## 8.1 Data Mapping Tables

### 1. `users` → `users`
| Old Column (`users`) | New Column (`users`) | Transformasi Logic |
| :--- | :--- | :--- |
| `user_id` | `id` | Direct mapping |
| `username` | `username` | Trim & lowercase |
| `password` | `password_hash` | Password hash BCRYPT dipertahankan (langsung kompatibel) |
| `nama` | `full_name` | Direct mapping |
| `role` | `role` | `0` → `'superadmin'`, `1` → `'admin'`, `2` → `'operator'`, `3` → `'supervisor'` |

---

### 2. `kelurahan` → `districts`
| Old Column (`kelurahan`) | New Column (`districts`) | Transformasi Logic |
| :--- | :--- | :--- |
| `id` | `id` | Direct mapping (kode kelurahan) |
| `kecamatan_id` | `subdistrict_id` | Direct mapping |
| `kecamatan_nama` | `subdistrict_name` | Trim & proper case |
| `kelurahan_nama` | `village_name` | Trim & proper case |

---

### 3. `dishub_anggota` → `attendants`
| Old Column (`dishub_anggota`) | New Column (`attendants`) | Transformasi Logic |
| :--- | :--- | :--- |
| `anggota_id` | `id` | Direct mapping (integer unsigned) |
| `anggota_noktp` | `nik` | Pembersihan string NIK (16 digit) |
| `anggota_nama` | `full_name` | Trim whitespace |
| `anggota_newnoreg` / `anggota_noreg` | `registration_number` | Prioritas `anggota_newnoreg`, fallback `anggota_noreg` |
| `anggota_alamat` | `address` | Direct mapping |
| `anggota_tgllhr` | `birth_date` | Direct date mapping |
| `anggota_tmplhr` | `birth_place` | Direct mapping |
| `anggota_jnsklm` | `gender` | `'L'` atau `'P'` |
| `anggota_nohp` | `phone_number` | Standarisasi nomor HP (`08...` atau `628...`) |
| `anggota_foto` | `photo_path` | Mapping relative path file upload |
| `anggota_jukirtypeid` | `attendant_type` | `1` → `'coordinator'`, `2` / `3` → `'member'` |
| `koor_id` | `coordinator_id` | Integer FK ke `attendants.id` |
| `anggota_status` | `status` | `3` → `'active'`, otherwise `'inactive'` |

---

### 4. `dishub_titpar` → `parking_spots`
| Old Column (`dishub_titpar`) | New Column (`parking_spots`) | Transformasi Logic |
| :--- | :--- | :--- |
| `titpar_id` | `id` | Direct mapping |
| `titpar_newnoreg` / `titpar_noreg` | `spot_code` | Prioritas nomor registrasi baru |
| `titpar_namatempat` | `spot_name` | Trim whitespace |
| `titpar_lokasi` | `address` | Direct mapping |
| `titpar_pmlknama` | `owner_name` | Direct mapping |
| `titpar_retritypeid` | `retribution_type_id`| FK ke `retribution_types.id` |
| `titpar_klrhnid` | `district_id` | FK ke `districts.id` (validasi kelurahan exists) |
| `titpar_latitude` | `latitude` | Decimal GIS coordinate |
| `titpar_longitude` | `longitude` | Decimal GIS coordinate |
| `titpar_status` | `status` | `3` → `'active'`, otherwise `'inactive'` |

---

### 5. `dishub_titpargrup` → `attendant_assignments`
| Old Column (`dishub_titpargrup`) | New Column (`attendant_assignments`) | Transformasi Logic |
| :--- | :--- | :--- |
| `titpargrup_id` | `id` | Direct mapping |
| `titpargrup_titparid` | `parking_spot_id` | FK ke `parking_spots.id` |
| `titpargrup_anggotaid` | `attendant_id` | FK ke `attendants.id` |
| `titpargrup_staanggota` | `role` | `'1'` → `'coordinator'`, `'2'` → `'member'` |
| `titpargrup_namashift` | `shift_name` | Trim shift string |
| `titpargrup_shiftjammulai` | `shift_start_time` | Konversi string `HH:MM` ke format `TIME` |
| `titpargrup_shiftjamselesai` | `shift_end_time` | Konversi string `HH:MM` ke format `TIME` |
| `titpargrup_shifthari` | `working_days_per_week` | Integer hari kerja |
| `titpargrup_masaberlakustart` | `valid_from` | Date mapping |
| `titpargrup_masaberlakuend` | `valid_until` | Date mapping |

---

### 6. `va_owner` → `virtual_accounts`
| Old Column (`va_owner`) | New Column (`virtual_accounts`) | Transformasi Logic |
| :--- | :--- | :--- |
| `va_owner_id` | `id` | Direct mapping |
| `va_owner_va` | `va_number` | Trim nomor VA 16 digit |
| `va_owner_titparid` | `parking_spot_id` | Casting string ke integer FK `parking_spots.id` |
| `va_owner_anggotaid` | `coordinator_attendant_id` | Casting string ke integer FK `attendants.id` |
| `va_owner_berita_1` | `news_line_1` | Direct mapping |
| `va_owner_berita_2` | `news_line_2` | Direct mapping |
| `va_owner_hp` | `contact_phone` | Standarisasi no HP |
| `va_owner_expired` | `expired_at` | Konversi ISO string (`2026-12-31T00:00:00+07:00`) ke format `DATETIME` (`2026-12-31 00:00:00`) |

---

### 7. `potensi` → `daily_potentials`
| Old Column (`potensi`) | New Column (`daily_potentials`) | Transformasi Logic |
| :--- | :--- | :--- |
| `potensi_id` | `id` | Direct mapping |
| `potensi_va` | `virtual_account_id` | Lookup `virtual_accounts.id` berdasarkan `va_number = potensi_va` |
| `senin` s/d `minggu` | `monday_amount` s/d `sunday_amount` | Default `0` jika null |
| `srp` | `parking_space_unit` | Integer unit SRP |

---

### 8. `transaksi` → `transactions`
| Old Column (`transaksi`) | New Column (`transactions`) | Transformasi Logic |
| :--- | :--- | :--- |
| `transaksi_id` | `id` | Direct mapping |
| `transaksi_va` | `virtual_account_id` | Lookup `virtual_accounts.id` berdasarkan `va_number = transaksi_va` |
| `transaksi_nominal` | `amount` | Nominal integer setoran |
| `transaksi_tanggal` | `transaction_time` | DATETIME transaksi |
| `coreReferenceNo` | `core_reference_no` | Standarisasi kode unik referensi bank |
| `latestTransactionStatus` | `bank_response_code` | Kode status (contoh `'00'`) |
| `transactionStatusDesc` | `status_desc` | Deskripsi status |
| `wa_konfirmasi` | `wa_notification_log`| Text log |

---

## 8.2 Urutan Eksekusi Migrasi Data (Migration Order)
Untuk mencegah error integrity constraint / foreign key checks, migrasi harus dijalankan dengan urutan berikut:

1. `users`
2. `districts` (`kelurahan`)
3. `retribution_types` (`retritype`)
4. `attendants` (`dishub_anggota`)
5. `parking_spots` (`dishub_titpar`)
6. `attendant_assignments` (`dishub_titpargrup`)
7. `virtual_accounts` (`va_owner`)
8. `daily_potentials` (`potensi`)
9. `transactions` (`transaksi`)
10. `operational_issues` (`kendala`)
11. `ticket_distributions` (`tiket`)

---

## 8.3 Hasil Migrasi Aktual (15-Sep) — Jumlah Baris Final

Migrasi telah dieksekusi dari `dishub2026` → `dishub_recreate`. Jumlah terverifikasi per tabel:

| No | Tabel Sumber | Tabel Tujuan | Row Sumber | Row Migrasi | Fallback / Skipp |
| :---: | :--- | :---: | :---: | :--- |
| 1 | `users` | `users` | 18 | 18 | — |
| 2 | `kelurahan` | `districts` | 57 | 57 | — |
| 3 | `retritype` | `retribution_types` | 4 | 4 | — |
| 4 | `dishub_anggota` | `attendants` | 4,652 | 4,652 | 265 NIK kosong/invalid → `P`+15 digit |
| 5 | `dishub_titpar` | `parking_spots` | 1,708 | 1,708 | 88 kode duplikat → suffix `-<id>` |
| 6 | `dishub_titpargrup` | `attendant_assignments` | 4,832 | 4,825 | 7 duplikat literal dihapus |
| 7 | `va_owner` | `virtual_accounts` | 880 | 880 | 145 tanpa koordinator (NULL), 1 `titparid=0`→NULL |
| 8 | `potensi` | `daily_potentials` | 1,005 | 874 | 131 VA tak ditemuai di `virtual_accounts` |
| 9 | `transaksi` | `transactions` | 33,933 | 33,933 | 79 VA hilang → NULL; 4,063 ref duplikat |
| 10 | `kendala` | `operational_issues` | 6,968 | 6,968 | 4,699 foto kosong (NULL) |
| 11 | `tiket` | `ticket_distributions` | 1,867 | 1,867 | 1 `tiket_va='0'`→NULL |
| 12 | `register` +`.titpar`+`.anggota` | `attendant_registrations` | 11 | 11 | koordinator=5, spot=3, member=3 |
| 13 | `register*` (berkas) | `registration_documents` | — | 17 | dokumen KTP/KK/foto/denah |

**Nota**: `history_register` = 0 baris (tabel dead) — tidak migrasi.
