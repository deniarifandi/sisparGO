<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            $this->restoreRememberedLogin($session, $request);
            if (!$session->get('isLoggedIn')) {
                // Aplikasi berjalan di subfolder (/dishub2026/public/), jadi
                // jangan memakai path absolut host seperti /login.
                return redirect()->to(base_url('login'));
            }
        }

        // Allow request
        return null;
    }

    private function restoreRememberedLogin($session, RequestInterface $request): void
    {
        $cookie = (string) $request->getCookie('sisparma_remember');
        if (!preg_match('/^([a-f0-9]{32}):([a-f0-9]{64})$/', $cookie, $matches)) {
            return;
        }

        $db = \Config\Database::connect();
        $record = $db->table('auth_remember_tokens')->where('selector', $matches[1])->get()->getRowArray();
        if (!$record || strtotime($record['expires_at']) <= time() || !hash_equals($record['token_hash'], hash('sha256', $matches[2]))) {
            service('response')->deleteCookie('sisparma_remember', '', '/');
            return;
        }

        $user = $db->table('users')->where('user_id', $record['user_id'])->get()->getRowArray();
        if (!$user) {
            $db->table('auth_remember_tokens')->where('selector', $matches[1])->delete();
            return;
        }

        $session->regenerate();
        $session->set([
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'nama' => $user['nama'],
            'isLoggedIn' => true,
        ]);

        // Rotate the validator after each successful restore to limit replay.
        $validator = bin2hex(random_bytes(32));
        $db->table('auth_remember_tokens')->where('selector', $matches[1])->update([
            'token_hash' => hash('sha256', $validator),
            'expires_at' => date('Y-m-d H:i:s', time() + 30 * DAY),
        ]);
        service('response')->setCookie('sisparma_remember', $matches[1] . ':' . $validator, time() + 30 * DAY, '', '/', '', $request->isSecure(), true, 'Lax');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
