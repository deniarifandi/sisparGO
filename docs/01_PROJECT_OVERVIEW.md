# 01. PROJECT OVERVIEW

## 1.1 Executive Summary
**dishub2026** (berbasis evolusi dari *SISPARMA* - Sistem Informasi Pengelolaan Parkir Malang) adalah sistem informasi operasional dan tata kelola retribusi parkir daerah di lingkungan Dinas Perhubungan Kota Malang. 

Sistem ini dirancang untuk mendigitalkan seluruh siklus pengelolaan retribusi parkir tepi jalan umum (TJU) dan tempat khusus parkir (TKP):
1. **Registrasi & Verifikasi Jukir**: Pendaftaran mandiri juru parkir (jukir) dan koordinator parkir beserta verifikasi dokumen (KTP, KK, Surat Tugas/Penunjukan, Foto Lokasi).
2. **Master Data & Wilayah**: Pengelolaan titik parkir (titpar), pembagian zona/rayon/kelurahan/kecamatan, dan penugasan shift kerja.
3. **Integrasi Perbankan (Bank Jatim SNAP VA)**: Penerbitan Virtual Account (VA) 16 digit terstandarisasi SNAP Bank Indonesia / Bank Jatim, inquiry tagihan real-time, dan webhook notification penerimaan setoran non-tunai.
4. **Potensi & Target Retribusi**: Penetapan potensi setoran harian (Senin–Minggu) per titik parkir dan komparasi realisasi setoran vs target harian/bulanan/tahunan.
5. **Karcis/Tiket & Kendala Operasional**: Distribusi karcis retribusi fisik bertingkat nomor seri, serta pengajuan dispensasi/kendala tidak setor jukir dengan verifikasi foto bukti lapangan.
6. **Executive Dashboard & Command Center**: Pemantauan real-time pendapatan daerah, peta sebaran titik parkir (GIS Leaflet), dan notifikasi WhatsApp Gateway ke koordinator parkir.

---

## 1.2 Business Context & Actors

| Actor / Peran | Deskripsi Tanggung Jawab |
| :--- | :--- |
| **Superadmin (Role 0)** | Mengelola akun user, konfigurasi sistem, audit menyeluruh, dan sinkronisasi Bank Jatim. |
| **Admin Bidang Parkir (Role 1)** | Verifikasi berkas pendaftaran jukir, penugasan koordinator/anggota ke titik parkir, approval kendala jukir, dan pencatatan distribusi tiket. |
| **Operator / Tim Lapangan (Role 2)** | Mengawasi wilayah/kecamatan (Tim Klojen, Tim Blimbing-Lowokwaru, Tim Sukun-Kedungkandang), input potensi lapangan, dan monitoring setoran. |
| **Pengawas / Pimpinan (Role 3 / Kadis)** | Akses baca laporan pendapatan, statistik capaian target bulanan, dan Command Center Dashboard. |
| **Koordinator Jukir (Public/Client)** | Pemegang rekening Virtual Account (VA), mengelola anggota jukir di bawahnya, menerima konfirmasi setoran via WhatsApp, dan mengajukan kendala setoran. |
| **Anggota Jukir (Client/Mobile)** | Petugas operasional di titik parkir yang bertugas memungut retribusi dan menjalankan shift kerja. |
| **Bank Jatim Core System (External System)** | Sistem perbankan yang mengirim inquiry tagihan dan webhook callback saat jukir melakukan transfer/setor ke VA. |

---

## 1.3 Key Business Workflows

### 1. Workflow Pendaftaran Jukir & Titik Parkir (KTA/Perijinan)
```
[Calon Koordinator / Jukir]
         │ (Mengisi Form Pendaftaran Publik + Upload KTP, KK, Denah)
         ▼
[Tabel: register & register_titpar & register_anggota]
         │
         ▼
[Admin Review & Verifikasi Lapangan]
         ├─► [Revisi / Tambah Catatan] ──► Notifikasi ke Jukir
         ├─► [Tolak / Reject]
         └─► [Approve / Setujui]
                 │
                 ├──► Generate Surat Persetujuan & Surat Penunjukan
                 ├──► Migrasi data ke `dishub_anggota` & `dishub_titpar`
                 └──► Create Virtual Account Bank Jatim (via API SNAP)
```

### 2. Workflow Setoran Retribusi & Webhook Bank Jatim
```
[Jukir / Koordinator Setor ke VA Bank Jatim]
         │
         ▼
[Bank Jatim Core Gateway]
         │
         ├──► (Optional Inquiry) GET /api/v1.0/transfer-va/inquiry
         │
         └──► (Payment Callback) POST /api/v1.0/transfer-va/va-notify
                      │
                      ▼
             [Controller: Sisparma::va_notify]
                      │
                      ├──► Cek Duplikasi `coreReferenceNo`
                      ├──► Simpan ke tabel `transaksi` (jika baru)
                      ├──► Simpan ke `dump_transaksi` (jika duplikat)
                      └──► Trigger WhatsApp Gateway (Notifikasi Sukses ke Jukir)
```

### 3. Workflow Potensi & Monitoring Realisasi
```
[Admin / Tim Menetapkan Potensi Harian (Senin - Minggu)]
         │
         ▼
[Tabel: potensi (dihubungkan dengan VA)]
         │
         ▼
[Engine Rekonsiliasi & Tagihan]
         │
         ├──► Target Harian = Potensi hari berjalan
         ├──► Target Bulanan = Sum(Potensi harian * frekuensi hari dalam bulan)
         ├──► Realisasi = Sum(transaksi_nominal pada periode terkait)
         └──► Deviasi / Kurang Setor = Target - Realisasi - Potongan Kendala
```

---

## 1.4 Primary Reconstruction Goals
1. **Pemisahan Arsitektur yang Jelas**: Memisahkan antarmuka Web Backoffice, Portal Registrasi Publik, Mobile API, dan Webhook Perbankan.
2. **Normalisasi Database Relasional Penuh**: Menghilangkan ketergantungan relasi berbasis string `va` tanpa foreign key, membersihkan kolom legacy, dan menyatukan tabel register dengan master table.
3. **Standarisasi Security & Webhook Handling**: Mengamankan endpoint Bank Jatim dengan verifikasi Signature Asymmetric (SHA256withRSA) / Symmetric (HMAC SHA512), memindahkan API credentials ke `.env`, dan mengimplementasikan RBAC (Role-Based Access Control) yang granular.
4. **Eliminasi Redundansi & Dead Code**: Menghapus duplikasi controller (`Titpar` vs `TitparController`, `Sisparma - Copy.php`, dll.) serta mengonsolidasi query raw ke layer Service yang teruji.
