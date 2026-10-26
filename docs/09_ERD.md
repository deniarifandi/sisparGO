# 09. ENTITY RELATIONSHIP DIAGRAM (ERD)

Diagram relasi entitas konseptual dan relasional untuk arsitektur database baru (*Proposed Clean Schema*).

---

## 9.1 ERD Konseptual (Mermaid Format)

```mermaid
erDiagram
    users {
        int id PK
        string username
        string full_name
        string role
        boolean is_active
    }

    districts {
        string id PK
        string subdistrict_name
        string village_name
    }

    retribution_types {
        int id PK
        string type_name
        string code
    }

    attendants {
        int id PK
        string nik UK
        string registration_number
        string full_name
        string phone_number
        string attendant_type
        int coordinator_id FK
        string status
    }

    parking_spots {
        int id PK
        string spot_code UK
        string spot_name
        string address
        string district_id FK
        int retribution_type_id FK
        decimal latitude
        decimal longitude
        string status
    }

    attendant_assignments {
        int id PK
        int parking_spot_id FK
        int attendant_id FK
        string role
        string shift_name
        time shift_start_time
        time shift_end_time
        date valid_until
        string status
    }

    virtual_accounts {
        int id PK
        string va_number UK
        int parking_spot_id FK
        int coordinator_attendant_id FK
        string news_line_1
        string news_line_2
        datetime expired_at
        string status
    }

    daily_potentials {
        int id PK
        int virtual_account_id FK
        int monday_amount
        int tuesday_amount
        int wednesday_amount
        int thursday_amount
        int friday_amount
        int saturday_amount
        int sunday_amount
        int parking_space_unit
    }

    transactions {
        int id PK
        int virtual_account_id FK
        int amount
        datetime transaction_time
        string core_reference_no UK
        string bank_response_code
        string wa_notification_status
    }

    operational_issues {
        int id PK
        int virtual_account_id FK
        date issue_date
        string reason
        string photo_evidence_path
        int deduction_amount
        string status
        int verified_by_user_id FK
    }

    ticket_distributions {
        int id PK
        int virtual_account_id FK
        date distribution_date
        int motorcycle_qty
        string motorcycle_serial_start
        string motorcycle_serial_end
        int car_qty
        string car_serial_start
        string car_serial_end
        string received_status
    }

    %% Relationships
    districts ||--o{ parking_spots : "contains"
    retribution_types ||--o{ parking_spots : "categorizes"
    attendants ||--o{ attendants : "supervises (coordinator to member)"
    
    parking_spots ||--o{ attendant_assignments : "assigned to"
    attendants ||--o{ attendant_assignments : "works at"

    parking_spots ||--o{ virtual_accounts : "has VA"
    attendants ||--o{ virtual_accounts : "owns VA"

    virtual_accounts ||--|| daily_potentials : "has daily target"
    virtual_accounts ||--o{ transactions : "receives payment"
    virtual_accounts ||--o{ operational_issues : "submits issue"
    virtual_accounts ||--o{ ticket_distributions : "receives physical tickets"
    
    users ||--o{ operational_issues : "approves"
```

---

## 9.2 Penjelasan Kardinalitas & Integritas Relasi

1. **`districts` (1) ── (N) `parking_spots`**:
   - Satu kelurahan menaungi banyak titik lokasi parkir. Titik parkir merujuk ke kelurahan induk untuk agregasi laporan target per kecamatan.
2. **`attendants` (1) ── (N) `attendants` (Self-Referencing)**:
   - Seorang koordinator parkir membawahi beberapa anggota jukir lapangan.
3. **`parking_spots` (N) ── (N) `attendants` via `attendant_assignments`**:
   - Satu titik parkir dapat dijaga oleh beberapa jukir dengan shift waktu berbeda (Pagi/Sore).
   - Seorang jukir dapat ditugaskan pada titik parkir tertentu dengan masa berlaku SK penugasan.
4. **`virtual_accounts` (1) ── (1) `daily_potentials`**:
   - Setiap nomor Virtual Account memiliki 1 konfigurasi target potensi 7 hari (Senin s/d Minggu).
5. **`virtual_accounts` (1) ── (N) `transactions`**:
   - Satu nomor Virtual Account menerima banyak mutasi setoran harian dari koordinator jukir.
6. **`virtual_accounts` (1) ── (N) `operational_issues`**:
   - Koordinator pemegang VA dapat mengajukan kendala operasional (hujan, penutupan jalan) pada tanggal tertentu untuk meminta dispensasi potongan tagihan harian.

---

## 9.3 Deviasi Cardinalitas Aktual (Setelah Migrasi, 15-Sep)

Setelah migrasi riil ke `dishub_recreate`, beberapa kardinalitas pada blueprint dilonggarkan untuk menampung data sumber — terverifikasi di database:

| Relasi | Blueprint asli | Keadaan setelah migrasi | Alasan |
| :--- | :--- | :--- | :--- |
| `virtual_accounts.parking_spot_id` | 1:N wajib | **nullable** (1 titik tanpa VA) | — |
| `virtual_accounts.coordinator_attendant_id` | 1:N wajib | **nullable** (145 VA tanpa koordinator) | koordinator tidak selalu terisi |
| `transactions.virtual_account_id` | wajib | **nullable** (79 transaksi orphan) | VA rujukan tidak ikut migrasi |
| `transactions.core_reference_no` | UNIQUE (idempotensi) | **bukan UNIQUE** (data duplikat) | retry bank yang sama |
| `operational_issues.photo_evidence_path` | wajib | **nullable** | 4,699 kendala tanpa foto |
| `parking_spots.district_id` | FK ketat | diset NULL jika invalid | 2 kode distrik tidak ada |

Diagram ERD konseptual tetap berlaku untuk desain logis; deviasi ini hanya memengaruhi integritas fisik data yang ter-migrasi.
