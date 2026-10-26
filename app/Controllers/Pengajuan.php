<?php 

namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\DishubAnggotaModel;
use App\Libraries\DataTable;
use App\Libraries\Whatsapp;
use DB;


class Pengajuan extends BaseController
{

    public function index(){
        echo "halaman Pengajuan";
    }

}
