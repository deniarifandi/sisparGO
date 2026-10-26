<?php

namespace App\Libraries;

class Jatim
{

    private $clientID = null;
    private $timestamp = null;
    private $stringtosign = null;
    private $clientSecret = null;
    private $partnerID = null;
    private $accessToken = null;
    private $signature_access_token = null;
    private $service_url = null;
    private $serverUrl = null;
    private $tokenUrl = null;

    // Kredensial per-prefix (diisi di constructor dari .env)
    private $clientID_15088 = null;
    private $clientSecret_15088 = null;
    private $clientID_10325 = null;
    private $clientSecret_10325 = null;
    private $partnerServiceId = null;
    private $value = null;

    function __construct(){
        // Kredensial SNAP dari .env — TANPA fallback: jika .env hilang, request bank
        // gagal terlihat jelas (bukan diam-diam pakai secret lama yang tersebar di Git).
        $this->clientID_15088      = env('bankJatim.clientId_15088');
        $this->clientSecret_15088  = env('bankJatim.clientSecret_15088');
        $this->clientID_10325      = env('bankJatim.clientId_10325');
        $this->clientSecret_10325  = env('bankJatim.clientSecret_10325');
        $this->partnerID           = env('bankJatim.partnerId');

        $this->service_url = "https://majapahit.bankjatim.co.id/snap/rest";
        date_default_timezone_set("Asia/Bangkok");

        $this->getTimestamp();

        //jatim
        $this->serverUrl = "https://majapahit.bankjatim.co.id/snap/rest";

        $this->tokenUrl = "https://majapahit.bankjatim.co.id/snap/rest/access_token/v1.0/b2b";

        $this->value = "1000000000.00";
    }

    ////////////////// prefix checker

    public function prefix_checker($va){
        if (substr($va, 0, 5) === "15088") {

            $this->clientID = $this->clientID_15088;
            $this->clientSecret = $this->clientSecret_15088;
            $this->partnerServiceId = "   15088";
        } elseif (substr($va, 0, 5) === "10325") {

            $this->clientID = $this->clientID_10325;
            $this->clientSecret = $this->clientSecret_10325;
            $this->partnerServiceId = "   10325";
        }else{
             return "wrong prefix";
        }
    }
    
    ///////////////////////////////////////

   // public function createVA($customerNo, $vaNo, $vaName, $expired, $vaEmail, $vaPhone){
    public function createVA($customerNo, $vaNo, $vaName, $expired, $vaEmail,$vaPhone, $vaTagihan){

        $this->prefix_checker($vaNo);

        $method = "POST";
        $url = "/api/v1.0/transfer-va/create-va";

        $baseUrl = $this->serverUrl.$url;
        
        $accessToken = $this->get_access_token();

        $requestBody = [
            "partnerServiceId" => $this->partnerServiceId,
            "customerNo" => $customerNo,
            "virtualAccountNo" => $vaNo,
            "virtualAccountName" => $vaName,
            "virtualAccountEmail" => $vaEmail,
            "virtualAccountPhone" => $vaPhone,
            "trxId" => date('YmdHis'),
            "totalAmount" => [
                "value" => $vaTagihan,
                "currency" => "IDR"
            ],
            "virtualAccountTrxType" => "I",
            "expiredDate" => $expired
        ];
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ".$accessToken,
            "X-CLIENT-KEY: $this->clientID",
            "X-SIGNATURE: ".$this->signature_service($method, $url, $requestBody, $accessToken),
            "X-TIMESTAMP: $this->timestamp",
            "X-PARTNER-ID: $this->partnerID",
            "X-EXTERNAL-ID: ".date('YmdHis'),
            "CHANNEL-ID: 00002"
        ];

        $method = "POST";  // Can be 'GET', 'POST', '', etc.

        $response = $this->callApi($baseUrl, $headers, $method, $requestBody);
         return $response;
    }

    function updateVA($customerNo, $vaNo, $vaName, $expired, $vaEmail, $vaPhone, $vaTagihan, $namatempat, $retritipe)
{
    $this->prefix_checker($vaNo);

    if ($retritipe == 2) {
        $retritipeLabel = "Tepi Jalan Umum (TJU)";
    } elseif ($retritipe == 3) {
        $retritipeLabel = "Tempat Khusus Parkir (TKP)";
    } else {
        $retritipeLabel = "Tepi Jalan Umum (TJU) (UNCF)";
    }

    $url = "/api/v1.0/transfer-va/update-va";
    $baseUrl = $this->serverUrl . $url;
    $method = "PUT";

    $accessToken = $this->get_access_token();

    // trxId WAJIB numeric, max 64 karakter — generate baru tiap request
    $trxId = date('YmdHis') . mt_rand(100, 999); // contoh: 20260728143022456 (17 digit, aman)

    $requestBody = [
        "partnerServiceId"     => $this->partnerServiceId,
        "customerNo"           => $customerNo,
        "virtualAccountNo"     => $vaNo,
        "virtualAccountName"   => $vaName,
        "virtualAccountEmail"  => $vaEmail,   // pakai parameter asli, bukan hardcode
        "virtualAccountPhone"  => $vaPhone,   // pakai parameter asli, bukan $retritipe
        "trxId"                => $trxId,     // numeric, bukan $namatempat
        "totalAmount" => [
            "value"    => $vaTagihan,
            "currency" => "IDR"
        ],
        "virtualAccountTrxType" => "I",
        "expiredDate"            => $expired,
        // kalau namatempat & retritipe memang perlu dikirim, cek dokumentasi API Jatim
        // apakah ada field khusus (mis. additionalInfo) untuk data tambahan ini —
        // jangan dipaksa masuk ke field yang formatnya sudah ditentukan (trxId, phone, email)
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken,
        "X-CLIENT-KEY: $this->clientID",
        "X-SIGNATURE: " . $this->signature_service($method, $url, $requestBody, $accessToken),
        "X-TIMESTAMP: $this->timestamp",
        "X-PARTNER-ID: $this->partnerID",
        "X-EXTERNAL-ID: " . date('YmdHis'),
        "CHANNEL-ID: 00002"
    ];

    $response = $this->callApi($baseUrl, $headers, $method, $requestBody);
    return $response;
}

    function deleteVA($customerNo, $vaNo){

        $this->prefix_checker($vaNo);
        $url = "/api/v1.0/transfer-va/delete-va";
        $baseUrl = $this->serverUrl.$url;
        $method = "DELETE";

       $accessToken = $this->get_access_token();

       $requestBody = [
        "partnerServiceId" => $this->partnerServiceId,
        "customerNo" => $customerNo,
        "virtualAccountNo" => $vaNo,
        "trxId" => date('YmdHis')
    ];

            // echo json_encode($requestBody);
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer ".$accessToken,
        "X-CLIENT-KEY: $this->clientID",
        "X-SIGNATURE: ".$this->signature_service($method, $url, $requestBody, $accessToken),
        "X-TIMESTAMP: $this->timestamp",
        "X-PARTNER-ID: $this->partnerID",
        "X-EXTERNAL-ID: ".date('YmdHis'),
        "CHANNEL-ID: 00002"
    ];

            // $method = "DELETE";  // Can be 'GET', 'POST', 'PUT', etc.

    $response = $this->callApi($baseUrl, $headers, $method, $requestBody);
    return $response;            
    
    }

    function inquiryva($vaNo){
        
        $this->prefix_checker($vaNo);
        $method = "POST";
        $url = "/api/v1.0/transfer-va/inquiry-va";
        $baseUrl = $this->serverUrl.$url;
        $customerNo = substr($vaNo, 5);
        
        $trxId = date('YmdHis') . mt_rand(100, 999); // unique per request, numeric max 64

        $accessToken = $this->get_access_token();
        
        $requestBody = [
            "partnerServiceId" => $this->partnerServiceId,
            "customerNo" => $customerNo,
            "virtualAccountNo" => $vaNo,
            "trxId" => $trxId
        ];

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ".$accessToken,
            "X-CLIENT-KEY: $this->clientID",
            "X-SIGNATURE: ".$this->signature_service($method, $url, $requestBody, $accessToken),
            "X-TIMESTAMP: $this->timestamp",
            "X-PARTNER-ID: $this->partnerID",
            "X-EXTERNAL-ID: ".date('YmdHis'),
            "CHANNEL-ID: 00002"
        ];


        $method = "POST";  // Can be 'GET', 'POST', 'PUT', etc.

        $response = $this->callApi($baseUrl, $headers, $method, $requestBody);
        return $response;
        // print_r($response);
    }

    ///////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function get_access_token(){
        $url = $this->tokenUrl;
     // $url = "https://sriwijaya.bankjatim.co.id/snap/rest/access_token/v1/b2b";
        $headers = [
            "Content-Type: application/json",
            "X-CLIENT-KEY: $this->clientID",
            "X-SIGNATURE: ".$this->signature_access_token(),
            "X-TIMESTAMP: $this->timestamp"
        ];
        $method = "POST";  // Can be 'GET', 'POST', 'PUT', etc.
        $data = [
            "grantType" => "client_credentials",
            "additionalInfo" => (object)[]
        ];

        $response = $this->callApi($url, $headers, $method, $data);
        $result = json_decode($response, true);
        //print_r($response);
        
        if (isset($result['accessToken'])) {
            $this->accessToken = $result['accessToken'];
            return $result['accessToken'];
        } else {
            //throw new \Exception("Failed to get access token: " . $response);
            return $response;
        }
        
        
    }

    function getTimestamp(){
        $this->timestamp = (new \DateTime())->format(\DateTime::ATOM);
    }

    function encryptWithHmacSHA512($plainText, $secretKey) {
        $hash = hash_hmac('sha512', $plainText, $secretKey, true);
        $base64Hash = base64_encode($hash);
        return $base64Hash;
    }

    function signature_access_token(){

        $stringtosign = $this->clientID."|".$this->timestamp;
        $signature_access_token = $this->encryptWithHmacSHA512($stringtosign, $this->clientSecret);
      
        return $signature_access_token;
    }

    function signature_service($method, $url, $requestBody, $accessToken){

        $requestBodyEncode = json_encode($requestBody);

            //Step 1: Minify request body by removing whitespaces
        $minifiedRequestBody = json_encode(json_decode($requestBodyEncode));

            // Step 2: Generate SHA-256 hash of minified request body
        $hashedRequestBody = hash("sha256", $minifiedRequestBody);

            // Step 3: Convert hash to lowercase hexadecimal
        $lowercaseHexHash = strtolower($hashedRequestBody);

            // Step 4: Concatenate all components
        $signature = $method . ":" . $url . ":" . $accessToken . ":".$lowercaseHexHash.":". $this->timestamp;

        $xsignature = $this->encryptWithHmacSHA512($signature,$this->clientSecret);
        return $xsignature;

    }

    function callApi($url, $headers = [], $method = 'GET', $data = null) {

        $ch = curl_init($url);

    // Set request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // Attach headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // If there's data for POST or PUT, attach it to the request
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

    // Return response instead of outputting it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout: jangan biarkan worker PHP menggantung selamanya saat bank lambat
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // SSL verification (produksi wajib tetap on)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    // Execute the request and capture the response
        $response = curl_exec($ch);

    // Check for errors — log, jangan echo (echo merusak body JSON response)
        if (curl_errno($ch)) {
            log_message('error', '[JATIM-API] curl error: ' . curl_error($ch) . ' url=' . $url);
        }

    // Close the cURL session
        curl_close($ch);

        return $response;
    }
}
