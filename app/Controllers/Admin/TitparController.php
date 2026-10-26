<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RegisterTitparModel;
use App\Services\UploadService;

class TitparController extends BaseController
{
    protected RegisterTitparModel $titparModel;

    public function __construct()
    {
        $this->titparModel = new RegisterTitparModel();
    }

    public function detail(int $id)
    {
        $titpar = $this->titparModel->getWithRegister($id);
        if (!$titpar) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('register/detail_titpar', compact('titpar'));
    }

    public function update(int $id)
    {
        $this->titparModel->update($id, [
            'titpar_namatempat' => $this->request->getPost('titpar_namatempat'),
            'titpar_lokasi'     => $this->request->getPost('titpar_lokasi'),
            'latitude'          => $this->request->getPost('latitude'),
            'longitude'         => $this->request->getPost('longitude'),
            'jenis_kendaraan'   => RegisterTitparModel::parseJenisKendaraan(
                                        (array)$this->request->getPost('jenis_kendaraan')
                                   ),
            'jam_operasional'   => $this->request->getPost('jam_operasional'),
            'batas_parkir'      => $this->request->getPost('batas_parkir'),
            'va'      => $this->request->getPost('va'),
            // 'kesanggupan'       => $this->request->getPost('kesanggupan'),
            // 'keberatan'         => $this->request->getPost('keberatan'),
            // 'melintang'         => $this->request->getPost('melintang'),
            // 'melintang_alasan'  => $this->request->getPost('melintang_alasan'),
            // 'macet'             => $this->request->getPost('macet'),
            // 'macet_alasan'      => $this->request->getPost('macet_alasan'),
            // 'sosial'            => $this->request->getPost('sosial'),
            // 'sosial_alasan'     => $this->request->getPost('sosial_alasan'),
            // 'potensi_senin'     => $this->request->getPost('potensi_senin'),
            // 'potensi_selasa'    => $this->request->getPost('potensi_selasa'),
            // 'potensi_rabu'      => $this->request->getPost('potensi_rabu'),
            // 'potensi_kamis'     => $this->request->getPost('potensi_kamis'),
            // 'potensi_jumat'     => $this->request->getPost('potensi_jumat'),
            // 'potensi_sabtu'     => $this->request->getPost('potensi_sabtu'),
            // 'potensi_minggu'    => $this->request->getPost('potensi_minggu'),
            'status'            => (int)$this->request->getPost('status'),
            'catatan'           => $this->request->getPost('catatan'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function updateKondisi(int $id)
    {
        $melintang = (bool)$this->request->getPost('melintang');
        $macet     = (bool)$this->request->getPost('macet');
        $sosial    = (bool)$this->request->getPost('sosial');

        $this->titparModel->update($id, [
            'melintang'        => $melintang ? 1 : 0,
            'melintang_alasan' => $melintang ? trim($this->request->getPost('melintang_alasan')) : null,
            'macet'            => $macet ? 1 : 0,
            'macet_alasan'     => $macet ? trim($this->request->getPost('macet_alasan')) : null,
            'sosial'           => $sosial ? 1 : 0,
            'sosial_alasan'    => $sosial ? trim($this->request->getPost('sosial_alasan')) : null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Kondisi lapangan berhasil diperbarui.');
    }

    public function updatePotensi(int $id)
    {
        $this->titparModel->update($id, [
            'potensi_senin'  => $this->request->getPost('potensi_senin'),
            'potensi_selasa' => $this->request->getPost('potensi_selasa'),
            'potensi_rabu'   => $this->request->getPost('potensi_rabu'),
            'potensi_kamis'  => $this->request->getPost('potensi_kamis'),
            'potensi_jumat'  => $this->request->getPost('potensi_jumat'),
            'potensi_sabtu'  => $this->request->getPost('potensi_sabtu'),
            'potensi_minggu' => $this->request->getPost('potensi_minggu'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Potensi kendaraan berhasil diperbarui.');
    }

    public function upload(string $field)
    {
        $allowed = ['kesanggupan','keberatan','denah','kk','berita_acara','penunjukan','foto_tampak_depan','foto_tampak_samping'];

        if (!in_array($field, $allowed)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Field tidak valid.']);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak ditemukan.']);
        }

        $row = $this->titparModel->find($id);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $upload = new UploadService();
        $result = $upload->handle(
            $this->request->getFile($field),
            $field,
            $row[$field] ?? ''
        );

        if (!$result['success']) {
            return $this->response->setJSON(['status' => false, 'message' => $result['message']]);
        }

        $this->titparModel->update($id, [
            $field       => $result['filename'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true, 'message' => 'Upload berhasil.']);
    }

    public function updateFoto()
    {
        $id = $this->request->getPost('id');

        $upload = new UploadService();

        $fields = ['foto_tampak_depan', 'foto_tampak_samping','kesanggupan', 'keberatan','denah','berita_acara','penunjukan','survey','persetujuan'];

        $data = [
            'updated_at'     => date('Y-m-d H:i:s'),
        ];


        foreach ($fields as $field) {
            $result = $upload->handle(
                $this->request->getFile($field),
                $field,
                $existing[$field] ?? ''
            );

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            if ($result['filename']) {
                $data[$field] = $result['filename'];
                
            }
        }


        $this->titparModel->update($id, $data);

        return redirect()->to(base_url('admin/register/detail/' . $id))
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function print(int $id)
    {
        $titpar = $this->titparModel->getFullDetailForPrint($id);

        if (!$titpar) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('register/print_persetujuan', ['titpar' => $titpar]);
    }

  
}