# API pengajuan pendaftaran anggota

POST `/api/pendaftaran-anggota`, multipart/form-data, publik untuk calon anggota.

Tabel: `pengajuan_pendaftaran_anggota`. Status awal `menunggu`. API ini tidak membuat akun login atau memasukkan data ke `dishub_anggota`. Persetujuan admin membuat anggota dan akun login sebagaimana dijelaskan di bawah.

Wajib: `request_id` (20–64 karakter alfanumerik/tanda hubung), `nama_lengkap` (maks 150), `nik` (16 digit).
Opsional: `nomor_kk` (16 digit), `no_handphone`, `tanggal_lahir` (YYYY-MM-DD), `tempat_lahir`, `jenis_kelamin` (Laki-laki/Perempuan), `alamat_lengkap`, `provinsi`, `kabupaten_kota`, `kecamatan_id`, `kelurahan_id`, `pekerjaan`, `pendidikan_terakhir`, `agama`.
Wilayah yang diisi wajib memiliki induk yang sesuai di tabel `kelurahan`.

Berkas opsional: `pas_foto`, `dokumen_ktp`, `dokumen_kk`; gambar JPEG/PNG/WEBP, maks 2 MiB per berkas. Nama acak disimpan dalam tabel; berkas disimpan privat di `writable/uploads/pendaftaran_anggota/`. Jangan mengekspos folder ini melalui URL publik.

Respons 201: `{"message":"Pengajuan pendaftaran berhasil diterima.","data":{"nomor_pengajuan":"REG-...","status":"menunggu"}}`.
Pengiriman ulang dengan request_id dan data sama menghasilkan 200 dengan nomor yang sama. Data berbeda untuk request_id sama menghasilkan 409. Satu NIK hanya boleh memiliki satu pengajuan; pengajuan ulang ditolak 409 dan diarahkan menghubungi petugas. Aturan ini juga berlaku bila status diubah kelak.
422: validasi gagal. 500: penyimpanan gagal. Tidak ada endpoint publik untuk membaca data pribadi atau dokumen.

## Penerapan

Jalankan `php spark migrate` sebelum memakai endpoint. Migrasi lokal sudah diterapkan.
Deploy controller, Routes.php, dan migrasi ke server produksi, lalu jalankan migrasi di sana.
Flutter mengikuti WILAYAH_API_BASE_URL; endpoint bisa diganti melalui PENDAFTARAN_API_URL.
Contoh desktop lokal: `flutter run --dart-define=WILAYAH_API_BASE_URL=http://localhost/dishub2026/public/api/wilayah`.
Pada perangkat Android fisik gunakan alamat server yang dapat dijangkau perangkat (localhost menunjuk perangkat itu sendiri).

Nomor pengajuan baru memakai 8 karakter acak dalam format `XXXX-XXXX` (tanpa I, O, 0, 1). Nomor lama tetap berlaku dan respons pengiriman ulang tetap mengembalikan nomor awal. Kolom nomor_pengajuan tetap memiliki unique index.


## Perbaikan dan keputusan

- Admin memilih Setujui, Minta Perbaikan (`perlu_perbaikan`), atau Tolak (`ditolak`, final). Catatan wajib untuk dua pilihan terakhir.
- Semua aksi dicatat di `pengajuan_pendaftaran_riwayat`, termasuk snapshot data sebelum/sesudah, aktor, waktu, dan request ID pengiriman ulang.
- `POST /api/pendaftaran-anggota/status` menerima NIK dan nomor_pengajuan; `bisa_diperbaiki` hanya true untuk perlu_perbaikan. Alasan tersedia di `alasan_penolakan` untuk penolakan maupun permintaan perbaikan.
- `POST /api/pendaftaran-anggota/perbaikan/data` menerima NIK dan nomor_pengajuan, mengembalikan field form hanya jika status perlu_perbaikan. Dokumen ditunjukkan dengan boolean *_tersedia; nama file privat tidak dibuka.
- `POST /api/pendaftaran-anggota/perbaikan` menerima multipart dengan semua field form, NIK, nomor_pengajuan, request_id baru, dan foto pengganti opsional. NIK dan kode harus cocok dengan pengajuan. Status kembali menunggu; kode dan tanggal pengajuan awal dipertahankan. Berkas lama dipertahankan bila tidak diganti. Berkas yang diganti tetap disimpan privat sebagai referensi snapshot riwayat.
- Request ID sama dengan payload sama menghasilkan respons ulang tanpa perubahan status/riwayat tambahan. Gunakan ID baru untuk setiap versi perbaikan.
- Pengajuan berstatus menunggu, disetujui, atau ditolak tidak dapat diperbaiki. Penolakan lama tetap final; migrasi tidak mengubah keputusan lama.
- Jalankan migrasi 000007 pada server sebelum deploy alur ini. Test lokal: `php tests/manual/registration_revision.php` (data sintetis di dalam transaksi rollback).


## Persetujuan dan akun login

Migrasi `000008_LinkApprovedRegistrationAccounts` menambahkan relasi pengajuan–anggota, unique index asal pengajuan, flag `must_change_password`, indeks NIK, serta memperbesar kolom nama/alamat anggota. Migrasi lokal sudah diterapkan; jalankan `php spark migrate` saat penerapan server.

`POST /admin/pendaftaran-anggota/approve/{id}` tetap memerlukan login admin dan CSRF. Respons JSON 201 berisi `credentials.user_id` dan `credentials.password` hanya setelah transaksi berhasil. Password berupa dua kata Indonesia berbeda plus empat angka, dipilih dengan CSPRNG. Password plaintext tidak disimpan di database, session, atau riwayat. Admin melihat popup sekali dan dapat menyalinnya; setelah ditutup password tidak bisa ditampilkan kembali.

Persetujuan menyalin data ke `dishub_anggota` (ID otomatis menjadi ID login), memakai kode pengajuan sebagai `anggota_noreg`, mengubah status pengajuan, menghubungkan kedua record, dan menulis riwayat dalam satu transaksi. Klik ulang ditolak tanpa membuat akun baru. Data pengajuan tetap dipertahankan. Pas foto disalin ke folder foto anggota. Referensi KTP/KK disimpan di anggota dengan awalan `pendaftaran/{id}/`; file tetap privat dan halaman edit anggota memakai endpoint dokumen admin. Tidak dibuat URL publik untuk dokumen identitas.

Login aplikasi menggunakan tabel anggota, bukan tabel users admin. `POST /api/loginapp` menghasilkan 403 dengan `code: PASSWORD_CHANGE_REQUIRED` untuk password sementara yang benar. Belum ada respons user yang dapat dipakai masuk dashboard. `POST /api/loginapp/ganti-password-awal` menerima JSON `username`, `temporary_password`, dan `new_password`. Endpoint memverifikasi password sementara, mengunci row, mengubah hash, dan melepas flag. Password baru minimal 10 karakter dan maksimal 72 byte, berbeda dari password sementara. Login normal diperlukan setelah perubahan. Rate limit login lama berlaku juga di endpoint penggantian awal.

Aplikasi Flutter menggunakan `AUTH_API_BASE_URL` jika diatur; default mengikuti akar `WILAYAH_API_BASE_URL`. Deploy backend/migrasi dan build aplikasi baru bersama-sama. Akun lama tidak diwajibkan ganti password. Persetujuan historis tidak otomatis dibuatkan akun oleh migrasi.

Pengujian lokal: `php tests/manual/registration_approval.php`. Semua data sintetis di-rollback dan file fixture dibersihkan.
