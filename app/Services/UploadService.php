<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class UploadService
{
    // Default allowed mime types
    private array $allowedMime = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/pdf'
    ];

    // Default max size (3 MB)
    private int $maxSize = 3 * 1024 * 1024;

    // -------------------------------------------------------
    // Configuration (chainable)
    // -------------------------------------------------------

    public function imagesOnly(): static
    {
        $this->allowedMime = [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];
        return $this;
    }

    public function maxSize(int $bytes): static
    {
        $this->maxSize = $bytes;
        return $this;
    }

    // -------------------------------------------------------
    // Main method
    // -------------------------------------------------------

    /**
     * Handle a single file upload.
     *
     * @return array{success: bool, message: string, filename: string|null}
     */
    public function handle(
        ?UploadedFile $file,
        string $folder,
        string $oldFilename = ''
    ): array {

        // No file uploaded → skip (not an error)
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->ok(null);
        }

        // File exists but invalid
        if (!$file->isValid()) {
            return $this->fail($file->getErrorString());
        }

        // Check mime type
        if (!in_array($file->getMimeType(), $this->allowedMime)) {
            return $this->fail("Format file tidak didukung. Gunakan JPG, PNG, PDF" .
                (in_array('application/pdf', $this->allowedMime) ? ', atau PDF.' : '.'));
        }

        // Check file size
        if ($file->getSize() > $this->maxSize) {
            $mb = $this->maxSize / 1024 / 1024;
            return $this->fail("Ukuran file maksimal {$mb} MB.");
        }

        // Create folder if not exists
        $path = FCPATH . 'uploads/' . trim($folder, '/') . '/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Delete old file if exists
        if ($oldFilename && is_file($path . $oldFilename)) {
            @unlink($path . $oldFilename);
        }

        // Move new file
        $newName = $file->getRandomName();
        $file->move($path, $newName);

        return $this->ok($newName);
    }

    // -------------------------------------------------------
    // Delete a file
    // -------------------------------------------------------

    public function delete(string $folder, string $filename): void
    {
        $path = FCPATH . 'uploads/' . trim($folder, '/') . '/' . $filename;
        if ($filename && is_file($path)) {
            @unlink($path);
        }
    }

    // -------------------------------------------------------
    // Response helpers
    // -------------------------------------------------------

    private function ok(?string $filename): array
    {
        return ['success' => true, 'message' => '', 'filename' => $filename];
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'message' => $message, 'filename' => null];
    }
}