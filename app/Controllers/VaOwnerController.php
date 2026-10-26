<?php 

namespace App\Controllers;

use App\Models\VaOwnerModel;
use App\Models\DishubAnggotaModel;
use App\Libraries\DataTable;
use App\Libraries\Jatim;
use App\Controllers\VaController;
use DB;

class VaOwnerController extends BaseController
{
    protected $vaModel;
    private $jatim;

    public function __construct()
    {
        $this->vaModel = new VaOwnerModel(); 
        $this->jatim = new Jatim();
    }

    public function index()
    {

        return view('va_owner/index');
    }

    public function getTitparWithAnggota($anggotaId)
{
    $model = new \App\Models\DishubAnggotaModel();

    $data = $model->getTitparWithAnggota((int) $anggotaId);

    return $this->response->setJSON([
        'status' => true,
        'data'   => $data
    ]);
}

public function getOtherTitpar($anggotaId, $currentTitparId)
{
    $model = new \App\Models\DishubAnggotaModel();

    $data = $model->getOtherTitpar(
        (int) $anggotaId,
        (int) $currentTitparId
    );

    return $this->response->setJSON([
        'status' => true,
        'data'   => $data
    ]);
}

    /////// updating berita

    public function updatingBerita($offset){
        $db = db_connect();

        // Query builder: get all rows matching this email
        $builder = $db->table('va_owner')
        ->select('va_owner_va, va_owner_hp, va_owner_email, va_owner_berita_3, va_owner_nama')
           ->where("TRIM(va_owner_berita_3) != 'DISHUB KOTA MALANG'")
            ->orWhere('va_owner_berita_3 IS NULL') // Optional: include nulls
        // ->limit(1)
        ->offset($offset);
;
        $result = $builder->get()->getResult();


        // Send data to view
        return view('updateberita', ['result' => $result,'offset' => $offset]);

    }

    public function dump_inquiry($function_va){
        $db = db_connect();  
        $jatimresult = $this->jatim->inquiryVa(
            $function_va
        );
        
        $data = [
            'inquiry'     => $jatimresult
        ];

        $builder = $db->table('dump_inquiry');
        $insert = $builder->insert($data);
        if ($insert) {
            return $jatimresult;
        } else {
                // Jika gagal
            echo "Insert failed!";
            exit();
        }
    }

    public function getVaOwnerDetail($function_va){

        $db = db_connect();  

        $builder = $db->table('va_owner')->select('va_owner_id,anggota_nama, anggota_id, va_owner.va_owner_va, va_owner_hp, titpar_namatempat, titpar_lokasi, titpar_retritypeid, va_owner_titpargrupid, va_owner_nama')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
        ->join('dishub_titpar','dishub_titpar.titpar_id =dishub_titpargrup.titpargrup_titparid','left')
        ->where('va_owner_va',$function_va);
        
        return $builder->get()->getResult();

    }

    public function getPotensi($function_va){
        $db = db_connect();  
        $resultpotensi = $db->table('potensi')
            ->where('potensi_va', $function_va)
            ->get()
            ->getResult();

            $total_tahunan = 0;
                     // Assuming $resultpotensi is not empty
            if (!empty($resultpotensi)) {
                $potensi = $resultpotensi[0]; // take the first result



                if (!empty($potensi->senin) || !empty($potensi->selasa) || !empty($potensi->rabu) ||
                    !empty($potensi->kamis) || !empty($potensi->jumat) || !empty($potensi->sabtu) || !empty($potensi->minggu)) {
                // Daily values exist — sum all days, multiply by 52 weeks
                    $harian_total = $potensi->senin + $potensi->selasa + $potensi->rabu +
                $potensi->kamis + $potensi->jumat + $potensi->sabtu + $potensi->minggu;
                $total_tahunan = $harian_total * 52;
            } elseif (!empty($potensi->mingguan)) {
                // Weekly value exists
                $total_tahunan = $potensi->mingguan * 52;
            } elseif (!empty($potensi->bulanan)) {
                // Monthly value exists
                $total_tahunan = $potensi->bulanan * 12;
            } elseif (!empty($potensi->tahunan)) {
                // Yearly value already exists
                $total_tahunan = $potensi->tahunan;
            }

        } else {
            echo "Data potensi tidak ditemukan.";
        }

        if ($total_tahunan == 0) {
            $total_tahunan = 1000000000;
        }

        return $total_tahunan;
    }

    public function getRetriType($function_va)
    {
        // Get the first 5 characters
        $prefix = substr($function_va, 0, 5);

        if ($prefix === '15088') {
            $retritype = 'Tepi Jalan Umum (TJU)';
        } elseif ($prefix === '10325') {
            $retritype = 'Tempat Khusus Parkir (TKP)';
        } else {
            $retritype = 'Unconfirmed';
        }

        return $retritype;
    }

    public function sendUpdate($function_va,$offset){
        $db = db_connect();  
        $model = new VaOwnerModel();

        $inquiry = $this->dump_inquiry($function_va);

        print_r($inquiry);
        $data = json_decode($inquiry, true);

        $result = $this->getVaOwnerDetail($function_va);

        if (empty($result)) {
            log_message('error', "VA owner detail tidak ditemukan untuk VA: {$function_va}");
            $offset++;
            return redirect()->to(base_url().'updatingberita/'.$offset);
        }

        $potensi = $this->getPotensi($function_va);

        $retritype = $this->getRetriType($function_va);

            // Extract top-level fields
        $responseCode = $data['responseCode'];
        $responseMessage = $data['responseMessage'];

            // Extract virtualAccountData
        if (!isset($data['virtualAccountData'])) {
            $offset++;
            return redirect()->to(base_url().'updatingberita/'.$offset);
        }
        $va = $data['virtualAccountData'];
            $partnerServiceId = trim($va['partnerServiceId']); // trimmed to remove extra space
            $customerNo = $va['customerNo'];
            $virtualAccountNo = $va['virtualAccountNo'];
            $virtualAccountName = $va['virtualAccountName'];
            $virtualAccountEmail = $va['virtualAccountEmail'];
            $virtualAccountPhone = $va['virtualAccountPhone'];
            $trxId = $va['trxId'];
            $virtualAccountTrxType = $va['virtualAccountTrxType'];
            $expiredDate = $va['expiredDate'];

            // Extract totalAmount
            $totalAmountValue = $va['totalAmount']['value'];
            $totalAmountCurrency = $va['totalAmount']['currency'];

            // Extract feeAmount
            $feeAmountValue = $va['feeAmount']['value'];
            $feeAmountCurrency = $va['feeAmount']['currency'];

            // Extract additionalInfo
            $sign = $va['additionalInfo']['sign'];
            $paymentFlagStatus = $va['additionalInfo']['paymentFlagStatus'];
            $reffInstitusi = $va['additionalInfo']['reffInstitusi'];
            $otherInfo1 = $va['additionalInfo']['otherInfo1'];
            $otherInfo2 = $va['additionalInfo']['otherInfo2'];
            $otherInfo3 = $va['additionalInfo']['otherInfo3'];

        //print_r($result);
        // print_r($result[0]->titpar_namatempat);
        $berita5 = "DISHUB KOTA MALANG";
        $hp = $result[0]->va_owner_hp ?? '000000000';
        //$titpar = $result[0]->titpar_namatempat ?? $otherInfo1;

         $cleanNama = preg_replace('/[^A-Za-z\s]/', '', $result[0]->titpar_namatempat);
         if ($cleanNama == "") {
            $cleanNama = $otherInfo3;
        }
        echo "cleannama:".$cleanNama;

        


        $data = [
            'va_owner_id'        => $result[0]->va_owner_id,
            'va_owner_titpargrupid' => $result[0]->va_owner_titpargrupid,
            'va_owner_va'        => $result[0]->va_owner_va,
            'va_owner_nama'      => $result[0]->va_owner_nama,
            
            'va_owner_berita_1'  => $cleanNama,
            'va_owner_berita_2'  => $retritype,
            'va_owner_berita_3'  => "DISHUB KOTA MALANG",
            'va_owner_hp'        => $hp,
            'va_owner_expired'   => $expiredDate,
            'va_owner_email'    =>  $retritype,
            'va_owner_tagihan'  =>  $potensi
        ];    

       
        $jatimresult = $this->jatim->updateVA(
            $result[0]->anggota_id,
            $virtualAccountNo,
            $virtualAccountName,
            $expiredDate,
            $berita5,
            $hp,
            $total_tahunan_str = strval($potensi),
            mb_substr($cleanNama, 0, 24),
            $retritype
        );

        $result2 = json_decode($jatimresult, true);

        echo "<br><br>";
        echo json_encode($data);
        // $result['responseMessage'] = "Success";
        echo "<br><br>";
        echo $jatimresult;

        if ($result2['responseMessage'] == "Success") {
            $update = $model->update($result[0]->va_owner_id, $data);

            if ($update) {
                echo "<br><br>Data updated successfully!<br>";

                    // Get the updated data from database
                $updatedData = $model->find($result[0]->va_owner_id);
                    //print_r($updatedData);
            } else {
                echo "Update failed!";
                $offset++;
            }
            // session()->setFlashdata('message', $result['responseMessage']);
            // return redirect()->to('/va-owner');
        } else {
            $offset++;
            // session()->setFlashdata('message', $result['responseMessage']);
            // return redirect()->back()->withInput();
        }
        // $result['responseMessage'] = "Success";
        echo '<a href="' . base_url('updatingberita/' . $offset) . '" class="btn btn-primary">
        Update Berita
      </a>';
       // return redirect()->to(base_url().'updatingberita/'.$offset);
        
    }

    // end updating berita

    public function getaccesstoken(){
        $this->jatim->get_access_token();
    }

    public function inquiryVa(){
        // $jatimresult = $this->jatim->createVA(
        //     $dataanggota[0]->anggota_id,
        //     $data['va_owner_va'],
        //     $dataanggota[0]->anggota_nama,
        //     $tanggal,
        //     $this->request->getPost('va_owner_email'),
        //     $data['va_owner_hp']
        // );

        $result = json_decode($jatimresult,true);
    }   

  public function create()
{
    $db = \Config\Database::connect();

    $register_id = $this->request->getGet('register_id');

    $selected_titpargrup_id = null;
    $register_hp = null;

    if ($register_id) {
        $reg = $db->table('register') // sesuaikan nama tabel
            ->select('register_titpargrupid,hp')
            ->where('id', $register_id)
            ->get()
            ->getRowArray();

        if ($reg) {
            $selected_titpargrup_id = $reg['register_titpargrupid'];
            $register_hp = $reg['hp'];
        }
    }

    $data['register_id'] = $register_id;
    $data['selected_titpargrup_id'] = $selected_titpargrup_id;
    $data['va_owner_hp'] = $register_hp;

    // tetap pakai builder juga untuk anggota
    $data['anggota'] = $db->table('dishub_anggota')
        ->select('anggota_nama, anggota_id, titpar_namatempat, titpar_id, titpargrup_id')
        ->where('anggota_status !=', 1)
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
        ->get()
        ->getResultArray();

    return view('va_owner/form', $data);
}

    public function store()
    {
        $db = db_connect();
        $model = new VaOwnerModel();
        $tanggal = $this->request->getPost('va_owner_expired'); // example: 2025-12-31
        $register_id = $this->request->getPost('register_id');

        $dataanggota = $db->table('dishub_anggota')
        ->select('dishub_anggota.anggota_id, dishub_anggota.anggota_nama, titpargrup_id, titpar_namatempat, titpar_lokasi')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id', 'left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id')
        ->where('dishub_titpargrup.titpargrup_id', $this->request->getPost('titpargrup_id'))
        ->get()
        ->getResult();
        // echo $data[0]->anggota_id;
       // print_r($data);

        if (empty($dataanggota)) {
            session()->setFlashdata('message', 'Data anggota/titpar tidak ditemukan untuk titpargrup ini.');
            return redirect()->back()->withInput();
        }

        // exit();
        if (!empty($tanggal)) {
            $dt = new \DateTime($tanggal, new \DateTimeZone('Asia/Jakarta'));
            $tanggal = $dt->format('Y-m-d\T00:00:00P'); // 2025-12-31T00:00:00+07:00
        } else {
            // if empty, default 1 year from today
            $dt = new \DateTime('+1 year', new \DateTimeZone('Asia/Jakarta'));
            $tanggal = $dt->format('Y-m-d\T00:00:00P');
        }
        $data = [
            'va_owner_id'        => $this->request->getPost('va_owner_id'),
            // 'va_owner_anggotaid' => $anggotaId,
            // 'va_owner_titparid'  => $titpar_id,
            'va_owner_titpargrupid' => $this->request->getPost('titpargrup_id'),
            'va_owner_va'        => $this->request->getPost('va_owner_va'),
            'va_owner_nama'      => $dataanggota[0]->anggota_nama,
            'va_owner_berita_1'  => $dataanggota[0]->titpar_namatempat,
            'va_owner_berita_2'  => $dataanggota[0]->titpar_lokasi,
            'va_owner_berita_3'  => $this->request->getPost('va_owner_berita_3'),
            'va_owner_hp'        => $this->request->getPost('va_owner_hp'),
            'va_owner_expired'   => $tanggal,
            'va_owner_email'    => "arifandi.malang@gmail.com",
            'va_owner_tagihan'  => $this->request->getPost('va_owner_tagihan')
        ];

        //HERE
        $jatimresult = $this->jatim->createVA(
            $dataanggota[0]->anggota_id,
            $data['va_owner_va'],
            $dataanggota[0]->anggota_nama,
            $tanggal,
            $data['va_owner_email'],
            $data['va_owner_hp'],
            $data['va_owner_tagihan']
        );

        $result = json_decode($jatimresult,true);
        // $result['responseMessage'] = "Success";
        
        if ($result['responseMessage'] == "Success") {
            $model->save($data);
            session()->setFlashdata('message', $result['responseMessage']);
            
            return redirect()->to('/va-owner');
        } else {
            if($result['responseMessage'] === "Invalid Bill/Virtual Account. Virtual Account Number Already Registered / Innactive"){
                $existing = $model->where('va_owner_va', $data['va_owner_va'])->first();
                if ($existing) {
                    session()->setFlashdata('message', "VA existed on both Server");
                } else {
                    // New record → Insert
                    $model->save($data);

                    if (!empty($register_id)) {
                        $db->table('register')
                            ->where('id', $register_id)
                            ->update([
                                'register_va' => $this->request->getPost('va_owner_va')
                            ]);
                         session()->setFlashdata('message', "VA already created, Insert overrided");
                         return redirect()->to('/perijinan');
                    }
                    session()->setFlashdata('message', "VA already created, Insert overrided");
                }

                return redirect()->to('/va-owner');
            }
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->back()
            ->withInput();
        }

        //return redirect()->to('/va-owner');
    }

    public function edit($id)
    {
      $model = new VaOwnerModel();
      $anggotaModel = new DishubAnggotaModel();

      $data['va_owner'] = $model->find($id);

    // Convert expired date for single record (edit)
      if (!empty($data['va_owner']['va_owner_expired'])) {
        try {
            $dt = new \DateTime($data['va_owner']['va_owner_expired']);
            $data['va_owner']['va_owner_expired'] = $dt->format('Y-m-d');
        } catch (\Exception $e) {
            $data['va_owner']['va_owner_expired'] = date('Y-m-d', strtotime('+1 year'));
        }
    } else {
        $data['va_owner']['va_owner_expired'] = date('Y-m-d', strtotime('+1 year'));
    }

    $data['anggota'] = $anggotaModel
    ->select('anggota_nama, anggota_id, titpar_namatempat, titpar_id, titpargrup_id, va_owner_expired') // tambahkan kolom
    ->where('anggota_status !=', 1)
    ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left')
    ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
    ->join('va_owner','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left') // kalau kolomnya dari tabel ini
    ->findAll();


        //inquiry
    $jatimresult = $this->jatim->inquiryVa(
        $data['va_owner']['va_owner_va']
    );
        // echo "show va here".$data['va_owner']['va_owner_va'];
        // $result = json_encode($jatimresult, true);
        // echo $jatimresult;
        // exit();
    $data['jatim'] = json_decode($jatimresult,true);;
        //inquiry

    //print_r($data['jatim']);
    //exit();

    // Convert expired date for anggota list
    foreach ($data['anggota'] as &$row) {
        if (!empty($row['va_owner_expired'])) {
            try {
                $dt = new \DateTime($row['va_owner_expired']);
                $row['va_owner_expired'] = $dt->format('Y-m-d');
            } catch (\Exception $e) {
                $row['va_owner_expired'] = date('Y-m-d', strtotime('+1 year'));
            }
        } else {
            $row['va_owner_expired'] = date('Y-m-d', strtotime('+1 year'));
        }
    }
    unset($row);

    return view('va_owner/form', $data);

}

public function update($id)
{   
    echo "this update";

    $db = db_connect();
    $model = new VaOwnerModel();
    $postData = $this->request->getPost();

    $dataanggota = $db->table('dishub_anggota')
    ->select('dishub_anggota.anggota_id, dishub_anggota.anggota_nama, titpargrup_id, titpar_namatempat, titpar_lokasi, titpar_retritypeid')
    ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id', 'left')
    ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id')
    ->where('dishub_titpargrup.titpargrup_id', $this->request->getPost('titpargrup_id'))
    ->get()
    ->getResult();

    if (empty($dataanggota)) {
        session()->setFlashdata('message', 'Data anggota/titpar tidak ditemukan untuk titpargrup ini.');
        return redirect()->back()->withInput();
    }

        // Handle tanggal
    if (!empty($postData['va_owner_expired'])) {
        $dt = new \DateTime($postData['va_owner_expired'], new \DateTimeZone('Asia/Jakarta'));
    } else {
        $dt = new \DateTime('+1 year', new \DateTimeZone('Asia/Jakarta'));
    }
        $formattedTanggal = $dt->format('Y-m-d\T00:00:00P'); // 2025-12-31T00:00:00+07:00
        
        $data = [
            'va_owner_id'        => $this->request->getPost('va_owner_id'),
            'va_owner_titpargrupid' => $this->request->getPost('titpargrup_id'),
            'va_owner_va'        => $this->request->getPost('va_owner_va'),
            'va_owner_nama'      => $dataanggota[0]->anggota_nama,
            
            'va_owner_berita_1'  => $dataanggota[0]->titpar_namatempat,
            'va_owner_berita_2'  => $dataanggota[0]->titpar_lokasi,
            'va_owner_berita_3'  => $this->request->getPost('va_owner_berita_3'),
            'va_owner_hp'        => $this->request->getPost('va_owner_hp'),
            'va_owner_expired'   => $formattedTanggal,
            'va_owner_email'    => $this->request->getPost('va_owner_email'),
            'va_owner_tagihan'  => $this->request->getPost('va_owner_tagihan')
        ];

        // echo $dataanggota[0]->anggota_id . "<br>";
        // echo $this->request->getPost('va_owner_va') . "<br>";
        // echo $dataanggota[0]->anggota_nama . "<br>";
        // echo $formattedTanggal . "<br>";
        // echo $this->request->getPost('va_owner_email') . "<br>";
        // echo $data['va_owner_hp'] . "<br>";
        // echo $data['va_owner_tagihan'] . "<br>";
        // echo $dataanggota[0]->titpar_namatempat . "<br>";
        // echo $dataanggota[0]->titpar_retritypeid . "<br>";
        // exit();

        // Update via external API
        $jatimresult = $this->jatim->updateVA(
            $dataanggota[0]->anggota_id,
            $this->request->getPost('va_owner_va') ,
            $dataanggota[0]->anggota_nama,
            $formattedTanggal,
            $this->request->getPost('va_owner_email'),
            $data['va_owner_hp'],
            $data['va_owner_tagihan'],
            $dataanggota[0]->titpar_namatempat,
            $dataanggota[0]->titpar_retritypeid
        );

        $result = json_decode($jatimresult, true);
        // $result['responseMessage'] = "Success";

        if ($result['responseMessage'] == "Success") {
            $model->update($id, $data);
            session()->setFlashdata('message', $result['responseMessage']);
            return redirect()->to('/va-owner');
        } else {
            session()->setFlashdata('message', $result['responseMessage'].json_encode($data));
            return redirect()->back()->withInput();
        }


    }

    public function delete($id)
{
    $db = db_connect();
    $record = $this->vaModel->find($id);

    if (!$record) {
        session()->setFlashdata('message', 'Data tidak ditemukan.');
        return redirect()->to('/va-owner');
    }

    // TODO: aktifkan kembali saat endpoint deactivate Jatim sudah stabil
    // $jatimresult = $this->jatim->deleteVA($record['va_owner_id'], $record['va_owner_va']);
    // $result = json_decode($jatimresult, true);
    // $message = $result['responseMessage'] ?? 'Unknown response';

    $message = "Internal VA Deactivated"; // sementara: soft-delete lokal saja

    $builder = $db->table('va_owner');
    $builder->where('va_owner_id', $id);
    $builder->update(['deleted_at' => date('Y-m-d H:i:s')]);

    $rows = $db->affectedRows();
    $extra = $rows > 0 ? "& Internal VA soft deleted" : "& Internal delete failed";

    session()->setFlashdata('message', $message . " " . $extra);
    return redirect()->to('/va-owner');
}

    public function deleteOLD($id)
    {
        $db = db_connect();
        // $record = $this->vaModel->find($id);

        // if (! $record) {
        //     session()->setFlashdata('message', 'Data tidak ditemukan atau gagal dihapus.');
        //     return redirect()->to('/va-owner');
        // }

        // $recordId  = $record['va_owner_id'];  // primary key
        // $vaOwnerVa = $record['va_owner_va'];  // VA value

        //$jatimresult = $this->jatim->deleteVA($recordId, $vaOwnerVa);
        //$result      = json_decode($jatimresult, true);
        //$message     = $result['responseMessage'] ?? 'Unknown response';

        $message = "Internal VA Deactivated";
        if ($message === "Internal VA Deactivated") {
            session()->setFlashdata('message', $message);
             // Use Query Builder to only update the deleted_at column
            $builder = $db->table('va_owner'); // change 'va' to your actual table name
            $builder->where('va_owner_id', $id);
            $builder->update(['deleted_at' => date('Y-m-d H:i:s')]);

            $rows = $db->affectedRows();

            $extra = $rows > 0 ? "& Internal VA soft deleted" : "& Internal delete failed";
            return redirect()->to('/va-owner');
        }


        if ($message === "Invalid . Virtual Account" || $message === "Invalid Bill/Virtual Account. Virtual Account Number Unknown") {
            $this->vaModel->delete($id);
            $rows = $this->vaModel->db->affectedRows();

            $extra = $rows > 0 ? "Internal va deleted" : "Internal Delete Failed";
            session()->setFlashdata('message', $message . ", " . $extra);

            return redirect()->back()->withInput();
        }


        session()->setFlashdata('message', $message);
        return redirect()->back()->withInput();
    }

    public function undelete($id)
    {
        $db = db_connect();

        $message = "Internal VA Reactivated";
        if ($message === "Internal VA Reactivated") {

            session()->setFlashdata('message', $message);
             // Use Query Builder to only update the deleted_at column
            $builder = $db->table('va_owner'); // change 'va' to your actual table name
            $builder->where('va_owner_id', $id);
            $builder->update(['deleted_at' => null]);

            $rows = $db->affectedRows();

            $extra = $rows > 0 ? "& Internal VA Restored" : "& Internal Restore Failed";
            return redirect()->to('/va-owner');
        }

        session()->setFlashdata('message', $message);
        return redirect()->back()->withInput();
    }

    //custom
    public function data(){
        $db = db_connect();
        $builder = $db->table('va_owner')->select('va_owner_id,anggota_nama, anggota_id, va_owner.va_owner_va, va_owner_hp, titpar_namatempat, titpar_lokasi, va_owner_berita_2, va_owner_berita_3, va_owner_email, titpar_klrhnid, kelurahan.kecamatan_id, kelurahan.kecamatan_nama, kelurahan.kelurahan_nama, va_owner.deleted_at, va_owner_expired, titpargrup_staanggota, anggota_foto, titpar_id')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
        ->join('dishub_titpar','dishub_titpar.titpar_id =dishub_titpargrup.titpargrup_titparid','left');
        $builder->join('kelurahan', 'kelurahan.id = dishub_titpar.titpar_klrhnid', 'left');

        $builder->groupBy('va_owner_va');

        $status = (string) ($this->request->getPost('status') ?? '');
        if ($status === 'aktif') {
            $builder->where('va_owner.deleted_at', null);
        } elseif ($status === 'nonaktif') {
            $builder->where('va_owner.deleted_at IS NOT NULL', null, false);
        }


        // Columns to apply search on
        $columns = ['va_owner_va','va_owner_hp','anggota_newnoreg', 'anggota_nama','anggota_nohp','titpar_namatempat'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);
    }

    public function detailVaApp($anggota_id,$bulan,$tahun)
    {
          $db = db_connect();

    // Step 1: Get VA owners (base data)
    $builder = $db->table('va_owner')
        ->select('
            va_owner_anggotaid, 
            va_owner_va, 
            anggota_nama, 
            va_owner_expired, 
        ')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->where('anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();


    foreach ($vaResults as &$row) {
        $va = $row['va_owner_va'];

        $potensi = $db->table('potensi')
            ->where('potensi_va', $va)
            ->get()
            ->getResultArray();

        $row['potensi'] = $potensi;
    }

    // Step 2: For each VA, get this month's transactions
    foreach ($vaResults as &$row) {
        $va = $row['va_owner_va'];

        $transaksi = $db->table('transaksi')
            ->where('transaksi_va', $va)
            ->where('MONTH(transaksi_tanggal)', $bulan)
            ->where('YEAR(transaksi_tanggal)', $tahun)
            ->orderBy('transaksi_tanggal','desc')
            ->get()
            ->getResultArray();

        $row['transaction'] = $transaksi;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);
    }

}
