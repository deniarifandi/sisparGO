<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DeviceToken extends BaseController
{
    public function register()
    {
        $userId = (string) session()->get('user_id');
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $token = trim((string) ($payload['fid'] ?? ''));
        $platform = trim((string) ($payload['platform'] ?? 'android'));

        if (!ctype_digit($userId) || (int) $userId <= 0 || $token === '' || strlen($token) > 255) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Invalid installation ID.',
            ]);
        }
        if (!in_array($platform, ['android', 'ios'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Invalid platform.',
            ]);
        }

        $db = \Config\Database::connect();
        $existing = $db->table('device_push_tokens')
            ->select('id')
            ->where('device_token', $token)
            ->get()
            ->getRow();
        $values = [
            'user_id' => $userId,
            'platform' => $platform,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $db->table('device_push_tokens')->where('id', $existing->id)->update($values);
        } else {
            $db->table('device_push_tokens')->insert($values + [
                'device_token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON(['status' => true]);
    }
}
