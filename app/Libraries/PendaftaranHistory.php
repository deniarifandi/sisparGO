<?php
namespace App\Libraries;
class PendaftaranHistory
{
    public static function record($db, ?array $before, array $after, string $action, string $actor, ?string $requestId=null, ?string $hash=null): void
    {
        $ok=$db->table('pengajuan_pendaftaran_riwayat')->insert([
            'pengajuan_id'=>$after['id'],'aksi'=>$action,'status_awal'=>$before['status'] ?? null,
            'status_akhir'=>$after['status'],'catatan'=>$action==='kirim_ulang' ? ($before['catatan_verifikasi'] ?? null) : ($after['catatan_verifikasi'] ?? null),
            'aktor'=>$actor,'request_id'=>$requestId,'payload_hash'=>$hash,
            'data_sebelum'=>$before ? json_encode($before) : null,
            'data_sesudah'=>json_encode($after),'created_at'=>date('Y-m-d H:i:s'),
        ]);
        if(!$ok) throw new \RuntimeException('History insert failed');
    }
}
