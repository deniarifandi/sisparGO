<?php

namespace App\Controllers;

use App\Libraries\DataTable;
use App\Libraries\Jatim;


class JatimController extends BaseController
{

    private $db = null;
    private $jatim = null;


    function __construct(){
      
      $this->jatim = new Jatim();
        
    }

    public function get_access_token(){
        $jatimresult = $this->jatim->get_access_token();

        $result = json_decode($jatimresult, true);
        // $result['responseMessage'] = "Success";
        print_r($result);
        exit();
        if ($result['responseMessage'] == "Success") {
            
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->to('/va-owner');
        } else {
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->back()->withInput();
        }
    }


    public function signature_access_token(){
        $jatimresult = $this->jatim->signature_access_token();

        // $result = json_decode($jatimresult, true);
        // $result['responseMessage'] = "Success";
        echo $jatimresult;
        exit();
        if ($result['responseMessage'] == "Success") {
            
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->to('/va-owner');
        } else {
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->back()->withInput();
        }
    }
    
  }
