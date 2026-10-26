<?php

namespace App\Models;

use CodeIgniter\Model;

class DishubTitparModel extends Model
{
    protected $table = 'dishub_titpar';
    protected $primaryKey = 'titpar_id';
    protected $allowedFields = ['titpar_nama']; // adjust if more fields needed
}