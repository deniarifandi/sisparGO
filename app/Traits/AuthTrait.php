<?php

namespace App\Traits;

trait AuthTrait
{
    protected function getNik(): ?string
    {
        return session()->get('progress_nik');
    }

    protected function requireNik(): string|false
    {
        $nik = $this->getNik();

        if (!$nik) {
            session()->setFlashdata('error', 'Sesi habis. Silakan cek status kembali.');
            return false;
        }

        return $nik;
    }
}