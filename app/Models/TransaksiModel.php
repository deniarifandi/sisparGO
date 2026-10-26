<?php
// app/Models/TransaksiModel.php
namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table      = 'transaksi';
    protected $primaryKey = 'transaksi_id';
   protected $allowedFields = [
    'transaksi_va',
    'transaksi_nominal',
    'transaksi_tanggal',
    'transaksi_jenis' // if you have it
];
}
