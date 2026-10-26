<?php

namespace App\Controllers;

use App\Libraries\DataTable;

class Chat extends BaseController
{
    public $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Chat index page
     */
    public function index()
    {
        return view('chat/index');
    }

    /**
     * DataTables server-side data
     */
    public function data()
    {
        // $adminId = session()->get('user_id');
        $adminId = 1;

        $builder = $this->db->table('chat_rooms cr')
    ->select([
        'cr.id AS room_id',
        'a.anggota_nama AS client_name',
        'MAX(cm.message) AS last_message',
        'MAX(cm.created_at) AS last_message_at',
        "SUM(
            CASE 
                WHEN cm.is_read = 0 
                AND cm.sender_id != {$adminId}
                THEN 0 ELSE 1 
            END
        ) AS unread_count"
    ])
    ->join('dishub_anggota a', 'a.anggota_id = cr.client_id')
    ->join('chat_messages cm', 'cm.chat_room_id = cr.id', 'left')
    ->where('cr.admin_id', $adminId)
    ->groupBy('cr.id');


        /**
         * Columns allowed for searching
         * Must match selected aliases
         */
        $columns = [
            'room_id',
            'client_name',
            'last_message'
        ];

        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);
    }
}