<?php

namespace App\Controllers;

class LegalDocuments extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $rows = $this->db->table('legal_documents')->orderBy('document_type')->get()->getResultArray();
        return view('legal_documents/index', ['rows' => $rows]);
    }

    public function apiIndex()
    {
        $rows = $this->db->table('legal_documents')->select('document_type,title,content,effective_date')->where('is_active', 1)->orderBy('document_type')->get()->getResultArray();
        return $this->response->setJSON(['data' => $rows]);
    }

    public function save()
    {
        $id = (int) $this->request->getPost('legal_id');
        $data = [
            'title' => trim((string) $this->request->getPost('title')),
            'content' => trim((string) $this->request->getPost('content')),
            'effective_date' => $this->request->getPost('effective_date') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('legal_documents')->where('legal_id', $id)->update($data);
        return redirect()->to(base_url('legal-documents'))->with('success', 'Dokumen legal berhasil diperbarui.');
    }
}
