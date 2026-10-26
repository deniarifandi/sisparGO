<?php

namespace App\Libraries;

class Whatsapp
{
     public function sendMessage($recipient, $nama, $nominal, $tanggal,$lokasi, $idtransaksi)
    {
        //$accessToken = 'EAAcbbjv93u0BPB6mVQi5DgDnvwiiZAZCSxsJUCYWV5q2nHoHSeRBlUIPHKkScdzN7vzATkTD1BDjVeJyNsyUZAT8VCb3CE33jI5vVQwrkJ4AP9aRHItxD9YywZAbXZB2q201crYyR75wQkhAFUekX9Tp4HVPhhu2IIaDPZCZCxAWwbUMWqvKn7KcG4YFgJZC3pQ7GGVVCyIbI59IJZB8HSzTknzaCsKgiHhY6JDqxAmctkA2Y6Dv6soclLIt1fxGFjAZDZD';
        $accessToken = 'EAAcbbjv93u0BPAsJ40nIQNgj92aP0yYkDl8CTym3QnQDmUDxTz5WqfvnU5Uo9eZBXvCpcZAadMqwWuT9qiUYZBDZBvWz06LAPokDLtfivZCGxZBY6mi5KClyBQCHHtRyQQKMK3ewkBdX2i61gngioW9ctSsmCG5X7gZAvVamrGpG5RZBe4musMXOQ7MmCswHikIasxTZBxyyF5N7ZCiJTbkZBXXlAZBdcD1YBW5ZBWdWFejdzdJ64JvzQIbLzmq64rKqhtwZDZD';
        $accessToken = 'EAAcbbjv93u0BPPdS3swcVVVzBJwkrn2oLGZABP1JMZBnd7XQqyZCwE6Fa3kK1L6QFfU7WvhHXTDT32IgLY0HvFCxn0ZAZBtn1iexZAiiKkKa9y0Ve5vhJNXvpZCBNqwmHvkb9mQh7mmisCp7cnrL9KoukWGt2CrrDIajwfPkA6f8ZB8HIWm4MJ0IwEW9ZBmmGXTIjOQZDZD';//production
        //$accessToken = "blablabasteuhsoaethusau";
        $phoneNumberId = '631443813396840'; //testing
        $phoneNumberId = '718496908011318';//production
        ; // international format

        $url = "https://graph.facebook.com/v19.0/$phoneNumberId/messages";

         $data = [
        "messaging_product" => "whatsapp",
        "to" => $recipient,
        "type" => "template",
        "template" => [
            "name" => "pembayaran_berhasil_prod", // your approved template name
            "language" => [
                "code" => "id"
            ],
            "components" => [
                [
                    "type" => "body",
                    "parameters" => [
                        [ "type" => "text", "text" => $nama ],
                        [ "type" => "text", "text" => $lokasi ],
                        [ "type" => "text", "text" => $nominal ],
                        [ "type" => "text", "text" => $tanggal ]
                    ]
                ],
                [
                    "type" => "button",
                    "sub_type" => "url",
                    "index" => 0,
                    "parameters" => [
                        [ "type" => "text", "text" => $idtransaksi ] // dynamic part of the URL
                    ]
                ]
            ]
        ]
    ];

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
          $data = json_decode($response, true);

        if (isset($data['error']['message'])) {
            // If there's an error, set flash message with detailed error
            $status = [
                'status'  => 'error',
                'message' => $data['error']['message'] ?? 'Something went wrong!'
            ];
        } else {
            // Success message
            $status = [
                'status'  => 'accepted',
                'message' => $data['messages'][0]['message_status'] ?? 'Message sent'
            ];
        }

        $db = \Config\Database::connect();
        $builder = $db->table('transaksi');

        // save only the status string into DB
        $builder->where('transaksi_id', $idtransaksi)
                ->update(['wa_konfirmasi' => $status['status']]);

        return $status;
            
            
        }

        curl_close($ch);
    }


    public function webhook(){
           $request = service('request');

        // Untuk verifikasi dari Meta
        if ($request->getMethod() === 'get' && $request->getGet('hub_mode')) {
            $verifyToken = 'ocueo8fu9oe8udo3e9udeo9uoea4u8heo243aoc223532uheochu9u8aeodue9o8u'; // Ubah sesuai token yang kamu daftarkan di Meta

            if (
                $request->getGet('hub_mode') === 'subscribe' &&
                $request->getGet('hub_verify_token') === $verifyToken
            ) {
                return $this->response->setBody($request->getGet('hub_challenge'));
            } else {
                return $this->failForbidden('Invalid verify token');
            }
        }

        // Tangkap body JSON saat ada pesan masuk
        $json = $request->getJSON(true); // true => array

        // Simpan ke log (opsional, bisa disimpan ke DB juga)
        log_message('info', 'Webhook received: ' . json_encode($json));

        // Contoh mengambil pesan teks
        if (isset($json['entry'][0]['changes'][0]['value']['messages'])) {
            $messages = $json['entry'][0]['changes'][0]['value']['messages'];

            foreach ($messages as $msg) {
                $from = $msg['from']; // nomor pengirim
                $body = $msg['text']['body'] ?? ''; // isi pesan teks

                // Lakukan sesuatu, misalnya:
                log_message('info', "Pesan dari $from: $body");
            }
        }

        return $this->respond(['status' => 'ok'], 200);
    }
}
