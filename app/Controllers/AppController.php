<?php 

namespace App\Controllers;

use App\Libraries\DataTable;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AppController extends BaseController
{
    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
  }

  public function index(){
   echo "index page";
}





}
