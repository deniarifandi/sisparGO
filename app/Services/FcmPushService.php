<?php

namespace App\Services;

class FcmPushService
{
    public function notifyAdminReply(int $memberId, string $message): void
    {
        $preview = preg_replace('/\s+/u', ' ', trim($message)) ?? trim($message);
        if (mb_strlen($preview, 'UTF-8') > 160) {
            $preview = mb_substr($preview, 0, 159, 'UTF-8') . '…';
        }
        $projectId = trim((string) env('FCM_PROJECT_ID', ''));
        $serviceAccountPath = trim((string) env('FCM_SERVICE_ACCOUNT_PATH', ''));
        if ($projectId === '' || $serviceAccountPath === '' || !is_file($serviceAccountPath)) {
            log_message('warning', 'FCM is not configured; chat push was skipped.');
            return;
        }

        $db = \Config\Database::connect();
        $tokens = $db->table('device_push_tokens')
            ->select('device_token')
            ->where('user_id', (string) $memberId)
            ->get()
            ->getResultArray();
        if (!$tokens) return;

        try {
            $credentials = json_decode((string) file_get_contents($serviceAccountPath), true, 512, JSON_THROW_ON_ERROR);
            if (($credentials['project_id'] ?? '') !== $projectId) {
                throw new \RuntimeException('FCM project ID does not match the service account.');
            }
            $accessToken = $this->accessToken($credentials);
            $client = \Config\Services::curlrequest(['http_errors' => false, 'timeout' => 12]);

            foreach ($tokens as $row) {
                $response = $client->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    [
                        'headers' => ['Authorization' => "Bearer {$accessToken}"],
                        'json' => [
                            'message' => [
                                'fid' => $row['device_token'],
                                'notification' => [
                                    'title' => 'Pesan Admin',
                                    'body' => $preview,
                                ],
                                'data' => ['type' => 'admin_chat'],
                                'android' => ['priority' => 'high'],
                            ],
                        ],
                    ]
                );
                if ($response->getStatusCode() >= 400) {
                    $errorBody = json_decode((string) $response->getBody(), true);
                    log_message('warning', 'FCM send failed with HTTP {code}: {reason}', [
                        'code' => $response->getStatusCode(),
                        'reason' => $errorBody['error']['status'] ?? 'unknown',
                    ]);
                }
            }
        } catch (\Throwable $error) {
            log_message('error', 'FCM chat notification failed: {message}', ['message' => $error->getMessage()]);
        }
    }

    private function accessToken(array $credentials): string
    {
        $cacheKey = 'fcm_access_token_' . hash('sha256', $credentials['client_email']);
        $cachedToken = cache()->get($cacheKey);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $now = time();
        $encode = static fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header . '.' . $claims;
        if (!openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Unable to sign FCM authorization request.');
        }
        $jwt = $unsigned . '.' . $encode($signature);
        $client = \Config\Services::curlrequest(['http_errors' => false, 'timeout' => 12]);
        $response = $client->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);
        $body = json_decode((string) $response->getBody(), true);
        if ($response->getStatusCode() >= 400 || empty($body['access_token'])) {
            throw new \RuntimeException('Google OAuth did not return an access token.');
        }
        $ttl = max(1, min(3000, (int) ($body['expires_in'] ?? 3600) - 60));
        cache()->save($cacheKey, $body['access_token'], $ttl);
        return $body['access_token'];
    }
}
