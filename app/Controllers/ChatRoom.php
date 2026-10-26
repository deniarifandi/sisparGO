<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DataTable;

class ChatRoom extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Start chat (client -> admin)
     */

    public function index()
    {
        return view('chat_room/index');
    }

public function start()
{
    $clientId = (int) session()->get('user_id');
    if ($clientId <= 0) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Invalid client'
        ])->setStatusCode(400);
    }

    $adminId = 1;

    // reuse existing room
    $room = $this->db->table('chat_rooms')
        ->select('id')
        ->where([
            'client_id' => $clientId,
            'admin_id'  => $adminId,
        ])
        ->get()
        ->getRow();

    if ($room) {
        return $this->response->setJSON([
            'status'        => true,
            'chat_room_id'  => (int) $room->id,
            'is_new'        => false,
        ]);
    }

    // insert new room
    $now = date('Y-m-d H:i:s');

    try {
        $this->db->table('chat_rooms')->insert([
            'id'         => $clientId,
            'client_id'  => $clientId,
            'admin_id'   => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } catch (\Throwable $e) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Failed to create chat room',
            'error'   => $e->getMessage(),
        ])->setStatusCode(500);
    }

    return $this->response->setJSON([
        'status'        => true,
        'chat_room_id'  => $this->db->insertID(),
        'is_new'        => true,
    ]);
}


     public function data()
    {
        $adminId = session()->get('user_id');

        $builder = $this->db->table('chat_rooms cr')
            ->select([
                'cr.id',
                'a.anggota_nama',
                'cm.message',
                'cm.created_at',
                "SUM(
                    CASE
                        WHEN cm_all.is_read = 0
                        AND cm_all.sender_type = 'client'
                        THEN 1 ELSE 0
                    END
                ) AS unread_count"
            ])
            ->join('dishub_anggota a', 'a.anggota_id = cr.client_id')

            // last message per room
            ->join(
                '(SELECT chat_room_id, MAX(created_at) AS max_date 
                  FROM chat_messages 
                  GROUP BY chat_room_id) cm_last',
                'cm_last.chat_room_id = cr.id',
                'left'
            )
            ->join(
                'chat_messages cm',
                'cm.chat_room_id = cr.id AND cm.created_at = cm_last.max_date',
                'left'
            )

            // unread counter
            ->join('chat_messages cm_all', 'cm_all.chat_room_id = cr.id')

            // ->where('cr.admin_id', $adminId)

            ->groupBy('cr.id');

        // MUST match table column order (including non-visible logic)
        $columns = [
    'cr.id',
    'a.anggota_nama',
    'cm.message',
    null,              // unread_count → disable search
    'cm.created_at',
    null               // action
];

        $dt = new DataTable($builder, $columns);
        return $this->response->setJSON($dt->generate());
    }

/**
 * Admin starts a new chat with a client
 */
public function adminStart()
{
    $adminId  = session()->get('user_id') ?? 1;
    $clientId = (int) $this->request->getPost('client_id');

    if ($clientId <= 0) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Invalid client'
        ])->setStatusCode(400);
    }

    // room id == client_id in this schema (not auto-increment)
    $room = $this->db->table('chat_rooms')
        ->where('id', $clientId)
        ->get()
        ->getRow();

    if ($room) {
        return $this->response->setJSON([
            'status'       => true,
            'chat_room_id' => (int) $room->id,
            'is_new'       => false,
        ]);
    }

    $now = date('Y-m-d H:i:s');

    try {
        $this->db->table('chat_rooms')->insert([
            'id'         => $clientId,
            'client_id'  => $clientId,
            'admin_id'   => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } catch (\Throwable $e) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Failed to create chat room',
            'error'   => $e->getMessage(),
        ])->setStatusCode(500);
    }

    return $this->response->setJSON([
        'status'       => true,
        'chat_room_id' => $clientId,
        'is_new'       => true,
    ]);
}

/**
 * Search clients (dishub_anggota) for the "start chat" picker
 */
public function searchClient()
{
    $keyword = $this->request->getGet('q');

    $clients = $this->db->table('dishub_anggota')
        ->select('dishub_anggota.anggota_id, anggota_nama, titpar_namatempat')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id', 'left')
        ->join('dishub_titpar', 'dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid', 'left')
        ->groupStart()
            ->like('anggota_nama', $keyword)
            ->orLike('titpar_namatempat', $keyword)
        ->groupEnd()
        ->where('anggota_status', 3)
        ->groupBy('dishub_anggota.anggota_id')
        ->orderBy('anggota_nama', 'ASC')
        ->limit(20)
        ->get()
        ->getResultArray();

    return $this->response->setJSON($clients);
}
}
