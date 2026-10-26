<?php

namespace App\Controllers;

use App\Libraries\DataTable;
use DB;

class Home extends BaseController
{
    private const REMEMBER_COOKIE = 'sisparma_remember';
    private const REMEMBER_DAYS = 30;

    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
    }

    /**
     * Brute-force throttle: locks login when `login.maxAttempts` failed tries
     * for this IP or this username happen within `login.lockoutMinutes`.
     */
    private function loginIsLocked(string $username): bool
    {
        $db      = \Config\Database::connect();
        $minutes = max(1, (int) env('login.lockoutMinutes', 15));
        $max     = max(1, (int) env('login.maxAttempts', 5));
        $since   = date('Y-m-d H:i:s', time() - $minutes * 60);

        $count = $db->table('auth_login_attempts')
            ->where('attempted_at >=', $since)
            ->where('successful', 0)
            ->groupStart()
                ->where('ip_address', $this->request->getIPAddress())
                ->orWhere('username', $username)
            ->groupEnd()
            ->countAllResults();

        return $count >= $max;
    }

    private function recordLoginAttempt(string $username, bool $success): void
    {
        $db = \Config\Database::connect();
        $db->table('auth_login_attempts')->insert([
            'username'    => $username ?: null,
            'ip_address'  => $this->request->getIPAddress(),
            'successful'  => $success ? 1 : 0,
            'attempted_at'=> date('Y-m-d H:i:s'),
        ]);
    }

    public function loginAttempts()
    {
        if (!session('isLoggedIn') || !in_array(session('role'), [0, '0'], true)) return redirect()->to('/')->with('error', 'Hanya Superadmin yang dapat mengelola pembatasan login.');
        $rows = $this->db->table('auth_login_attempts')
            ->orderBy('attempted_at', 'DESC')->limit(300)->get()->getResultArray();
        $minutes = max(1, (int) env('login.lockoutMinutes', 15));
        $max = max(1, (int) env('login.maxAttempts', 5));
        return view('user/login_attempts', compact('rows', 'minutes', 'max'));
    }

    public function resetLoginAttempts()
    {
        if (!session('isLoggedIn') || !in_array(session('role'), [0, '0'], true)) return redirect()->to('/')->with('error', 'Hanya Superadmin yang dapat mengelola pembatasan login.');
        $scope = (string) $this->request->getPost('scope');
        $value = trim((string) $this->request->getPost('value'));
        $builder = $this->db->table('auth_login_attempts');
        if ($scope === 'username' && $value !== '') $builder->where('username', $value)->delete();
        elseif ($scope === 'ip' && $value !== '') $builder->where('ip_address', $value)->delete();
        elseif ($scope === 'all') $builder->emptyTable();
        else return redirect()->back()->with('error', 'Target reset tidak valid.');
        return redirect()->back()->with('success', 'Pembatasan login berhasil direset.');
    }

    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url());
        }

        // Show login page
        return view('login');
    }

    public function loginPost()
{
    $db = \Config\Database::connect();
    $session = session();

    $username = trim((string) $this->request->getPost('username'));
    $password = (string) $this->request->getPost('password');

    // Brute-force throttle
    if ($this->loginIsLocked($username)) {
        $session->setFlashdata('error', 'Terlalu banyak percobaan. Silakan coba lagi beberapa saat.');
        return redirect()->back()->withInput();
    }

    // Get user by username
    $user = $db->table('users')->where('username', $username)->get()->getRowArray();

    if (!$user || !password_verify($password, $user['password'])) {
        // Generic message to avoid username enumeration
        $this->recordLoginAttempt($username, false);
        $session->setFlashdata('error', 'Username atau password salah.');
        return redirect()->back()->withInput();
    }

    $this->recordLoginAttempt($username, true);

    // Prevent session fixation
    $session->regenerate();

    // Login success
    $session->set([
        'user_id'     => $user['user_id'],
        'username'    => $user['username'],
        'role'        => $user['role'],
        'nama'        => $user['nama'],
        'isLoggedIn'  => true
    ]);

    $response = redirect()->to(base_url());

    if ($this->request->getPost('remember') === '1') {
        $this->issueRememberToken((int) $user['user_id'], $response);
    } else {
        $this->revokeRememberToken($response);
    }

    return $response;
}

    private function issueRememberToken(int $userId, \CodeIgniter\HTTP\ResponseInterface $response): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + self::REMEMBER_DAYS * DAY;

        $db = \Config\Database::connect();
        $db->table('auth_remember_tokens')->insert([
            'user_id'    => $userId,
            'selector'   => $selector,
            'token_hash' => hash('sha256', $validator),
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
        ]);

        $response->setCookie(self::REMEMBER_COOKIE, $selector . ':' . $validator, $expiresAt, '', '/', '', $this->request->isSecure(), true, 'Lax');
    }

    private function revokeRememberToken(\CodeIgniter\HTTP\ResponseInterface $response): void
    {
        $cookie = (string) $this->request->getCookie(self::REMEMBER_COOKIE);
        $selector = explode(':', $cookie, 2)[0] ?? '';
        if (preg_match('/^[a-f0-9]{32}$/', $selector)) {
            \Config\Database::connect()->table('auth_remember_tokens')->where('selector', $selector)->delete();
        }
        $response->deleteCookie(self::REMEMBER_COOKIE, '', '/');
    }


public function loginApp(){

       // Allow only POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Method Not Allowed. Please use POST'
            ])->setStatusCode(405);
        }

        // Read JSON body
        $json = $this->request->getJSON(true);
        $username = $json['username'] ?? null;
        $password = $json['password'] ?? null;

        if (!is_string($username) || !is_string($password) || $username === '' || $password === '') {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Username and password are required',
            ])->setStatusCode(400);
        }

        try {
            // Connect to database
            $db = \Config\Database::connect();

            // Query user data
            $builder = $db->table('dishub_anggota');
            $builder->where('anggota_id', $username);
            $user = $builder->get()->getRowArray();

            // Brute-force throttle
            if ($this->loginIsLocked($username)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Too many attempts. Please try again later.',
                ])->setStatusCode(429);
            }

            if (!$user) {
                $this->recordLoginAttempt($username, false);
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid username or password.',
                ])->setStatusCode(401);
            }

            // Verify password. Support legacy plaintext rows and migrate to bcrypt.
            $stored = (string) $user['password'];
            $algo   = password_get_info($stored);
            $ok     = $algo['algo'] !== null
                ? password_verify($password, $stored)     // bcrypt row
                : hash_equals($stored, $password);        // legacy plaintext

            if (!$ok) {
                $this->recordLoginAttempt($username, false);
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid username or password.',
                ])->setStatusCode(401);
            }

            // Upgrade legacy plaintext / weak hash to current bcrypt on successful login
            if ($algo['algo'] === null || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $db->table('dishub_anggota')
                   ->where('anggota_id', $username)
                   ->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
            }

            $this->recordLoginAttempt($username, true);
            if (!empty($user['must_change_password'])) {
                return $this->response->setHeader('Cache-Control','no-store')->setStatusCode(403)->setJSON([
                    'status'=>false,'code'=>'PASSWORD_CHANGE_REQUIRED',
                    'message'=>'Ganti password sementara sebelum masuk ke aplikasi.',
                ]);
            }

            // Issue a per-member bearer token for authenticated mobile endpoints.
            $token = bin2hex(random_bytes(32));
            $tokenRow = $db->table('api_tokens')
                ->where('user_id', $user['anggota_id'])
                ->get()
                ->getRowArray();
            if ($tokenRow) {
                $db->table('api_tokens')
                    ->where('user_id', $user['anggota_id'])
                    ->update(['token' => $token]);
            } else {
                $db->table('api_tokens')->insert([
                    'user_id' => $user['anggota_id'],
                    'token' => $token,
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['anggota_id'],
                    'username' => $user['anggota_id'],
                    'nama' => $user['anggota_nama'] ?? '',
                    'token' => $token,
                ],
            ])->setStatusCode(200);

        } catch (DatabaseException $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
}

    public function changeInitialPassword()
    {
        $this->response->setHeader('Cache-Control','no-store');
        $json=$this->request->getJSON(true);
        $username=$json['username'] ?? null;
        $temporary=$json['temporary_password'] ?? null;
        $password=$json['new_password'] ?? null;
        if(!is_string($username) || !ctype_digit($username) || !is_string($temporary) || !is_string($password)) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Data password tidak valid.']);
        }
        if($this->loginIsLocked($username)) return $this->response->setStatusCode(429)->setJSON(['message'=>'Terlalu banyak percobaan. Coba lagi nanti.']);
        if(mb_strlen($password)<10 || strlen($password)>72 || $password===$temporary) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Gunakan password baru minimal 10 karakter, maksimal 72 byte, berbeda dari password sementara.']);
        }
        $db=db_connect();$db->transBegin();
        try {
            $row=$db->query('SELECT anggota_id,password,must_change_password FROM '.$db->prefixTable('dishub_anggota').' WHERE anggota_id = ? FOR UPDATE',[$username])->getRowArray();
            if(!$row || empty($row['must_change_password']) || !password_verify($temporary,$row['password'])) {
                $db->transRollback();$this->recordLoginAttempt($username,false);
                return $this->response->setStatusCode(401)->setJSON(['message'=>'ID atau password sementara tidak valid, atau password sudah diganti.']);
            }
            if(!$db->table('dishub_anggota')->where('anggota_id',$username)->update([
                'password'=>password_hash($password,PASSWORD_DEFAULT),'must_change_password'=>0,
            ]) || !$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Password update failed');
        } catch(\Throwable $error) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['message'=>'Password belum dapat diganti. Silakan coba lagi.']);
        }
        $this->recordLoginAttempt($username,true);
        return $this->response->setJSON(['message'=>'Password berhasil diganti. Silakan login dengan password baru.']);
    }

    public function logout()
    {
        $response = redirect()->to(base_url('login'));
        $this->revokeRememberToken($response);
        session()->destroy();
        return $response;
    }

    public function userlist()
    {
        return view('user/index');
    }

    public function userdata(){
          $db = \Config\Database::connect();
          
        $builder = $db->table('users')->select('*');
        if (session('role') > 0) {
            $builder->where('role >', session('role'));
            $builder->orWhere('user_id', session('user_id'));
        }
        
        
        // Columns to apply search on
        $columns = ['nama'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);   
    }

    public function usercreate(){
       if ((int) session('role') > 2) {
           return redirect()->to('/user/list')->with('error', 'Hanya Admin Utama ke atas yang dapat membuat user.');
       }
       return view('user/form', ['allowedRoles' => $this->allowedUserRoles()]);
    }

    private function allowedUserRoles(): array
    {
        $current = (int) session('role');
        if ($current > 2) return [];
        return array_values(array_filter([1, 2, 3, 4], static fn ($role) => $role > $current));
    }

    public function userstore(){
            if ((int) session('role') > 2) {
                return redirect()->to('/user/list')->with('error', 'Hanya Admin Utama ke atas yang dapat membuat user.');
            }
            $role = filter_var($this->request->getPost('role'), FILTER_VALIDATE_INT);
            if ($role === false || !in_array($role, $this->allowedUserRoles(), true)) {
                return redirect()->back()->withInput()->with('error', 'Role yang dipilih tidak boleh lebih tinggi dari role Anda.');
            }
            $db = \Config\Database::connect();
            $builder = $db->table('users');

            $data = [
                'username' => $this->request->getPost('username'),
                'nama' => $this->request->getPost('nama'),
                'role'     => $role,
            ];

            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $builder->insert($data);

            return redirect()->to('/user/list')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function useredit($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');

        $user = $builder->where('user_id', $id)->get()->getRowArray();

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        return view('user/form', ['user' => $user, 'allowedRoles' => $this->allowedUserRoles()]); // same form view, reused
    }

    public function userupdate($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $existing = $builder->where('user_id', $id)->get()->getRowArray();
        if (!$existing) return redirect()->to('/user/list')->with('error', 'User tidak ditemukan.');
        $role = filter_var($this->request->getPost('role'), FILTER_VALIDATE_INT);
        $sameRole = $role !== false && (int) $existing['role'] === $role;
        if ($role === false || (!$sameRole && !in_array($role, $this->allowedUserRoles(), true)) || ((int) $existing['role'] === 0 && (int) session('role') !== 0)) {
            return redirect()->back()->withInput()->with('error', 'Role yang dipilih tidak boleh lebih tinggi dari role Anda.');
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'nama'     => $this->request->getPost('nama'),
            'role'     => $role,
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $builder->where('user_id', $id)->update($data);

        return redirect()->to('/user/list')->with('success', 'User berhasil diupdate.');
    }

    public function userdelete($id)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('users');
        $builder->where('user_id', $id)->delete();

        session()->setFlashdata('success', 'Pengguna berhasil dihapus.');
        return redirect()->to(base_url('user/list'));
    }


    public function index(): string
    {
        return view('dashboard');
    }

    // ---- Add this method inside Home controller ----
    // Route: $routes->get('statistikpublik', 'Home::statistikPublik');
    public function statistikPublik()
    {
        // Titik parkir resmi -> reuses same status flag as titparCount()
        $titikParkir = $this->db->table('dishub_titpar')
            ->where('titpar_status', '3')
            ->countAllResults();

        // Jukir aktif -> adjust the where() to match your real "aktif" column/value
        // e.g. if there's a status column on dishub_anggota, filter on that instead.
        $jukirAktif = $this->db->table('dishub_anggota')
            ->countAllResults();

        // Jumlah kecamatan dari master wilayah Kota Bogor.
        $kecamatanTerlayani = $this->db->table('kelurahan')->select('kecamatan_id')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->countAllResults();

        // KTA / VA terbit -> reuses existing helper, excludes soft-deleted rows
        $ktaTerbit = $this->get_total_va();

        return $this->response->setJSON([
            'titik_parkir'        => $titikParkir,
            'jukir_aktif'         => $jukirAktif,
            'kecamatan_terlayani' => $kecamatanTerlayani,
            'kta_terbit'          => $ktaTerbit,
        ]);
    }

    // ---- Replace your existing publicdashboard() with this ----
    // Public page no longer needs pendapatan/target/persentase at all,
    // so don't even compute them here — one less place sensitive
    // revenue figures could leak from.

    public function titparPublic()
{
    $data = $this->db->table('dishub_titpar')
        ->select('titpar_namatempat, titpar_lokasi, titpar_partypeid, titpar_koderayon')
        // ->where('titpar_staapproveid', 1)
        ->where('titpar_status', 2)
        ->get()
        ->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $data
    ]);
}

public function anggotaPublic()
{
    $data = $this->db->table('dishub_anggota')
        ->select('anggota_noreg, anggota_nama, anggota_jukirtypeid')
        // ->where('anggota_staapproveid', 1)
        ->where('anggota_status', 2)
        ->where('deleted_at', null)
        ->get()
        ->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $data
    ]);
}
    public function publicdashboard()
    {
         $getPendapatanHariIni = $this->getPendapatanHariIni();

        //targetHarian 
        $potensiBulanan = $this->get_potensi_bulanan();
        $potensiHarian = ($potensiBulanan/30) + $this->get_potensi_harian();

        //presentase
        $persentase = 0;
        if ($potensiHarian > 0) {
            $persentase = ($getPendapatanHariIni / $potensiHarian) * 100;
        }

        //total va
        $totalVa = $this->get_total_va();
        //total potensi
        $totalPotensi = $this->get_total_potensi();

        $data = [
            'pendapatanHariIni' => $getPendapatanHariIni,
            'targetHariIni'     => $potensiHarian,
            'persentase'        => $persentase,
            'totalVa'           => $totalVa,
            'totalPotensi'      => $totalPotensi
        ];

        return view('publicdashboard', $data);
    }
public function commandcenter()
{
    $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
    $jenis   = $this->request->getGet('jenis');

    $jenisPrefix = null;
    if ($jenis === 'TJU') $jenisPrefix = '15088';
    elseif ($jenis === 'TKP') $jenisPrefix = '10325';
    elseif ($jenis === 'DISHUB') $jenisPrefix = '1032500008';

    $getPendapatanFilter = $this->getPendapatanByDate($tanggal, $jenisPrefix);

    $potensiBulanan = $this->get_potensi_bulanan($jenisPrefix);
    $potensiHarian  = ($potensiBulanan / 30) + $this->get_potensi_harian($jenisPrefix);

    $persentase = 0;
    if ($potensiHarian > 0) {
        $persentase = ($getPendapatanFilter / $potensiHarian) * 100;
    }

    $data = [
        'pendapatanHariIni' => $getPendapatanFilter,
        'targetHariIni'     => $potensiHarian,
        'persentase'        => $persentase,
        'totalVa'           => $this->get_total_va($jenisPrefix),
        'totalPotensi'      => $this->get_total_potensi($jenisPrefix),
        'tanggal'           => $tanggal,
        'jenis'             => $jenis
    ];

    if ($this->request->isAJAX()) {
        return $this->response->setJSON($data);
    }

    return view('commandcenter', $data);
}

public function getPendapatanByDate($tanggal, $jenisPrefix = null)
{
    $builder = $this->db->table('transaksi')
        ->selectSum('transaksi_nominal','total')
        ->where('DATE(transaksi_tanggal)', $tanggal);
    if ($jenisPrefix) $builder->like('transaksi_va', $jenisPrefix, 'after');
    $result = $builder->get()->getRow();
    return $result->total ?? 0;
}

public function get_potensi_bulanan($jenisPrefix = null)
{
    $rows = $this->potensiAktifRows(date('Y-m-d'), $jenisPrefix);
    return array_sum(array_map(static fn($r) => (float)($r['bulanan'] ?? 0), $rows));
}

public function get_potensi_harian($jenisPrefix = null)
{
    $columnMap = [
        'Monday'=>'senin','Tuesday'=>'selasa','Wednesday'=>'rabu','Thursday'=>'kamis',
        'Friday'=>'jumat','Saturday'=>'sabtu','Sunday'=>'minggu'
    ];
    $col = $columnMap[date('l')] ?? 'senin';

    $rows = $this->potensiAktifRows(date('Y-m-d'), $jenisPrefix);
    return array_sum(array_map(static fn($r) => (float)($r[$col] ?? 0), $rows));
}

private function potensiAktifRows(string $tanggal, ?string $jenisPrefix = null): array
{
    $builder = $this->db->table('va_owner vo')->select('vo.va_owner_va')
        ->where('vo.deleted_at IS NULL', null, false);
    if ($jenisPrefix) $builder->like('vo.va_owner_va', $jenisPrefix, 'after');
    $vas = array_column($builder->get()->getResultArray(), 'va_owner_va');
    if (!$vas) return [];
    $history = $this->db->table('potensi_riwayat')->whereIn('potensi_va', $vas)
        ->where('berlaku_mulai <=', $tanggal)
        ->groupStart()->where('berlaku_sampai IS NULL', null, false)->orWhere('berlaku_sampai >=', $tanggal)->groupEnd()
        ->orderBy('berlaku_mulai', 'DESC')->get()->getResultArray();
    $seen = []; $rows = [];
    foreach ($history as $row) if (!isset($seen[$row['potensi_va']])) { $seen[$row['potensi_va']] = true; $rows[] = $row; }
    return $rows;
}

public function get_total_va($jenisPrefix = null)
{
    $builder = $this->db->table('va_owner')->where('deleted_at', null);
    if ($jenisPrefix) $builder->like('va_owner_va', $jenisPrefix, 'after');
    return $builder->countAllResults();
}

public function get_total_potensi($jenisPrefix = null)
{
    $builder = $this->db->table('potensi p')
        ->join('va_owner vo', 'vo.va_owner_va = p.potensi_va')
        ->where('vo.deleted_at IS NULL', null, false);
    if ($jenisPrefix) $builder->like('vo.va_owner_va', $jenisPrefix, 'after');
    return $builder->countAllResults();
}
    public function commandcenterEX()
{
    // Tangkap filter tanggal dari request (format: Y-m-d)
    // Jika tidak ada parameter, default ke hari ini
    $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');

    // Ambil data pendapatan berdasarkan 1 hari (tanggal) saja
    $getPendapatanFilter = $this->getPendapatanByDate($tanggal);

    // Target harian tetap dinamis menggunakan logika hari atau disesuaikan
    $potensiBulanan = $this->get_potensi_bulanan();
    $potensiHarian  = ($potensiBulanan / 30) + $this->get_potensi_harian();

    // Persentase pencapaian terhadap target harian
    $persentase = 0;
    if ($potensiHarian > 0) {
        $persentase = ($getPendapatanFilter / $potensiHarian) * 100;
    }

    $totalVa      = $this->get_total_va();
    $totalPotensi = $this->get_total_potensi();

    $data = [
        'pendapatanHariIni' => $getPendapatanFilter, // Variabel tetap dipertahankan agar tidak merusak view lama
        'targetHariIni'     => $potensiHarian,
        'persentase'        => $persentase,
        'totalVa'           => $totalVa,
        'totalPotensi'      => $totalPotensi,
        'tanggal'           => $tanggal // Hanya mengirim 1 parameter tanggal ke view
    ];

    // Jika request dikirim via AJAX, kembalikan response JSON agar dashboard bisa update tanpa reload page
    if ($this->request->isAJAX()) {
        return $this->response->setJSON($data);
    }

    return view('commandcenter', $data);
}

public function getPendapatanByDateOLD($tanggal)
{

    // Contoh implementasi di Model
    $builder = $this->db->table('transaksi')
                    ->selectSum('transaksi_nominal','total')
                    ->where('DATE(transaksi_tanggal)', $tanggal)
                    ->get()
                    ->getRow();
    
    return  $builder->total;
}

    public function getPendapatanHariIni()
    {
        $today = date('Y-m-d'); // Hari ini

        $builder = $this->db->table('transaksi')
            ->selectSum('transaksi_nominal', 'total') // SUM kolom transaksi_nominal
            ->where('DATE(transaksi_tanggal)', $today)
            ->get()
            ->getRow();

        return  $builder->total; // return 0 kalau null
    }

    // public function get_potensi_bulananEx(){
     
    //     $builder = $this->db->table('potensi');
      
    //     $builder->select('SUM(bulanan) as bulanan');

    //     $result = $builder->get()->getResult();
    //     return $result[0]->bulanan;
    // }

    // public function get_total_va(){
    //     $builder = $this->db->table('va_owner')
    //     ->where('deleted_at',null);
    //     return $builder->countAllResults();
    // }

    // public function get_total_potensi(){
    //     $builder = $this->db->table('potensi');
    //     return $builder->countAllResults();
    // }

    // public function get_potensi_harian(){
    //     $day = date('l'); 
    //     $builder = $this->db->table('potensi');
    //     // echo $day;

    //     switch($day) {
    //     case 'Monday':
    //         $builder->select('SUM(senin) as harian');
    //         break;
    //     case 'Tuesday':
    //         $builder->select('SUM(selasa) as harian');
    //         break;
    //     case 'Wednesday':
    //         $builder->select('SUM(rabu) as harian');
    //         break;
    //     case 'Thursday':
    //         $builder->select('SUM(kamis) as harian');
    //         break;
    //     case 'Friday':
    //         $builder->select('SUM(jumat) as harian');
    //         break;
    //     case 'Saturday':
    //         $builder->select('SUM(sabtu) as harian');
    //         break;
    //     case 'Sunday':
    //         $builder->select('SUM(minggu) as harian');
    //         break;
    //     default:
    //         echo "Unable to determine the day.";

    //     }

    //     $result = $builder->get()->getResult();
    //     return $result[0]->harian;
    // }


    public function targetSetoranHariIni()
    {
        $today = date('Y-m-d'); // Hari ini

        $builder = $this->db->table('transaksi')
            ->selectSum('transaksi_nominal', 'total') // SUM kolom transaksi_nominal
            ->where('DATE(transaksi_tanggal)', $today)
            ->get()
            ->getRow();

        return $builder->total ?? 0; // return 0 kalau null
    }

    public function getPotensiCek(){
        $db = \Config\Database::connect();
$tahunIni = date('Y');

$potensiTotal = $db->table('potensi')
    ->select("SUM((senin+selasa+rabu+kamis+jumat+sabtu+minggu)*4 + mingguan*4 + bulanan) AS potensi")
   
    ->get()
    ->getRowArray();

print_r($potensiTotal);
    }

public function targetSetoranBulanan()
{
    $db    = \Config\Database::connect();
    $tahun = date('Y');
    $jenis = $this->request->getGet('jenis'); // TJU | TKP | DISHUB | null

    $jenisPrefix = null;
    if ($jenis === 'TJU') $jenisPrefix = '15088';
    elseif ($jenis === 'TKP') $jenisPrefix = '10325';
    elseif ($jenis === 'DISHUB') $jenisPrefix = '1032500008';

    // potensi (exclude VA terhapus + filter jenis)
    $potensiBuilder = $db->table('potensi p')
        ->select('p.*')
        ->join('va_owner vo', 'vo.va_owner_va = p.potensi_va')
        ->where('vo.deleted_at IS NULL', null, false);
    if ($jenisPrefix) $potensiBuilder->like('vo.va_owner_va', $jenisPrefix, 'after');
    $potensiRows = $potensiBuilder->get()->getResultArray();

    // Ambil snapshot potensi yang berlaku pada bulan tertentu.
    $potensiHistoryRows = $db->table('potensi_riwayat pr')
        ->select('pr.*')
        ->join('va_owner vo', 'vo.va_owner_va = pr.potensi_va')
        ->where('vo.deleted_at IS NULL', null, false)
        ->orderBy('pr.berlaku_mulai', 'ASC')->get()->getResultArray();
    $potensiHistory = [];
    foreach ($potensiHistoryRows as $row) $potensiHistory[$row['potensi_va']][] = $row;
    $potensiSnapshot = static function ($va, $tanggal) use ($potensiHistory) {
        $selected = null;
        foreach ($potensiHistory[$va] ?? [] as $row) {
            if ($row['berlaku_mulai'] > $tanggal) break;
            if (!empty($row['berlaku_sampai']) && $row['berlaku_sampai'] < $tanggal) continue;
            $selected = $row;
        }
        return $selected;
    };

    // transaksi per bulan
    $trxBuilder = $db->table('transaksi t')
        ->select('MONTH(t.transaksi_tanggal) AS bulan, SUM(t.transaksi_nominal) AS transaksi')
        ->join('va_owner vo', 'vo.va_owner_va = t.transaksi_va')
        ->where('YEAR(t.transaksi_tanggal)', $tahun)
        ->where('vo.deleted_at IS NULL', null, false)
        ->groupBy('MONTH(t.transaksi_tanggal)');
    if ($jenisPrefix) $trxBuilder->like('t.transaksi_va', $jenisPrefix, 'after');
    $trxMap = [];
    foreach ($trxBuilder->get()->getResultArray() as $t) {
        $trxMap[(int)$t['bulan']] = (float)$t['transaksi'];
    }

    // pengurangan (kendala) per bulan
    $kendalaSql = "
        SELECT MONTH(k.created_at) AS bulan,
            SUM(
                (CASE DAYOFWEEK(k.created_at)
                    WHEN 2 THEN IFNULL(p.senin,0) WHEN 3 THEN IFNULL(p.selasa,0)
                    WHEN 4 THEN IFNULL(p.rabu,0) WHEN 5 THEN IFNULL(p.kamis,0)
                    WHEN 6 THEN IFNULL(p.jumat,0) WHEN 7 THEN IFNULL(p.sabtu,0)
                    WHEN 1 THEN IFNULL(p.minggu,0) ELSE 0 END)
                *
                (CASE k.kategori WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.25 ELSE 0 END)
            ) AS pengurangan
        FROM kendala k
        JOIN potensi p ON p.potensi_va = k.kendala_va
        JOIN va_owner vo ON vo.va_owner_va = p.potensi_va
        WHERE YEAR(k.created_at) = ? AND k.approval = 1 AND vo.deleted_at IS NULL
          " . ($jenisPrefix ? "AND vo.va_owner_va LIKE ?" : "") . "
        GROUP BY MONTH(k.created_at)
    ";
    $bindings = $jenisPrefix ? [$tahun, $jenisPrefix . '%'] : [$tahun];
    $kendalaMap = [];
    foreach ($db->query($kendalaSql, $bindings)->getResultArray() as $k) {
        $kendalaMap[(int)$k['bulan']] = (float)$k['pengurangan'];
    }

    $result = [];
    for ($bulan = 1; $bulan <= 12; $bulan++) {

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $weekday = ['senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0];
        for ($d=1;$d<=$daysInMonth;$d++){
            $day = date('N', strtotime("$tahun-$bulan-$d"));
            if ($day==1) $weekday['senin']++;
            elseif ($day==2) $weekday['selasa']++;
            elseif ($day==3) $weekday['rabu']++;
            elseif ($day==4) $weekday['kamis']++;
            elseif ($day==5) $weekday['jumat']++;
            elseif ($day==6) $weekday['sabtu']++;
            elseif ($day==7) $weekday['minggu']++;
        }

        $potensiBulan = 0;
        foreach ($potensiRows as $p) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                $snapshot = $potensiSnapshot($p['potensi_va'], $date) ?: $p;
                $weekdayNo = (int) date('N', strtotime($date));
                if ((float)($snapshot['bulanan'] ?? 0) > 0) $potensiBulan += (float)$snapshot['bulanan'] / $daysInMonth;
                elseif ((float)($snapshot['mingguan'] ?? 0) > 0) $potensiBulan += (float)$snapshot['mingguan'] / 7;
                else $potensiBulan += (float)($snapshot[['senin','selasa','rabu','kamis','jumat','sabtu','minggu'][$weekdayNo - 1]] ?? 0);
            }
        }

        $result[] = [
            'bulan'       => $bulan,
            'potensi'     => $potensiBulan,
            'pengurangan' => $kendalaMap[$bulan] ?? 0,
            'transaksi'   => $trxMap[$bulan] ?? 0
        ];
    }

    return $this->response->setJSON($result);
}
   
public function targetSetoranBulananex1()
{
    $db = \Config\Database::connect();
    $tahun = date('Y');

    // ambil semua potensi
    $potensiRows = $db->table('potensi')->get()->getResultArray();

    // ambil transaksi per bulan
    $transaksiData = $db->table('transaksi')
        ->select("MONTH(transaksi_tanggal) AS bulan, SUM(transaksi_nominal) AS transaksi")
        ->where("YEAR(transaksi_tanggal)", $tahun)
        ->groupBy("MONTH(transaksi_tanggal)")
        ->get()
        ->getResultArray();

    $trxMap = [];
    foreach ($transaksiData as $t) {
        $trxMap[(int)$t['bulan']] = (float)$t['transaksi'];
    }

    $result = [];

    for ($bulan = 1; $bulan <= 12; $bulan++) {

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $weekday = [
            'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,
            'jumat'=>0,'sabtu'=>0,'minggu'=>0
        ];

        for ($d=1;$d<=$daysInMonth;$d++){
            $day = date('N', strtotime("$tahun-$bulan-$d"));

            if ($day==1) $weekday['senin']++;
            elseif ($day==2) $weekday['selasa']++;
            elseif ($day==3) $weekday['rabu']++;
            elseif ($day==4) $weekday['kamis']++;
            elseif ($day==5) $weekday['jumat']++;
            elseif ($day==6) $weekday['sabtu']++;
            elseif ($day==7) $weekday['minggu']++;
        }

        $potensiBulan = 0;

        foreach ($potensiRows as $p) {

            if (!empty($p['bulanan']) && $p['bulanan'] > 0) {
                $potensiBulan += $p['bulanan'];
            }

            elseif (!empty($p['mingguan']) && $p['mingguan'] > 0) {
                $potensiBulan += $p['mingguan'] * 4;
            }

            else {
                $potensiBulan +=
                    ($p['senin'] ?? 0) * $weekday['senin'] +
                    ($p['selasa'] ?? 0) * $weekday['selasa'] +
                    ($p['rabu'] ?? 0) * $weekday['rabu'] +
                    ($p['kamis'] ?? 0) * $weekday['kamis'] +
                    ($p['jumat'] ?? 0) * $weekday['jumat'] +
                    ($p['sabtu'] ?? 0) * $weekday['sabtu'] +
                    ($p['minggu'] ?? 0) * $weekday['minggu'];
            }
        }

        $result[] = [
            'bulan' => $bulan,
            'potensi' => $potensiBulan,
            'transaksi' => $trxMap[$bulan] ?? 0
        ];
    }

    return $this->response->setJSON($result);
}

public function targetSetoranBulanan1()
{
   $db = \Config\Database::connect();
    $tahunIni = date('Y');

    // Hitung total potensi (sama tiap bulan)
   $potensiTotal = $db->table('potensi')
    ->select("
        (
            SUM(
                (COALESCE(senin,0)+COALESCE(selasa,0)+COALESCE(rabu,0)+COALESCE(kamis,0)+COALESCE(jumat,0)+COALESCE(sabtu,0)+COALESCE(minggu,0))*4
                + COALESCE(mingguan,0)*4
                + COALESCE(bulanan,0)
            )
        ) AS potensi
    ")
    ->get()
    ->getRowArray();


    $potensi = $potensiTotal['potensi'];

    // Ambil transaksi per bulan
    $transaksiData = $db->table('transaksi')
        ->select("MONTH(transaksi_tanggal) AS bulan, SUM(transaksi_nominal) AS transaksi")
        ->where("YEAR(transaksi_tanggal)", $tahunIni)
        ->groupBy("MONTH(transaksi_tanggal)")
        ->get()
        ->getResultArray();

    // Buat array 1-12 bulan
    $result = [];
    for($i=1;$i<=12;$i++){
        $transaksiBulan = 0;
        foreach($transaksiData as $t){
            if((int)$t['bulan'] === $i){
                $transaksiBulan = (float)$t['transaksi'];
                break;
            }
        }
        $result[] = ['bulan'=>$i,'potensi'=>$potensi,'transaksi'=>$transaksiBulan];
    }

    return $this->response->setJSON($result);
}

public function targetSetoranBulananKecamatan($i = null)
{
    $db    = \Config\Database::connect();
    $bulan = date('n');
    $tahun = date('Y');
    $jenis = $this->request->getGet('jenis');

    $jenisPrefix = null;
    if ($jenis === 'TJU') $jenisPrefix = '15088';
    elseif ($jenis === 'TKP') $jenisPrefix = '10325';
    elseif ($jenis === 'DISHUB') $jenisPrefix = '1032500008';

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
    $weekday = ['senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0];
    for ($d=1;$d<=$daysInMonth;$d++) {
        $day = date('N', strtotime("$tahun-$bulan-$d"));
        if ($day==1) $weekday['senin']++;
        elseif ($day==2) $weekday['selasa']++;
        elseif ($day==3) $weekday['rabu']++;
        elseif ($day==4) $weekday['kamis']++;
        elseif ($day==5) $weekday['jumat']++;
        elseif ($day==6) $weekday['sabtu']++;
        elseif ($day==7) $weekday['minggu']++;
    }

    // transaksi bulan ini per kecamatan
    $trxBuilder = $db->table('transaksi')
        ->select('SUBSTRING(titpar_klrhnid,1,6) as kecamatan_id, SUM(transaksi_nominal) AS total_transaksi')
        ->join('va_owner','va_owner.va_owner_va = transaksi.transaksi_va','left')
        ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('MONTH(transaksi_tanggal)', $bulan)
        ->where('YEAR(transaksi_tanggal)', $tahun)
        ->where('va_owner.deleted_at', null)
        ->groupBy('kecamatan_id');
    if ($jenisPrefix) $trxBuilder->like('transaksi.transaksi_va', $jenisPrefix, 'after');
    $subquery = $trxBuilder->get()->getResultArray();

    // potensi per kecamatan
    $potensiBuilder = $db->table('potensi')
        ->select("
            SUBSTRING(dishub_titpar.titpar_klrhnid,1,6) AS kecamatan_id,
            SUM(
                CASE
                    WHEN potensi.bulanan IS NOT NULL AND potensi.bulanan > 0 THEN potensi.bulanan
                    WHEN potensi.mingguan IS NOT NULL AND potensi.mingguan > 0 THEN potensi.mingguan * 4
                    ELSE
                        (COALESCE(potensi.senin,0) * {$weekday['senin']} +
                         COALESCE(potensi.selasa,0) * {$weekday['selasa']} +
                         COALESCE(potensi.rabu,0) * {$weekday['rabu']} +
                         COALESCE(potensi.kamis,0) * {$weekday['kamis']} +
                         COALESCE(potensi.jumat,0) * {$weekday['jumat']} +
                         COALESCE(potensi.sabtu,0) * {$weekday['sabtu']} +
                         COALESCE(potensi.minggu,0) * {$weekday['minggu']})
                END
            ) AS potensi
        ")
        ->join('va_owner','va_owner.va_owner_va = potensi.potensi_va','left')
        ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('va_owner.deleted_at', null)
        ->groupBy('kecamatan_id');
    if ($jenisPrefix) $potensiBuilder->like('va_owner.va_owner_va', $jenisPrefix, 'after');
    $potensiTotal = $potensiBuilder->get()->getResultArray();

    // pengurangan (kendala) per kecamatan, bulan berjalan
    $kendalaSql = "
        SELECT SUBSTRING(t.titpar_klrhnid,1,6) AS kecamatan_id,
            SUM(
                (CASE DAYOFWEEK(k.created_at)
                    WHEN 2 THEN IFNULL(p.senin,0) WHEN 3 THEN IFNULL(p.selasa,0)
                    WHEN 4 THEN IFNULL(p.rabu,0) WHEN 5 THEN IFNULL(p.kamis,0)
                    WHEN 6 THEN IFNULL(p.jumat,0) WHEN 7 THEN IFNULL(p.sabtu,0)
                    WHEN 1 THEN IFNULL(p.minggu,0) ELSE 0 END)
                *
                (CASE k.kategori WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.25 ELSE 0 END)
            ) AS pengurangan
        FROM kendala k
        JOIN potensi p ON p.potensi_va = k.kendala_va
        JOIN va_owner vo ON vo.va_owner_va = p.potensi_va
        JOIN dishub_titpargrup g ON g.titpargrup_id = vo.va_owner_titpargrupid
        JOIN dishub_titpar t ON t.titpar_id = g.titpargrup_titparid
        WHERE MONTH(k.created_at) = ? AND YEAR(k.created_at) = ?
          AND k.approval = 1 AND vo.deleted_at IS NULL
          " . ($jenisPrefix ? "AND vo.va_owner_va LIKE ?" : "") . "
        GROUP BY kecamatan_id
    ";
    $bindings = $jenisPrefix ? [$bulan, $tahun, $jenisPrefix . '%'] : [$bulan, $tahun];
    $kendalaMap = [];
    foreach ($db->query($kendalaSql, $bindings)->getResultArray() as $k) {
        $kendalaMap[$k['kecamatan_id'] ?? 'UNKNOWN'] = (float)$k['pengurangan'];
    }

    $merged = [];
    foreach ($potensiTotal as $p) {
        $id = $p['kecamatan_id'] ?? 'UNKNOWN';
        $merged[$id] = [
            'kecamatan_id' => $id,
            'potensi'      => (float)$p['potensi'],
            'pengurangan'  => $kendalaMap[$id] ?? 0,
            'transaksi'    => 0
        ];
    }
    foreach ($subquery as $t) {
        $id = $t['kecamatan_id'] ?? 'UNKNOWN';
        if (!isset($merged[$id])) {
            $merged[$id] = [
                'kecamatan_id' => $id,
                'potensi'      => 0,
                'pengurangan'  => $kendalaMap[$id] ?? 0,
                'transaksi'    => (float)$t['total_transaksi']
            ];
        } else {
            $merged[$id]['transaksi'] = (float)$t['total_transaksi'];
        }
    }

    $kecamatanNames = [];
    foreach ($db->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray() as $wilayah) {
        $kecamatanNames[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
    }

    $result = [];
    foreach ($kecamatanNames as $id => $nama) {
        $item = $merged[$id] ?? [
            'kecamatan_id' => (string) $id,
            'potensi' => 0,
            'pengurangan' => 0,
            'transaksi' => 0,
        ];
        $item['kecamatan_nama'] = $nama;
        $result[] = $item;
    }
    return $this->response->setJSON($result);
}

public function targetSetoranBulananKecamatanEx($i)
{
    $db = \Config\Database::connect();
    $bulan = date('n');
    $tahun = date('Y');

    // hitung distribusi hari dalam bulan
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekday = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,
        'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d=1;$d<=$daysInMonth;$d++) {
        $day = date('N', strtotime("$tahun-$bulan-$d"));

        if ($day==1) $weekday['senin']++;
        elseif ($day==2) $weekday['selasa']++;
        elseif ($day==3) $weekday['rabu']++;
        elseif ($day==4) $weekday['kamis']++;
        elseif ($day==5) $weekday['jumat']++;
        elseif ($day==6) $weekday['sabtu']++;
        elseif ($day==7) $weekday['minggu']++;
    }

    // transaksi bulan ini per kecamatan
    $subquery = $db->table('transaksi')
        ->select('SUBSTRING(titpar_klrhnid,1,6) as kecamatan_id, SUM(transaksi_nominal) AS total_transaksi')
        ->join('va_owner','va_owner.va_owner_va = transaksi.transaksi_va','left')
        ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('MONTH(transaksi_tanggal)', $bulan)
        ->where('YEAR(transaksi_tanggal)', $tahun)
        ->where('va_owner.deleted_at', null)
        ->groupBy('kecamatan_id')
        ->get()
        ->getResultArray();

    // potensi per kecamatan (calendar based)
    $potensiTotal = $db->table('potensi')
        ->select("
            SUBSTRING(dishub_titpar.titpar_klrhnid,1,6) AS kecamatan_id,
            SUM(
                CASE
                    WHEN potensi.bulanan IS NOT NULL AND potensi.bulanan > 0
                        THEN potensi.bulanan
                    WHEN potensi.mingguan IS NOT NULL AND potensi.mingguan > 0
                        THEN potensi.mingguan * 4
                    ELSE
                        (COALESCE(potensi.senin,0) * {$weekday['senin']} +
                         COALESCE(potensi.selasa,0) * {$weekday['selasa']} +
                         COALESCE(potensi.rabu,0) * {$weekday['rabu']} +
                         COALESCE(potensi.kamis,0) * {$weekday['kamis']} +
                         COALESCE(potensi.jumat,0) * {$weekday['jumat']} +
                         COALESCE(potensi.sabtu,0) * {$weekday['sabtu']} +
                         COALESCE(potensi.minggu,0) * {$weekday['minggu']})
                END
            ) AS potensi
        ")
        ->join('va_owner','va_owner.va_owner_va = potensi.potensi_va','left')
        ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('va_owner.deleted_at', null)
        ->groupBy('kecamatan_id')
        ->get()
        ->getResultArray();

    $merged = [];

    foreach ($potensiTotal as $p) {
        $id = $p['kecamatan_id'] ?? 'UNKNOWN';
        $merged[$id] = [
            'kecamatan_id' => $id,
            'potensi' => (float)$p['potensi'],
            'transaksi' => 0
        ];
    }

    foreach ($subquery as $t) {
        $id = $t['kecamatan_id'] ?? 'UNKNOWN';

        if (!isset($merged[$id])) {
            $merged[$id] = [
                'kecamatan_id'=>$id,
                'potensi'=>0,
                'transaksi'=>(float)$t['total_transaksi']
            ];
        } else {
            $merged[$id]['transaksi'] = (float)$t['total_transaksi'];
        }
    }

    $kecamatanNames = [];
    foreach ($db->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->distinct()->get()->getResultArray() as $wilayah) {
        $kecamatanNames[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
    }

    $result = array_map(function($item) use ($kecamatanNames) {
        $id = $item['kecamatan_id'];
        $item['kecamatan_nama'] = $kecamatanNames[$id] ?? 'Unknown';
        return $item;
    }, array_values($merged));

    return $this->response->setJSON($result);
}

public function targetSetoranBulananKecamatanOLD($i)
{
   $db = \Config\Database::connect();
    $bulanini = date('m');
    $tahunIni = date('Y');

    $subquery = $db->table('transaksi')
    ->select('SUBSTRING(titpar_klrhnid, 1, 6) as kecamatan_id,SUM(transaksi_nominal) AS total_transaksi')
    ->join('va_owner','va_owner.va_owner_va = transaksi.transaksi_va','left')
    ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
    ->join('dishub_anggota','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left')
    ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
    
    ->where('MONTH(transaksi_tanggal)', $bulanini, false)
    ->where('YEAR(transaksi_tanggal)', $tahunIni, false)
    ->groupBy('kecamatan_id')
    ->get()
    ->getResultArray();

    $potensiTotal = $db->table('potensi')
    ->select("
        SUM(
            (
                (COALESCE(senin,0)+COALESCE(selasa,0)+COALESCE(rabu,0)+COALESCE(kamis,0)+COALESCE(jumat,0)+COALESCE(sabtu,0)+COALESCE(minggu,0))*4
                + COALESCE(mingguan,0)*4
                + COALESCE(bulanan,0)
            )
        ) AS potensi,
        SUBSTRING(dishub_titpar.titpar_klrhnid, 1, 6) AS kecamatan_id
    ")
    ->join('va_owner','va_owner.va_owner_va = potensi.potensi_va','left')
    ->join('dishub_titpargrup','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left')
    ->join('dishub_anggota','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left')
    ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
    ->groupBy('kecamatan_id')
    ->get()
    ->getResultArray();
 $merged = [];

    foreach ($potensiTotal as $p) {
        $id = $p['kecamatan_id'] ?? 'UNKNOWN';
        $merged[$id] = [
            'kecamatan_id' => $id,
            'potensi' => (float) $p['potensi'],
            'transaksi' => 0
        ];
    }

    foreach ($subquery as $t) {
        $id = $t['kecamatan_id'] ?? 'UNKNOWN';
        if (!isset($merged[$id])) {
            $merged[$id] = [
                'kecamatan_id' => $id,
                'potensi' => 0,
                'transaksi' => (float) $t['total_transaksi']
            ];
        } else {
            $merged[$id]['transaksi'] = (float) $t['total_transaksi'];
        }
    }

    $kecamatanNames = [];
    foreach ($db->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->distinct()->get()->getResultArray() as $wilayah) {
        $kecamatanNames[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
    }

    $result = array_map(function($item) use ($kecamatanNames) {
    $id = $item['kecamatan_id'];
    $item['kecamatan_nama'] = $kecamatanNames[$id] ?? 'Unknown';
    return $item;
}, array_values($merged));


    // ---- Return merged JSON ----
    return $this->response->setJSON($result);
}

    public function syncData()
    {
      
        $url = 'https://sisparma.malangkota.go.id/api/dishub_dynamic_api_fortified.php?table=dishub_anggota&parameter=anggota_id,anggota_noreg,anggota_newnoreg,anggota_nama&order=anggota_id&ad=asc&limit=10000';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);          // follow 3xx redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);          // keep verification on in production
        curl_setopt($ch, CURLOPT_HEADER, true);                   // include headers in output
        curl_setopt($ch, CURLOPT_ENCODING, '');                   // accept gzip/deflate (empty string = all)
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/xxx Safari/537.36');

        // Add other headers you captured from the browser (e.g. Referer, Accept-Language)
        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://target.example.com/',
            // 'Cookie: cf_clearance=...; __cf_bm=...'   // set cookie below if you have it
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // If you already have cookies string, set it like:
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Cookie: cf_clearance=...; __cf_bm=...']));

        $response = curl_exec($ch);
        if ($response === false) {
            echo "cURL error: " . curl_error($ch) . PHP_EOL;
            curl_close($ch);
            exit;
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        // split headers and body
        $header_size = $info['header_size'];
        $raw_headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        echo "HTTP Code: " . $info['http_code'] . PHP_EOL;
        echo "Effective URL: " . $info['url'] . PHP_EOL;
        echo "Response headers:\n" . $raw_headers . PHP_EOL;
        echo "Response body:\n" . $body . PHP_EOL;


    }

    public function privacypolicy(){

         return view('privacy_policy');

    }

    public function titparCount()
{
    $jenis = $this->request->getGet('jenis');
    $jenisPrefix = null;
    if ($jenis === 'TJU') $jenisPrefix = '15088';
    elseif ($jenis === 'TKP') $jenisPrefix = '10325';
    elseif ($jenis === 'DISHUB') $jenisPrefix = '1032500008';

    $builder = $this->db->table('dishub_titpar t')
        ->select('COUNT(DISTINCT t.titpar_id) as total')
        ->where('t.titpar_status', '3');

    if ($jenisPrefix) {
        $builder->join('dishub_titpargrup g', 'g.titpargrup_titparid = t.titpar_id')
                 ->join('va_owner vo', 'vo.va_owner_titpargrupid = g.titpargrup_id')
                 ->like('vo.va_owner_va', $jenisPrefix, 'after');
    }

    $data = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $data
    ]);
}

    public function titparCountEX(){
         $db = \Config\Database::connect();

        $builder = $db->table('dishub_titpar');
        $builder->select('COUNT(*) as total');
        $builder->where('dishub_titpar.titpar_status','3');

        $query = $builder->get();

        $data = $query->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
