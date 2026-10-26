# 14. BUSINESS LOGIC EXTRACTION

Pemisahan aturan bisnis (*Pure Business Rules*) dari implementasi teknis untuk memudahkan recreate tanpa ketergantungan kode lama.

---

## 14.1 Domain 1: Pengelolaan Titik Parkir, Koordinator & Shift

### Entitas Terkait
`ParkingSpot`, `Attendant`, `AttendantAssignment`, `VirtualAccount`

### Business Rules:
1. **Aturan Penugasan Koordinator**:
   - Satu titik parkir (`ParkingSpot`) pada satu masa berlaku **hanya boleh memiliki 1 Koordinator utama**.
   - Koordinator bertindak sebagai penanggung jawab administratif dan rekening Virtual Account terdaftar atas nama koordinator tersebut.
2. **Aturan Anggota / Pembantu Jukir**:
   - Satu titik parkir dapat memiliki 0 atau banyak anggota jukir pembantu.
   - Anggota jukir bekerja berdasarkan penugasan shift (pagi/siang/malam) dan jam kerja tertentu.
3. **Pergantian Koordinator (Change Coordinator)**:
   - Jika koordinator diganti, status penugasan koordinator lama diubah menjadi `transferred` / `expired`, dan penugasan koordinator baru dibuat aktif.
   - Rekening VA harus disinkronkan: Berita transfer dan data nomor HP penerima notifikasi WhatsApp disesuaikan dengan koordinator baru.

---

## 14.2 Domain 2: Potensi, Target Retribusi & Rekonsiliasi Tagihan

### Entitas Terkait
`VirtualAccount`, `DailyPotential`, `Transaction`, `OperationalIssue`

### Formula & Aturan Perhitungan Bisnis:

1. **Penetapan Potensi 7 Hari**:
   - Setiap nomor Virtual Account memiliki penetapan nilai kewajiban setoran harian untuk masing-masing hari:
     $$\{P_{\text{senin}}, P_{\text{selasa}}, P_{\text{rabu}}, P_{\text{kamis}}, P_{\text{jumat}}, P_{\text{sabtu}}, P_{\text{minggu}}\}$$
   - Contoh: Sebuah titik parkir di pusat perbelanjaan memiliki potensi Senin–Kamis = Rp 50.000, Jumat–Minggu = Rp 80.000.

2. **Kalkulasi Target Bulanan Aktual**:
   - Target bulanan tidak dihitung sekadar $\text{potensi harian} \times 30$, melainkan dihitung berdasarkan **kalender nyata** (jumlah hari Senin, Selasa, dst. pada bulan terkait):
     $$\text{Target Bulanan}(M, Y) = \sum_{d=1}^{\text{daysInMonth}(M,Y)} P_{\text{dayOfWeek}(d)}$$

3. **Realisasi Penerimaan Setoran**:
   - Total penerimaan aktual pada periode bulan $M$ dan tahun $Y$:
     $$\text{Realisasi}(M, Y) = \sum \text{Transaction.amount} \quad \text{dimana } \text{MONTH}(\text{time})=M \text{ dan } \text{YEAR}(\text{time})=Y$$

4. **Potongan Dispensasi / Kendala Disetujui**:
   - Jika jukir mengalami kendala di lapangan dan telah disetujui oleh admin dengan status `approved`:
     $$\text{Potongan}(M, Y) = \sum \text{OperationalIssue.deduction\_amount}$$

5. **Kekurangan Setoran (Deviasi Tagihan)**:
   $$\text{Kurang Setor} = \text{Target Bulanan} - \text{Realisasi} - \text{Potongan Kendala}$$
   - Jika $\text{Kurang Setor} \le 0$, maka status setoran bulan tersebut dinyatakan **LUNAS / MEMENUHI TARGET**.
   - Jika $\text{Kurang Setor} > 0$, maka sisa selisih dicatat sebagai **Tunggakan / Piutang Retribusi**.

---

## 14.3 Domain 3: Integrasi Perbankan & Webhook Setoran Bank Jatim

### Entitas Terkait
`VirtualAccount`, `Transaction`, `RawTransactionLog`, `WhatsAppNotification`

### Workflow & Business Rules:
1. **Format Nomor Virtual Account**:
   - Panjang 16 digit numerik.
   - Prefix `15088` (atau `10325`) + nomor pelanggan terdaftar.
2. **Idempotensi Transaksi**:
   - Bank dapat mengirimkan notifikasi webhook secara berulang (retry mechanism) jika terjadi latency jaringan.
   - Sistem **wajib mengecek keunikan `coreReferenceNo`**. Jika `coreReferenceNo` sudah pernah tercatat, sistem tidak boleh menambahkan nominal setoran kedua kalinya (simpan ke log duplikasi `raw_transaction_logs` dan tetap balas response sukses `2000000` ke Bank).
3. **Trigger WhatsApp Struk Setoran**:
   - Setiap transaksi baru yang berhasil disimpan, sistem secara asinkron memicu pengiriman pesan WhatsApp ke nomor HP koordinator (`va_owner.va_owner_hp`) berisi bukti tanda terima setoran retribusi parkir resmi DISHUB Kota Malang.

---

## 14.4 Domain 4: Distribusi Karcis / Tiket Fisik

### Business Rules:
1. **Penomoran Seri Berurutan**:
   - Distribusi karcis parkir fisik dicatat berdasarkan nomor seri awal dan akhir per jenis kendaraan (Motor, Mobil, Truk).
   - Jumlah lembar tiket = $(\text{Nomor Akhir} - \text{Nomor Awal} + 1)$.
2. **Serah Terima Fisik**:
   - Petugas bagian karcis menyerahkan bendel tiket kepada koordinator parkir, dan status berubah menjadi `handed_over` hingga dikonfirmasi diterima oleh jukir (`received`).
