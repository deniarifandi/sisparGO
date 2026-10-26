<?php
namespace App\Libraries;

class RegistrationAccount
{
    public static function temporaryPassword(): string
    {
        $words=explode(' ', 'awan angin embun hujan pelangi langit bulan bintang mentari senja fajar pagi malam siang samudra laut pantai pasir ombak karang sungai danau telaga bukit gunung lembah hutan rimba taman kebun sawah ladang padi jagung kelapa mangga jambu jeruk apel nanas pepaya pisang durian rambutan salak duku sirsak alpukat melon semangka bayam kangkung wortel tomat cabai labu timun kacang kedelai melati mawar anggrek kenanga cempaka teratai kembang bunga daun ranting akar batang tunas benih bambu cemara pinus jati mahoni beringin merpati elang rajawali kutilang kenari bangau camar kupu lebah semut jangkrik belalang kunang rusa kijang kancil kelinci gajah harimau macan singa beruang panda kuda sapi kerbau domba kambing ayam bebek angsa ikan gurami nila lele patin bandeng tuna udang kepiting kerang mutiara intan berlian emas perak tembaga besi baja batu kayu tanah air api cahaya bayang pijar kilau lentera pelita obor lilin warna biru hijau merah ungu jingga putih hitam kuning cokelat nila rumah pintu jendela atap teras pagar meja kursi lemari bantal selimut kain benang jarum kertas buku pena tinta pensil kuas lukisan gambar lagu nada irama suling seruling kendang gong gamelan gitar biola piano cerita kisah dongeng pantun puisi sajak kata bahasa ilmu angka hitung jalan jembatan lorong dermaga pelabuhan perahu kapal layar roda sepeda kereta pesawat sayap bendera payung tas koper topi sepatu sandal gelang kalung cincin pita simpul jaring tali jala pancing dayung layar kompas peta arah utara selatan timur barat tengah damai ramah ceria riang gembira tenang sabar santun sopan tulus jujur berani tangguh kuat lincah gesit cermat tekun rajin bijak hangat sejuk segar harum manis gurih renyah lembut halus terang indah elok cantik tampan rapi bersih luas lapang tinggi panjang bulat bundar kecil besar tebal tipis dalam dekat jauh cepat lambat kokoh subur makmur damai cerah');
        $words=array_values(array_unique($words));
        $a=random_int(0,count($words)-1);
        do { $b=random_int(0,count($words)-1); } while($a===$b);
        return ucfirst($words[$a]).ucfirst($words[$b]).str_pad((string)random_int(0,9999),4,'0',STR_PAD_LEFT);
    }

    // Caller owns the transaction and removes newly copied files if it rolls back.
    public static function create($db, array $application, int $adminId, array &$copied, array &$sourcesToDelete = []): array
    {
        $existing=$db->query('SELECT anggota_id FROM '.$db->prefixTable('dishub_anggota').' WHERE anggota_noktp = ? FOR UPDATE',[$application['nik']])->getRowArray();
        if($existing) throw new \DomainException('NIK sudah terdaftar sebagai anggota. Persetujuan dibatalkan.');
        $religions=['Islam'=>1,'Katolik'=>2,'Kristen'=>3,'Hindu'=>4,'Budha'=>5,'Kong Hu Cu'=>6];
        $password=self::temporaryPassword();
        $member=[
            'pendaftaran_id'=>$application['id'], 'password'=>password_hash($password,PASSWORD_DEFAULT),
            'must_change_password'=>1, 'anggota_noreg'=>$application['nomor_pengajuan'],
            'anggota_nama'=>$application['nama_lengkap'],'anggota_noktp'=>$application['nik'],
            'nomor_kk'=>$application['nomor_kk'], 'anggota_nohp'=>$application['no_handphone'] ?? '',
            'anggota_tgllhr'=>$application['tanggal_lahir'], 'anggota_tmplhr'=>$application['tempat_lahir'] ?? '',
            'anggota_jnsklm'=>match($application['jenis_kelamin']) {'Laki-laki'=>'L','Perempuan'=>'P',default=>null},
            'anggota_alamat'=>$application['alamat_lengkap'], 'anggota_klrhnid'=>$application['kelurahan_id'],
            'anggota_peker'=>$application['pekerjaan'] ?? '', 'anggota_pente'=>$application['pendidikan_terakhir'] ?? '',
            'anggota_staagamaid'=>$religions[$application['agama'] ?? ''] ?? null,
            'anggota_status'=>3, 'anggota_cid'=>$adminId, 'anggota_cdate'=>date('Y-m-d H:i:s'),
        ];
        $destinations = [
            'pas_foto' => ['column' => 'anggota_foto', 'folder' => 'anggota_foto'],
            'dokumen_ktp' => ['column' => 'foto_ktp', 'folder' => 'foto_ktp'],
            'dokumen_kk' => ['column' => 'foto_kk', 'folder' => 'foto_kk'],
        ];
        foreach($destinations as $from => $destination) {
            if(empty($application[$from])) continue;
            $folder=realpath(WRITEPATH.'uploads/pendaftaran_anggota');
            $source=$folder ? realpath($folder.DIRECTORY_SEPARATOR.basename($application[$from])) : false;
            if(!$source || dirname($source)!==$folder || !is_file($source)) throw new \DomainException('Dokumen pengajuan tidak ditemukan. Minta pendaftar memperbaiki dokumennya.');
            $targetFolder=FCPATH.'uploads/'.$destination['folder'];
            if(!is_dir($targetFolder) && !mkdir($targetFolder,0755,true) && !is_dir($targetFolder)) throw new \RuntimeException('Photo directory failed');
            $name='reg_'.bin2hex(random_bytes(16)).'.'.strtolower(pathinfo($source,PATHINFO_EXTENSION));
            $target=$targetFolder.DIRECTORY_SEPARATOR.$name;
            $copied[]=$target;
            if(!copy($source,$target)) throw new \RuntimeException('Photo copy failed');
            $sourcesToDelete[]=$source;
            $member[$destination['column']]=$name;
        }
        if(!$db->table('dishub_anggota')->insert($member)) throw new \RuntimeException('Member insert failed');
        $id=$db->insertID();
        return ['user_id'=>(string)$id,'password'=>$password,'nomor_pengajuan'=>$application['nomor_pengajuan']];
    }
}
