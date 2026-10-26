<?php 

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ChatController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // List chat rooms
    public function rooms()
    {
        $userId = session()->get('user_id');

        $rooms = $this->db->table('chat_rooms')
            ->where('admin_id', $userId)
            ->orWhere('client_id', $userId)
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $rooms
        ]);
    }

    // Start chat (client starts chat with admin)
    public function start()
    {
        $clientId = session()->get('user_id');
        $adminId  = $this->request->getPost('admin_id');

        $room = $this->db->table('chat_rooms')
            ->where('client_id', $clientId)
            ->where('admin_id', $adminId)
            ->get()->getRow();

        if (!$room) {
            $this->db->table('chat_rooms')->insert([
                'client_id' => $clientId,
                'admin_id'  => $adminId
            ]);
            $roomId = $this->db->insertID();
        } else {
            $roomId = $room->id;
        }

        return $this->response->setJSON([
            'status' => true,
            'chat_room_id' => $roomId
        ]);
    }

    // Get messages
    public function messages($roomId)
    {
        $messages = $this->db->table('chat_messages')
            ->where('chat_room_id', $roomId)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $messages
        ]);
    }

    // Send message
    public function send($roomId)
    {
        $senderId = session()->get('user_id');
        $message  = $this->request->getPost('message');

        $this->db->table('chat_messages')->insert([
            'chat_room_id' => $roomId,
            'sender_id' => $senderId,
            'message' => $message
        ]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Message sent'
        ]);
    }
}