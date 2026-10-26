<?php

if (!function_exists('showFileLink')) {
    function showFileLink(?string $file, string $folder): string
    {
        if (!$file) return '';

        return '
        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 border border-success-subtle mb-2">
            <span class="text-success small fw-medium">
                <small>
                    <svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill me-1" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    Tersimpan
                </small>
            </span>
            <a href="' . base_url('uploads/' . $folder . '/' . $file) . '"
               target="_blank"
               class="btn btn-sm btn-link text-decoration-none p-0 text-primary small fw-semibold">
                <small>Lihat Berkas →</small>
            </a>
        </div>';
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(int $status): string
    {
        $map = [
            1 => ['Layak',       'success'],
            2 => ['Tidak Layak', 'danger'],
        ];
        [$text, $color] = $map[$status] ?? ['Pending', 'warning'];
        return "<span class=\"badge bg-{$color}\">{$text}</span>";
    }
}