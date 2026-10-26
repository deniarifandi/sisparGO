<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// AUTHENTICATION (PUBLIC)
// ============================================================
    $routes->get('login', 'Home::login');
    $routes->post('auth/login', 'Home::loginPost', ['filter' => 'csrf']);
    $routes->post('logout', 'Home::logout');
    $routes->get('privacypolicy', 'Home::privacypolicy');
    $routes->get('legal-documents', 'LegalDocuments::index');
    $routes->post('legal-documents/save', 'LegalDocuments::save', ['filter' => 'csrf']);
    // Halaman hasil dibuat publik agar redirect setelah POST tidak tertahan authGuard.
    $routes->match(['get', 'post'], 'import/anggota-foto-json', 'ImportController::importAnggotaFotoJson');
    $routes->get('import/anggota-foto-json/hasil', 'ImportController::hasilImportAnggotaFotoJson');

// ============================================================
// PUBLIC & GUEST DASHBOARD / REGISTRATION
// ============================================================
    $routes->get('publicdashboard', 'Home::publicdashboard');
    $routes->get('titpar/public', 'Home::titparPublic');
    $routes->get('anggota/public', 'Home::anggotaPublic');
    $routes->get('whatsapp', 'whatsapp::send');
    $routes->get('app', 'AppController::index');

    $routes->group('register', function ($routes) {
        $routes->get('/', 'RegisterController::landing');
        $routes->get('form', 'RegisterController::register');
        $routes->post('save', 'RegisterController::registersave');
        $routes->post('update', 'RegisterController::update');
        $routes->get('progress', 'RegisterController::progressView');
        $routes->post('progress', 'RegisterController::progress');
        $routes->get('logout', 'RegisterController::logout');
        $routes->post('save-catatan', 'KtaController::saveCatatan');

        // Public Registration: Titik Parkir
        $routes->group('titpar', function ($routes) {
            $routes->get('add', 'TitparController::add');
            $routes->get('edit/(:num)', 'TitparController::edit/$1');
            $routes->post('save', 'TitparController::save');
            $routes->post('save/(:num)', 'TitparController::save/$1');
            $routes->get('delete/(:num)', 'TitparController::delete/$1');
        });

        // Public Registration: Anggota
        $routes->group('anggota', function ($routes) {
            $routes->get('add', 'AnggotaController::add');
            $routes->get('edit/(:num)', 'AnggotaController::edit/$1');
            $routes->post('save', 'AnggotaController::save');
            $routes->post('save/(:num)', 'AnggotaController::save/$1');
            $routes->get('delete/(:num)', 'AnggotaController::delete/$1');
        });
    });

    $routes->group('kendala', function ($routes) {
        $routes->post('approve', 'SisparmaApp::approve');
        $routes->post('reject', 'SisparmaApp::reject');
        $routes->post('updatePotongan', 'SisparmaApp::updatePotongan');
        $routes->get('getvalist/(:num)', 'SisparmaApp::getvalist/$1');
        $routes->post('upload', 'SisparmaApp::upload');
        $routes->get('getdata/(:num)', 'SisparmaApp::getdata/$1');
        $routes->get('history/(:num)', 'SisparmaApp::history/$1');
    });
    $routes->get('kendalaindex', 'SisparmaApp::kendalaindex');
    $routes->post('kendaladata', 'SisparmaApp::kendaladata');
    $routes->get('kendalaadmin', 'SisparmaApp::kendalaAdminForm');
    $routes->post('kendalaadminsave', 'SisparmaApp::kendalaAdminSave');

// ============================================================
// BACKOFFICE & PROTECTED ROUTES (REQUIRES AUTHGUARD)
// ============================================================
$routes->group('', ['filter' => 'authGuard'], function ($routes) {
    
    // HOME / DASHBOARD
    $routes->get('/', 'Home::index');
    $routes->get('commandcenter', 'Home::commandcenter');
    $routes->get('titpar/count', 'Home::titparCount');
    $routes->get('pendapatanhariini', 'Home::getPendapatanHariIni');
    $routes->get('targetsetoranbulanan', 'Home::targetSetoranBulanan');
    $routes->get('targetsetoranbulanankecamatan/(:num)', 'Home::targetSetoranBulananKecamatan/$1');
    $routes->get('getpotensicek', 'Home::getPotensiCek');

    // Maintenance: pull source code from the configured Git repository.
    $routes->get('maintenance/git-pull', 'SystemMaintenance::gitPull');
    $routes->post('maintenance/git-pull', 'SystemMaintenance::runGitPull', ['filter' => 'csrf']);

    // USER MANAGEMENT
    $routes->group('user', function ($routes) {
        $routes->get('list', 'Home::userlist');
        $routes->post('data', 'Home::userdata');
        $routes->get('create', 'Home::usercreate');
        $routes->post('store', 'Home::userstore');
        $routes->get('edit/(:num)', 'Home::useredit/$1');
        $routes->post('update/(:num)', 'Home::userupdate/$1');
        $routes->post('userdelete/(:num)', 'Home::userdelete/$1');
        $routes->get('login-attempts', 'Home::loginAttempts');
        $routes->post('login-attempts/reset', 'Home::resetLoginAttempts', ['filter' => 'csrf']);
    });

    // VA OWNER
    $routes->group('va-owner', function ($routes) {
        $routes->get('/', 'VaOwnerController::index');
        $routes->get('create', 'VaOwnerController::create');
        $routes->post('store', 'VaOwnerController::store');
        $routes->get('edit/(:num)', 'VaOwnerController::edit/$1');
        $routes->post('update/(:num)', 'VaOwnerController::update/$1');
        $routes->get('delete/(:num)', 'VaOwnerController::delete/$1');
        $routes->get('undelete/(:num)', 'VaOwnerController::undelete/$1');
        $routes->post('data', 'VaOwnerController::data');
        $routes->get('getaccesstoken', 'VaOwnerController::getaccesstoken');
        $routes->get('get_titpar_anggota/(:num)', 'VaOwnerController::getTitparWithAnggota/$1');
        $routes->get('other-titpar/(:num)/(:num)','VaOwnerController::getOtherTitpar/$1/$2');
    });

    $routes->get('updatingberita/(:num)', 'VaOwnerController::updatingberita/$1');
    $routes->get('sendupdate/(:num)/(:num)', 'VaOwnerController::sendupdate/$1/$2');

    // TRANSAKSI
    $routes->group('transaksi', function ($routes) {
        $routes->get('/', 'Transaksi::index');
        $routes->get('create', 'Transaksi::create');
        $routes->post('store', 'Transaksi::store');
        $routes->get('edit/(:num)', 'Transaksi::edit/$1');
        $routes->post('update/(:num)', 'Transaksi::update/$1');
        $routes->get('delete/(:num)', 'Transaksi::delete/$1');
        $routes->post('data', 'Transaksi::data');
        $routes->get('gettransaksibulanan/(:num)/(:num)', 'Transaksi::getTransaksiBulanan/$1/$2');
        $routes->get('send/(:any)', 'Transaksi::send_konfirmasi/$1');
        $routes->get('invoice/(:segment)', 'Transaksi::invoice/$1');
        $routes->get('va/(:any)','Transaksi::transaksiVa/$1');
        // $routes->get('transaksi/va/(:any)','Transaksi::transaksiVa/$1');
    });

    // POTENSI & TAGIHAN
    $routes->group('potensi', function ($routes) {
        $routes->get('/', 'Potensi::index');
        $routes->get('create/(:num)', 'Potensi::create/$1');
        $routes->post('store/(:num)', 'Potensi::store/$1');
        $routes->get('edit/(:num)', 'Potensi::edit/$1');
        $routes->post('update/(:num)', 'Potensi::update/$1');
        $routes->get('riwayat/edit/(:num)', 'Potensi::editRiwayat/$1');
        $routes->post('riwayat/update/(:num)', 'Potensi::updateRiwayat/$1');
        $routes->post('riwayat/delete/(:num)', 'Potensi::deleteRiwayat/$1');
        $routes->get('delete/(:num)', 'Potensi::delete/$1');
        $routes->post('data', 'Potensi::data');
        $routes->get('send/(:any)', 'Potensi::send_konfirmasi/$1');
        $routes->get('invoice/(:segment)', 'Potensi::invoice/$1');
        $routes->get('realisasi', 'Potensi::realisasi');
        $routes->post('datatagihan', 'Potensi::datatagihan');
        $routes->get('realisasi/tahunan', 'Potensi::realisasitahunan');
        $routes->post('datatagihan/tahunan', 'Potensi::datatagihantahunan');
        $routes->get('export', 'Potensi::export');
        $routes->get('export-resume', 'Potensi::exportResume');
    });

    // TIKET
    $routes->group('tiket', function ($routes) {
        $routes->get('/', 'Tiket::index');
        $routes->post('data', 'Tiket::data');
        $routes->get('pesan/(:any)', 'Tiket::pesan/$1');
        $routes->post('store', 'Tiket::store');
        $routes->post('update/(:any)', 'Tiket::update/$1');
        $routes->get('histori', 'Tiket::histori');
        $routes->post('datahistori', 'Tiket::datahistori');
        $routes->get('edit/(:any)', 'Tiket::edit/$1');
        $routes->get('print/(:any)', 'Tiket::print/$1');
    });

    // ANGGOTA
    $routes->group('anggota', function ($routes) {
        $routes->get('/', 'Anggota::index');
        $routes->get('detail/(:num)', 'Anggota::detail/$1');
        $routes->get('detail-data/(:num)', 'Anggota::detailData/$1');
        $routes->post('data', 'Anggota::data');
        $routes->post('password/(:num)', 'Anggota::resetPassword/$1', ['filter' => 'csrf']);
        $routes->get('create', 'Anggota::create');
        $routes->get('wilayah/provinsi', 'Anggota::provinsi');
        $routes->get('wilayah/kabupaten/(:segment)', 'Anggota::kabupaten/$1');
        $routes->get('wilayah/kecamatan/(:segment)', 'Anggota::kecamatan/$1');
        $routes->get('wilayah/kelurahan/(:segment)', 'Anggota::kelurahan/$1');
        $routes->post('store', 'Anggota::store');
        $routes->get('edit/(:num)', 'Anggota::edit/$1');
        $routes->post('update/(:num)', 'Anggota::update/$1');
        $routes->get('delete/(:num)', 'Anggota::delete/$1');
        $routes->get('undelete/(:num)', 'Anggota::undelete/$1');
    });

    // TITPAR
    $routes->group('titpar', function ($routes) {
        $routes->get('/', 'Titpar::index');
        $routes->post('data', 'Titpar::data');
        $routes->get('create', 'Titpar::create');
        $routes->post('store', 'Titpar::store');
        $routes->get('edit/(:num)', 'Titpar::edit/$1');
        $routes->post('update/(:num)', 'Titpar::update/$1');
        $routes->get('delete/(:num)', 'Titpar::delete/$1');
        $routes->get('undelete/(:num)', 'Titpar::undelete/$1');

        $routes->get('manage/(:num)', 'Titpar::manage/$1');
        $routes->get('print-penunjukan/(:num)', 'Titpar::printPenunjukan/$1');
        $routes->post('upload-penunjukan/(:num)', 'Titpar::uploadPenunjukan/$1', ['filter' => 'csrf']);
        $routes->get('surat-penunjukan/(:num)', 'Titpar::suratPenunjukan/$1');
        $routes->get('print-kta/(:num)', 'Titpar::printKta/$1');
        $routes->post('update-masa-berlaku/(:num)', 'Titpar::updateMasaBerlaku/$1');
        $routes->post('update-masa-berlaku-anggota/(:num)', 'Titpar::updateMasaBerlakuAnggota/$1');

        $routes->post(
            'add-koordinator/(:num)',
            'Titpar::addKoordinator/$1'
        );

        $routes->post(
            'add-anggota/(:num)',
            'Titpar::addAnggota/$1'
        );

        $routes->get('search-anggota/(:num)', 'Titpar::searchAnggota/$1');
        $routes->get('search-koordinator/(:num)', 'Titpar::searchKoordinator/$1');
        $routes->post('remove-koordinator/(:num)','Titpar::removeKoordinator/$1');
        $routes->post('remove-anggota/(:num)','Titpar::removeAnggota/$1');

        $routes->post('assign-anggota/(:num)','Titpar::assignAnggota/$1');
        $routes->post('change-coordinator/(:num)','Titpar::changeCoordinator/$1');

        $routes->post('replace-koordinator/(:num)','Titpar::replaceKoordinator/$1');

        $routes->get('search-koordinator-replacement/(:num)','Titpar::searchKoordinatorReplacement/$1');
        $routes->post('update-shift/(:num)', 'Titpar::updateShift/$1');
         $routes->post(
            'make-koordinator/(:num)',
            'Titpar::makeKoordinator/$1'
        );
    });

    // TITPAR GRUP
    $routes->group('titpargrup', function ($routes) {
        $routes->get('/', 'Titpargrup::index');
        $routes->post('data', 'Titpargrup::data');
        $routes->get('create', 'Titpargrup::create');
        $routes->post('store', 'Titpargrup::store');
        $routes->get('edit/(:num)', 'Titpargrup::edit/$1');
        $routes->post('update/(:num)', 'Titpargrup::update/$1');
        $routes->get('delete/(:num)', 'Titpargrup::delete/$1');
    });

    // KENDALA / SISPARMA APP INTERN

    // ADMIN PERIJINAN / KTA (NAMESPACE: Admin)
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('maintenance/hash-legacy-passwords', 'MaintenanceController::hashLegacyPasswords');
        $routes->get('pendaftaran-anggota', 'PendaftaranAnggotaController::index');
        $routes->post('pendaftaran-anggota/perbaikan/(:num)', 'PendaftaranAnggotaController::requestCorrection/$1', ['filter'=>'csrf']);
        $routes->post('pendaftaran-anggota/approve/(:num)', 'PendaftaranAnggotaController::approve/$1', ['filter' => 'csrf']);
        $routes->post('pendaftaran-anggota/reject/(:num)', 'PendaftaranAnggotaController::reject/$1', ['filter' => 'csrf']);
        $routes->get('pendaftaran-anggota/detail/(:num)', 'PendaftaranAnggotaController::detail/$1');
        $routes->get('pendaftaran-anggota/dokumen/(:num)/(:segment)', 'PendaftaranAnggotaController::document/$1/$2');
        
        $routes->group('register', function ($routes) {
            $routes->get('/', 'RegisterController::index');
            $routes->get('detail/(:num)', 'RegisterController::detail/$1');
            $routes->post('update-informasi', 'RegisterController::updateInformasi');
            $routes->post('update-foto', 'RegisterController::updateFoto');
            $routes->post('approve', 'RegisterController::approveMember');
            $routes->get('print-penunjukan/(:num)', 'RegisterController::printPenunjukan/$1');
        });

        $routes->group('titpar', function ($routes) {
            $routes->get('detail/(:num)', 'TitparController::detail/$1');
            $routes->post('update/(:num)', 'TitparController::update/$1');
            $routes->post('kondisi/(:num)', 'TitparController::updateKondisi/$1');
            $routes->post('potensi/(:num)', 'TitparController::updatePotensi/$1');
            $routes->post('upload/(:num)', 'TitparController::updateFoto/$1');
            $routes->get('print/(:num)', 'TitparController::print/$1');
        });

        $routes->group('anggota', function ($routes) {
            $routes->get('detail/(:num)', 'AnggotaController::detail/$1');
            $routes->post('update/(:num)', 'AnggotaController::update/$1');
            $routes->post('upload/(:num)', 'AnggotaController::updateFoto/$1');
        });
        $routes->group('anggota-perubahan', function ($routes) {
            $routes->get('/', 'AnggotaPerubahanController::index');
            $routes->post('approve/(:num)', 'AnggotaPerubahanController::approve/$1');
            $routes->post('reject/(:num)', 'AnggotaPerubahanController::reject/$1');
            $routes->post('rotate-file/(:segment)', 'AnggotaPerubahanController::rotateFile/$1');
        });
        $routes->group('kta-perpanjangan', function ($routes) {
            $routes->get('/', 'KtaPerpanjanganController::index');
            $routes->post('data', 'KtaPerpanjanganController::data');
            $routes->get('members/(:num)', 'KtaPerpanjanganController::members/$1');
            $routes->post('approve/(:num)', 'KtaPerpanjanganController::approve/$1');
            $routes->post('reject/(:num)', 'KtaPerpanjanganController::reject/$1');
            $routes->post('rotate/(:num)/(:segment)', 'KtaPerpanjanganController::rotate/$1/$2');
            $routes->post('rotate-file/(:segment)', 'KtaPerpanjanganController::rotateFile/$1');
        });
    });

    // BACKOFFICE KTA
    $routes->post('register/save-catatan', 'Admin\RegisterController::saveCatatan'); // Admin context fallback
    $routes->post('kta/data', 'Admin\RegisterController::data');
    $routes->group('perijinan', function ($routes) {
        $routes->get('/', 'RegisterController::admin');
    });
    $routes->post('kta/registeranggota', 'KtaController::registerAnggota');
    $routes->post('kta/approve', 'KtaController::approve');
    $routes->post('kta/reject', 'KtaController::reject');
    $routes->post('kta/penunjukan-upload', 'KtaUploadController::uploadPenunjukan');
    $routes->get('preview/penunjukan/(:any)', 'PreviewController::penunjukan/$1');

    // CHAT (BACKEND INTERFACE)
    $routes->group('chat-room', function ($routes) {
        $routes->get('/', 'ChatRoom::index');
        $routes->post('data', 'ChatRoom::data');
        $routes->get('(:num)', 'ChatMessage::index/$1');
        $routes->get('(:num)/messages', 'ChatMessage::fetch/$1');
        $routes->post('send', 'ChatMessage::send');
        $routes->get('new', 'ChatRoom::new');
    });

    // IMPORT
    $routes->get('import', 'ImportController::import');
    $routes->post('import-csv', 'ImportController::importCsv');
    $routes->get('import/photos', 'ImportController::downloadAllPhotos');
    $routes->get('import/sync-anggota-foto', 'ImportController::syncAnggotaFoto');

    // BANK JATIM API INTEGRATION
    $routes->group('api/jatim', function ($routes) {
        $routes->get('getaccesstoken', 'JatimController::get_access_token');
        $routes->get('signatureaccesstoken', 'JatimController::signature_access_token');
        $routes->get('getsignature', 'Jatim::getSignature');
        $routes->get('inquiry-va', 'Jatim::inquiryva');
        $routes->get('create-va', 'Jatim::createva');
    });
});

// ============================================================
// MOBILE & THIRD-PARTY API ENDPOINTS (BYPASS AUTHGUARD FOR TOKEN/WEBHOOK)
// ============================================================
$routes->group('api', function ($routes) {
    $routes->get('app/version', 'AppVersion::index');
    $routes->get('legal-documents', 'LegalDocuments::apiIndex');
    $routes->post('pendaftaran-anggota/perbaikan/data', 'PengajuanPendaftaran::revisionData');
    $routes->post('pendaftaran-anggota/perbaikan', 'PengajuanPendaftaran::revise');
    $routes->post('pendaftaran-anggota/status', 'PengajuanPendaftaran::status');
    $routes->post('pendaftaran-anggota/cek-nik', 'PengajuanPendaftaran::checkNik');
    $routes->post('pendaftaran-anggota', 'PengajuanPendaftaran::store');
    // Public geographic reference data for registration (no member data).
    $routes->get('wilayah/provinsi', 'Anggota::provinsi');
    $routes->get('wilayah/kabupaten/(:segment)', 'Anggota::kabupaten/$1');
    $routes->get('wilayah/kecamatan/(:segment)', 'Anggota::kecamatan/$1');
    $routes->get('wilayah/kelurahan/(:segment)', 'Anggota::kelurahan/$1');

    // App Authentication & Core Data
    $routes->post('loginapp', 'Home::loginApp');
    $routes->post('loginapp/ganti-password-awal', 'Home::changeInitialPassword');
    $routes->get('listTransaksiApp/(:num)', 'SisparmaApp::listTransaksiApp/$1');
    $routes->get('listTagihanApp/(:num)', 'SisparmaApp::listTagihanApp/$1');
    $routes->get('detailVaApp/(:num)', 'SisparmaApp::detailVaApp/$1');
    $routes->get('transaksi/va/(:num)/potensi-realisasi', 'PotensiRealisasiApi::bulanan/$1');
    $routes->get('transaksi/va/(:num)/potensi-realisasi/tahunan', 'PotensiRealisasiApi::tahunan/$1');
    $routes->get('detailAnggotaApp/(:num)', 'SisparmaApp::detailAnggotaApp/$1');
    $routes->get('detailTitparApp/(:num)', 'SisparmaApp::detailTitparApp/$1');
    $routes->get('targetSetoranApp/(:num)', 'SisparmaApp::targetSetoranApp/$1');
    $routes->get('kekuranganTagihanApp/(:num)', 'SisparmaApp::kekuranganTagihanApp/$1');
    $routes->get('profilApp/(:num)', 'SisparmaApp::profilApp/$1');
    $routes->get('kta/perpanjangan/(:num)', 'SisparmaApp::listPerpanjanganKta/$1');
    $routes->get('kta/perpanjangan/(:num)/members', 'SisparmaApp::anggotaPerpanjanganKta/$1');
    $routes->get('anggotaAktifApp', 'SisparmaApp::anggotaAktifApp');
    $routes->post('kta/perpanjangan/(:num)', 'SisparmaApp::ajukanPerpanjanganKta/$1');
    $routes->get('kta/perpanjangan/(:num)/status', 'SisparmaApp::statusPerpanjanganKta/$1');
    $routes->post('profilApp/(:num)/perubahan', 'SisparmaApp::ajukanPerubahanProfil/$1');
    $routes->get('profilApp/(:num)/perubahan', 'SisparmaApp::statusPerubahanProfil/$1');
    $routes->get('ktaAnggotaApp/(:num)', 'Titpar::ktaAnggotaApi/$1');
    $routes->get('ktaProfilApp/(:num)', 'Titpar::ktaProfilApi/$1');

    // KTA Upload API
    $routes->post('upload/(:segment)', 'KtaUploadController::upload/$1');
    $routes->group('upload', function ($routes) {
        $routes->post('kesanggupan', 'KtaUploadController::kesanggupan');
        $routes->post('keberatan', 'KtaUploadController::keberatan');
        $routes->post('denah', 'KtaUploadController::denah');
        $routes->post('kk', 'KtaUploadController::kk');
        $routes->post('berita_acara', 'KtaUploadController::uploadBeritaAcara');
        $routes->post('penunjukan', 'KtaUploadController::uploadPenunjukan');
    });

    // Internal Messenger API
    $routes->post('device-token', 'api\DeviceToken::register', ['filter' => 'authToken']);
    $routes->post('chat/start', 'ChatRoom::start', ['filter' => 'authToken']);
    $routes->get('chat/unread', 'api\ChatMessage::unread', ['filter' => 'authToken']);
    $routes->get('chat/unread/(:num)', 'api\ChatMessage::unread/$1', ['filter' => 'authToken']);
    $routes->get('chat/(:num)/messages', 'api\ChatMessage::messages/$1', ['filter' => 'authToken']);
    $routes->post('chat/send', 'api\ChatMessage::send', ['filter' => 'authToken']);

    // Webhook Callback Core Bank Jatim (VA Notification)
    $routes->post('v1.0/transfer-va/va-notify', 'Sisparma::va_notify');
});

$routes->post('chat-room/admin-start', 'ChatRoom::adminStart');
$routes->get('chat-room/search-client', 'ChatRoom::searchClient');
