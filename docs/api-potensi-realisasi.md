# Detail Potensi Bulanan dan Realisasi

## Rekap tahunan

`GET /api/transaksi/va/{va}/potensi-realisasi/tahunan?tahun=2026`

Respons `data.tahunan` berisi satu baris sesuai tahun yang dipilih:
`tahun`, `bulan_sampai`, `potensi`, `pengurangan_kendala`,
`potensi_setelah_kendala`, `realisasi`, `selisih`, dan `persentase`.
Nominal berupa angka rupiah; persentase berupa angka persen (contoh `91.09`).
Persentase nol bila target setelah kendala nol.
Tahun berjalan diakumulasi Januari sampai bulan berjalan; tahun lain Januari
sampai Desember, mengikuti perhitungan halaman web. `bulan_sampai` menjelaskan
batas akumulasi tersebut. Selisih = realisasi - potensi setelah kendala.
Validasi tahun dan status error sama dengan endpoint bulanan.

Endpoint bulanan di bawah juga menyertakan `data.tahunan`, sehingga aplikasi
dapat menampilkan kedua tabel dengan satu permintaan.

`GET /api/transaksi/va/{va}/potensi-realisasi?tahun=2026`

Base URL lokal: `http://localhost/dishub2026/public`.
Parameter `tahun` opsional, default tahun berjalan; menerima 1900–9999.
VA dikirim dan dikembalikan sebagai string. Endpoint mengikuti API mobile
yang ada tanpa login sesi web atau header Authorization.

Respons HTTP 200:

```json
{
  "status": "success",
  "data": {
    "va": "1032500006010301",
    "tahun": 2026,
    "bulanan": [
      {
        "tahun": 2026,
        "bulan": 1,
        "nama_bulan": "Januari",
        "potensi": 10850000,
        "pengurangan_kendala": 0,
        "potensi_setelah_kendala": 10850000,
        "realisasi": 10500000,
        "selisih": -350000,
        "snapshot_mulai": "2026-01-01"
      }
    ]
  }
}
```

Contoh dipersingkat menjadi satu bulan; respons sebenarnya berisi 12 bulan
berurutan Januari–Desember. Nominal berupa angka rupiah tanpa format tampilan.
`selisih = realisasi - potensi_setelah_kendala`; nilai negatif berarti kurang.
`snapshot_mulai` berupa string tanggal, dapat berisi beberapa tanggal dipisahkan
koma atau string kosong bila tidak ada snapshot. Perhitungan memakai model
halaman web, termasuk aturan transaksi dan kendala yang sama.

HTTP 400 untuk tahun tidak valid; HTTP 404 untuk VA tidak ditemukan:
`{"status":"error","message":"..."}`.

Contoh Flutter dengan package `http`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<List<Map<String, dynamic>>> fetchPotensiBulanan(
  String baseUrl, String va, int tahun,
) async {
  final uri = Uri.parse(
    '$baseUrl/api/transaksi/va/$va/potensi-realisasi',
  ).replace(queryParameters: {'tahun': '$tahun'});
  final response = await http.get(uri);
  final body = jsonDecode(response.body) as Map<String, dynamic>;
  if (response.statusCode != 200) {
    throw Exception(body['message'] ?? 'Gagal mengambil data');
  }
  return (body['data']['bulanan'] as List)
      .map((row) => Map<String, dynamic>.from(row as Map))
      .toList();
}
```

Gunakan `(row['potensi'] as num).toDouble()` untuk membaca nominal di Dart.
Untuk Android Emulator standar, gunakan base URL
`http://10.0.2.2/dishub2026/public`; perangkat fisik memakai IP LAN komputer.
Gunakan URL HTTPS server saat aplikasi dipublikasikan.
