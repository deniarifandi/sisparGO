<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!function_exists('generateQr')) {
    function generateQr(string $data): string
    {
        $dir = FCPATH . 'uploads/qrcode/';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = md5($data) . '.png';
        $path = $dir . $filename;

        if (!is_file($path)) {
            $qrCode = new QrCode(
                data: $data,
                size: 300,
                margin: 10
            );

            $writer = new PngWriter();
            $writer->write($qrCode)->saveToFile($path);
        }

        return base_url('uploads/qrcode/' . $filename);
    }
}