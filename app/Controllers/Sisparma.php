<?php

namespace App\Controllers;

use App\Libraries\DataTable;


class Sisparma extends BaseController
{
    // Fix PHP 8.2 deprecation: dynamic property $this->db pada store_payment()
    protected $db;

    public function va_notify(){

        // ====================================================================
        // P0-Lapis A: Webhook audit logging
        // Catat SETIAP request masuk (header + body + IP) ke writable/logs/
        // Tujuannya: mendapat sample request asli Bank Jatim untuk benchmark
        //            signature verification (Lapis B). Zero side-effect.
        // ====================================================================
        $rawBody = file_get_contents("php://input");
                $reqHeaders = function_exists('getallheaders') ? getallheaders() : [];
                $clientIp  = $this->request->getIPAddress();

                // Pastikan writable/logs/ ada (CI4 biasanya auto-create, tapi jaga-jaga)
                $logDir = WRITEPATH . 'logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0755, true);
                }

                $logPayload = [
            'ts'      => date('c'),
            'ip'      => $clientIp,
            'method'  => $this->request->getMethod(),
            'uri'     => $this->request->getUri()->getPath(),
            'headers' => [
                'X-SIGNATURE' => $this->request->getHeaderLine('X-SIGNATURE'),
                'X-TIMESTAMP' => $this->request->getHeaderLine('X-TIMESTAMP'),
                'X-PARTNER-ID'=> $this->request->getHeaderLine('X-PARTNER-ID'),
                'X-CLIENT-KEY'=> $this->request->getHeaderLine('X-CLIENT-KEY'),
                'CHANNEL-ID'  => $this->request->getHeaderLine('CHANNEL-ID'),
                'X-EXTERNAL-ID'=>$this->request->getHeaderLine('X-EXTERNAL-ID'),
                'Content-Type'=> $this->request->getHeaderLine('Content-Type'),
            ],
            'body_raw' => $rawBody,
            'all_headers_raw' => $reqHeaders,
        ];
        log_message('info', '[VA-NOTIFY] ' . json_encode($logPayload));

        // ====================================================================
        // P0-Lapis B: Verifikasi signature SNAP (HMAC-SHA512)
        // Secret dari .env bankJatim.webhookSecret.
        // KOSONG  = skip verifikasi (mode transisi, log SKIPPED)
        // TERISI  = signature salah -> 401, webhook asli bank tetap lolos
        // Formula: HMAC-SHA512( "POST:<path>:<bearerToken>:<sha256(bodyMinified)>:<X-TIMESTAMP>" , secret )
        // Path dicocokkan dengan header Url-Path dari bank, fallback kandidat standar.
        // ====================================================================
        $this->verifyBankSignature($rawBody, $reqHeaders, $clientIp);

        // Read and decode JSON input
        $input = $rawBody;
        $data = json_decode($input, true);

        // Validate JSON body structure
        if (!isset($data['latestTransactionStatus'], $data['transactionStatusDesc'], $data['virtualAccountNo'], $data['amount'], $data['coreReferenceNo'], $data['transactionDate'])) {
            log_message('warning', '[VA-NOTIFY] Invalid payload structure from IP=' . $clientIp);
            http_response_code(400);
            echo json_encode([
                "responseCode" => "4000002",
                "responseMessage" => "Invalid JSON structure"
            ]);
            exit;
        }

    // Process the request (add your logic here)

    //$data['transactionDate'] = (new \DateTime())->format(\DateTime::ATOM);

    $this->store_payment($data['latestTransactionStatus'], $data['transactionStatusDesc'], $data['virtualAccountNo'], $data['amount'], $data['coreReferenceNo'], $data['transactionDate']);

    $responseCode = "2000000";
    $responseMessage = "Request has been processed successfully";

    // Send JSON response
    http_response_code(200);
    echo json_encode([
        "responseCode" => $responseCode,
        "responseMessage" => $responseMessage
    ]);
    }

    public function store_payment(
    $latestTransactionStatus,
    $transactionStatusDesc,
    $virtualAccountNo,
    $amount,
    $coreReferenceNo,
    $transactionDate
) {
            $this->db = db_connect();
            $builder  = $this->db->table('transaksi');

           $exists = $builder
            ->where('coreReferenceNo', $coreReferenceNo)
            ->where('DATE(transaksi_tanggal)', date('Y-m-d', strtotime($transactionDate)))
            ->get()
            ->getRow();

        if ($exists) {

                    log_message('warning', '[VA-NOTIFY] Duplicate detected coreRef=' . $coreReferenceNo . ' date=' . date('Y-m-d', strtotime($transactionDate)) . ' va=' . $virtualAccountNo);

                    $builder  = $this->db->table('dump_transaksi');
                    $data = [
                        'latestTransactionStatus' => $latestTransactionStatus,
                        'transactionStatusDesc'   => $transactionStatusDesc,
                        'transaksi_va'            => $virtualAccountNo,
                        'transaksi_nominal'       => is_array($amount) ? $amount['value'] : $amount,
                        'coreReferenceNo'         => $coreReferenceNo,
                        'transaksi_tanggal'       => $transactionDate
                    ];

                    $builder->insert($data);

                    return true;
                }

                $data = [
                    'latestTransactionStatus' => $latestTransactionStatus,
                    'transactionStatusDesc'   => $transactionStatusDesc,
                    'transaksi_va'            => $virtualAccountNo,
                    'transaksi_nominal'       => is_array($amount) ? $amount['value'] : $amount,
                    'coreReferenceNo'         => $coreReferenceNo,
                    'transaksi_tanggal'       => $transactionDate
                ];

                $builder->insert($data);
                $id = $this->db->insertID();

                log_message('info', '[VA-NOTIFY] Stored transaksi id=' . $id . ' coreRef=' . $coreReferenceNo . ' va=' . $virtualAccountNo . ' nominal=' . (is_array($amount) ? $amount['value'] : $amount));

                return true;
        }



    function signature_service($method, $url, $requestBody, $accessToken){

    $requestBodyEncode = json_encode($requestBody);

        //Step 1: Minify request body by removing whitespaces
    $minifiedRequestBody = json_encode(json_decode(json)($requestBodyEncode));

        // Step 2: Generate SHA-256 hash of minified request body
    $hashedRequestBody = hash("sha256", $minifiedRequestBody);

        // Step 3: Convert hash to lowercase hexadecimal
    $lowercaseHexHash = strtolower($hashedRequestBody);

        // Step 4: Concatenate all components
    $signature = $method . ":" . $url . ":" . $accessToken . ":".$lowercaseHexHash.":". $this->timestamp;

    $xsignature = $this->encryptWithHmacSHA512($signature,$this->clientSecret);
    return $xsignature;

}   

    function encryptWithHmacSHA512($plainText, $secretKey) {
    // Generate the HMAC SHA-512 hash
        $hash = hash_hmac('sha512', $plainText, $secretKey, true);

        // Encode the hash in Base64
        $base64Hash = base64_encode($hash);

        return $base64Hash;
    }

    /**
     * P0-Lapis B: Verifikasi X-SIGNATURE webhook Bank Jatim (SNAP symmetric).
     * Formula: base64(HMAC-SHA512("POST:<path>:<bearerToken>:<sha256(bodyMinified)>:<X-TIMESTAMP>", secret))
     * - Secret dari .env: bankJatim.webhookSecret
     * - Kosong -> SKIPPED (mode transisi)
     * - Secret terisi + signature tidak cocok -> 401 (stop eksekusi)
     */
    private function verifyBankSignature(string $rawBody, array $headerBag, string $clientIp): void
    {
        $secret = env('bankJatim.webhookSecret', '');

        if ($secret === null || trim((string) $secret) === '') {
            log_message('info', '[VA-NOTIFY] SIGNATURE_CHECK=SKIPPED (bankJatim.webhookSecret kosong di .env)');
            return;
        }

        $sigReceived = $headerBag['X-SIGNATURE'] ?? ($headerBag['x-signature'] ?? '');
        $timestamp   = $headerBag['X-TIMESTAMP'] ?? ($headerBag['x-timestamp'] ?? '');
        $authHeader  = $headerBag['Authorization'] ?? ($headerBag['authorization'] ?? '');
        $urlPathHdr  = $headerBag['Url-Path'] ?? ($headerBag['url-path'] ?? '');

        if ($sigReceived === '' || $timestamp === '') {
            log_message('warning', '[VA-NOTIFY] SIGNATURE_CHECK=FAILED missing-headers ip=' . $clientIp);
            http_response_code(401);
            echo json_encode([
                "responseCode" => "4010000",
                "responseMessage" => "Unauthorized. Missing signature headers."
            ]);
            exit;
        }

        // Bearer token: header bank "Authorization: Bearer <jwt>"
        $bearerToken = preg_replace('/^Bearer\s+/i', '', trim($authHeader));

        // Kandidat path: prioritas header Url-Path dari bank, lalu fallback standar
        $pathCandidates = [];
        if ($urlPathHdr !== '') {
            $pathCandidates[] = $urlPathHdr;
        }
        foreach (['/v1.0/transfer-va/va-notify', '/api/v1.0/transfer-va/va-notify'] as $fallback) {
            if (!in_array($fallback, $pathCandidates, true)) {
                $pathCandidates[] = $fallback;
            }
        }

        // SHA-256 body minified (lowercase hex) — standar SNAP
        $bodyDecoded = json_decode($rawBody);
        $bodyMinified = ($bodyDecoded !== null) ? json_encode($bodyDecoded) : $rawBody;
        $bodyHash = strtolower(hash('sha256', $bodyMinified));

        $verified = false;
        $triedSts = [];
        foreach ($pathCandidates as $path) {
            $stringToSign = "POST:" . $path . ":" . $bearerToken . ":" . $bodyHash . ":" . $timestamp;
            $triedSts[] = $path;
            $calculated = base64_encode(hash_hmac('sha512', $stringToSign, $secret, true));
            if (hash_equals($calculated, $sigReceived)) {
                $verified = true;
                break;
            }
        }

        if (!$verified) {
            log_message('warning', '[VA-NOTIFY] SIGNATURE_CHECK=REJECTED ip=' . $clientIp
                . ' paths_tried=' . implode('|', $triedSts)
                . ' ts=' . $timestamp
                . ' body_sha256=' . $bodyHash);
            http_response_code(401);
            echo json_encode([
                "responseCode" => "4010000",
                "responseMessage" => "Unauthorized. Signature verification failed."
            ]);
            exit;
        }

        log_message('info', '[VA-NOTIFY] SIGNATURE_CHECK=PASSED path=' . $triedSts[0] . ' ip=' . $clientIp);
    }

}
