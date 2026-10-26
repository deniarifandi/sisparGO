<?php 

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ChatMessage extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

     public function messages($roomId)
    {
        $clientId = (int) session()->get('user_id');

        if ($clientId <= 0 || !$roomId) {
            return $this->response->setStatusCode(400);
        }

        // validate that this room belongs to the client
        $room = $this->db->table('chat_rooms')
            ->where('id', $roomId)
            ->where('client_id', $clientId)
            ->get()
            ->getRow();

        if (!$room) {
            return $this->response->setStatusCode(403);
        }

        // Membuka ruang chat menandai pesan dari admin sebagai sudah dibaca.
        $this->db->table('chat_messages')
            ->where('chat_room_id', $roomId)
            ->where('sender_type', 'admin')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // fetch messages
        $messages = $this->db->table('chat_messages')
            ->select('id, sender_type, message, is_read, created_at')
            ->where('chat_room_id', $roomId)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data'   => $messages
        ]);
    }

    public function unread($legacyClientId = null)
    {
        $clientId = (int) session()->get('user_id');
        if ($clientId <= 0) {
            return $this->response->setStatusCode(401);
        }

        $room = $this->db->table('chat_rooms')
            ->select('id')
            ->where('client_id', $clientId)
            ->get()
            ->getRow();

        $unread = 0;
        if ($room) {
            $unread = $this->db->table('chat_messages')
                ->where('chat_room_id', $room->id)
                ->where('sender_type', 'admin')
                ->where('is_read', 0)
                ->countAllResults();
        }

        return $this->response->setJSON([
            'status' => true,
            'unread' => $unread,
        ]);
    }

    public function send()
    {

        $clientId = (int) session()->get('user_id');
        $roomId   = $this->request->getPost('chat_room_id');
        $rawMessage = $this->request->getPost('message');
        $message = is_string($rawMessage) ? trim($rawMessage) : '';

        if ($clientId <= 0 || !$roomId || $message === '') {
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

        $room = $this->db->table('chat_rooms')
            ->where('id', $roomId)
            ->where('client_id', $clientId)
            ->get()
            ->getRow();

        if (!$room) {
            return $this->response->setStatusCode(404);
        }

        $retryAfter = (new \App\Services\ChatSendLimiter())->retryAfter('client', $clientId);
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
    'sender_id'    => $clientId,
    'sender_type'  => 'client',
    'message'      => $message,
    'is_read'      => 0
]);


        return $this->response->setJSON(['status' => true]);
    }
}
