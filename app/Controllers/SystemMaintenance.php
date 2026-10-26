<?php

namespace App\Controllers;

class SystemMaintenance extends BaseController
{
    public function gitPull(): string
    {
        $this->ensureSuperadmin();

        return view('system/git_pull', [
            'result' => null,
            'projectPath' => realpath(ROOTPATH) ?: ROOTPATH,
        ]);
    }

    public function runGitPull()
    {
        $this->ensureSuperadmin();

        $projectPath = realpath(ROOTPATH);
        if ($projectPath === false || !is_dir($projectPath . DIRECTORY_SEPARATOR . '.git')) {
            return view('system/git_pull', [
                'result' => ['success' => false, 'output' => 'Folder Git tidak ditemukan di: ' . ROOTPATH],
                'projectPath' => ROOTPATH,
            ]);
        }

        // Command is intentionally fixed; no command is accepted from the request.
        $command = 'git -C ' . escapeshellarg($projectPath) . ' pull';
        $output = $this->runGitCommand($command);

        return view('system/git_pull', [
            'result' => [
                'success' => $output['exitCode'] === 0,
                'output' => trim($output['text']),
            ],
            'projectPath' => $projectPath,
        ]);
    }

    private function runGitCommand(string $command): array
    {
        if (!function_exists('proc_open')) {
            return ['exitCode' => 1, 'text' => 'PHP proc_open dinonaktifkan. Aktifkan proc_open di php.ini XAMPP terlebih dahulu.'];
        }

        $pipes = [];
        $process = \proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes, ROOTPATH);

        if (!is_resource($process)) {
            return ['exitCode' => 1, 'text' => 'Git tidak dapat dijalankan oleh PHP.'];
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = \proc_close($process);

        return ['exitCode' => $exitCode, 'text' => trim($stdout . PHP_EOL . $stderr)];
    }

    private function ensureSuperadmin(): void
    {
        if ((int) session('role') !== 0) {
            throw \CodeIgniter\Exceptions\PageForbiddenException::forPageForbidden();
        }
    }
}
