<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ChatMessage extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Admin opens chat room
     */
    public function index($roomId)
    {
        $adminId = session()->get('user_id');

        $room = $this->db->table('chat_rooms cr')
            ->select('cr.*, a.anggota_nama')
            ->join('dishub_anggota a', 'a.anggota_id = cr.client_id')
            ->where('cr.id', $roomId)
            // ->where('cr.admin_id', $adminId)
            ->get()
            ->getRowArray();

        if (!$room) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // mark client messages as read
       $this->db->table('chat_messages')
    ->where('chat_room_id', $roomId)
    ->where('sender_type', 'client')
    ->update(['is_read' => 1]);

        return view('chat_room/detail', ['room' => $room]);
    }

    /**
     * Fetch messages (AJAX)
     */
    public function fetch($roomId)
    {
        $messages = $this->db->table('chat_messages')
            ->where('chat_room_id', $roomId)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($messages);
    }

    /**
     * Admin sends message to client
     */
    public function send()
    {
        $adminId = session()->get('user_id');
        $roomId  = $this->request->getPost('room_id');
        $rawMessage = $this->request->getPost('message');
        $message = is_string($rawMessage) ? trim($rawMessage) : '';

        if (!$roomId || $message === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Pesan tidak boleh kosong.',
            ]);
        }
        if (mb_strlen($message, 'UTF-8') > 1000) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Pesan maksimal 1.000 karakter.',
            ]);
        }

        // validate admin owns room
        $room = $this->db->table('chat_rooms')
            ->where('id', $roomId)
          //  ->where('admin_id', $adminId)
            ->get()
            ->getRow();

        if (!$room) {
            return $this->response->setStatusCode(403);
        }

        $retryAfter = (new \App\Services\ChatSendLimiter())->retryAfter('admin', (int) $adminId);
        if ($retryAfter !== null) {
            return $this->response
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) $retryAfter)
                ->setJSON([
                    'status' => false,
                    'message' => "Tunggu {$retryAfter} detik sebelum mengirim lagi.",
                    'retry_after' => $retryAfter,
                ]);
        }

       $this->db->table('chat_messages')->insert([
    'chat_room_id' => $roomId,
    'sender_id'    => $adminId,
    'sender_type'  => 'admin',
    'message'      => $message,
    // Client belum membaca pesan sampai ia membuka ruang chat.
    'is_read'      => 0
]);

        (new \App\Services\FcmPushService())->notifyAdminReply((int) $room->client_id, $message);

        return $this->response->setJSON(['status' => true]);
    }
}
