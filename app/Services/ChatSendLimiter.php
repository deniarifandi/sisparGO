<?php

namespace App\Services;

class ChatSendLimiter
{
    public function retryAfter(string $senderType, int $senderId): ?int
    {
        $throttler = service('throttler');
        $key = 'chat-send-' . $senderType . '-' . $senderId;

        if (! $throttler->check($key . '-2s', 1, 2)) {
            return $throttler->getTokenTime();
        }
        if (! $throttler->check($key . '-1m', 30, 60)) {
            return $throttler->getTokenTime();
        }

        return null;
    }
}
