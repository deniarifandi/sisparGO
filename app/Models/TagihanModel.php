<?php
// app/Models/TransaksiModel.php
namespace App\Models;

use CodeIgniter\Model;

class TagihanModel extends Model
{
    protected $table      = 'potensi';
    protected $primaryKey = 'potensi_id';
    protected $allowedFields = [
    'potensi_id',
    'potensi_va',
    'senin',
    'selasa',
    'rabu',
    'kamis',
    'jumat',
    'sabtu',
    'minggu',
    'mingguan',
    'bulanan',
    'tahunan',
    'srp'
];
}
