<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class AppVersion extends BaseController
{
    /**
     * Public version manifest consumed by the Sisparma mobile app.
     *
     * Update these values when publishing a release. Version codes must match
     * Android's monotonically increasing versionCode (Flutter build number).
     */
    public function index(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'latest_version' => '2.0.0',
                'latest_version_code' => 17,
                'minimum_version_code' => 10,
                'store_url' => 'https://play.google.com/store/apps/details?id=com.sinarumi.flutter_sisparma_jukir',
                'message' => 'Sisparma versi terbaru tersedia. Perbarui aplikasi untuk mendapatkan fitur dan perbaikan terbaru.',
            ],
        ]);
    }
}
