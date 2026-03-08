<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GitPullController extends Controller
{
    private const BRANCH = 'main';

    public function index()
    {
        $diagnostics = $this->getDiagnostics();
        $allOk = $this->isPullReady($diagnostics);
        $isHttp = !$this->canExec();

        return view('git-pull.index', compact('diagnostics', 'allOk', 'isHttp'));
    }

    public function pull()
    {
        $result = $this->executePull();

        return redirect()->route('git-pull.index')
            ->with($result['success'] ? 'success' : 'error', $result['output']);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'git_repo_url' => 'nullable|url|max:500',
            'git_access_token' => 'nullable|string|max:500',
            'git_provider' => 'required|in:github,gitlab',
        ]);

        Setting::set('git_repo_url', $request->input('git_repo_url', ''), 'string', 'Git repository URL', false);
        Setting::set('git_provider', $request->input('git_provider', 'github'), 'string', 'Git provider (github/gitlab)', false);

        if ($request->filled('git_access_token')) {
            Setting::set('git_access_token', $request->input('git_access_token'), 'string', 'Git access token', false);
        }

        return redirect()->route('git-pull.index')->with('success', 'Settings berhasil disimpan.');
    }

    public function postAction(Request $request)
    {
        $action = $request->input('action');
        $output = '';
        $success = false;

        if ($this->canExec()) {
            $safeDir = escapeshellarg(base_path());

            switch ($action) {
                case 'migrate':
                    exec("cd {$safeDir} && php artisan migrate --force 2>&1", $lines, $code);
                    $output = implode("\n", $lines);
                    $success = $code === 0;
                    break;
                case 'cache-clear':
                    exec("cd {$safeDir} && php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear 2>&1", $lines, $code);
                    $output = implode("\n", $lines);
                    $success = $code === 0;
                    break;
                case 'optimize':
                    exec("cd {$safeDir} && php artisan optimize 2>&1", $lines, $code);
                    $output = implode("\n", $lines);
                    $success = $code === 0;
                    break;
                default:
                    $output = 'Action tidak dikenal.';
            }
        } else {
            // When exec is disabled, use Artisan facade
            try {
                switch ($action) {
                    case 'migrate':
                        \Artisan::call('migrate', ['--force' => true]);
                        $output = \Artisan::output();
                        $success = true;
                        break;
                    case 'cache-clear':
                        \Artisan::call('cache:clear');
                        $output = \Artisan::output();
                        \Artisan::call('config:clear');
                        $output .= \Artisan::output();
                        \Artisan::call('view:clear');
                        $output .= \Artisan::output();
                        \Artisan::call('route:clear');
                        $output .= \Artisan::output();
                        $success = true;
                        break;
                    case 'optimize':
                        \Artisan::call('optimize');
                        $output = \Artisan::output();
                        $success = true;
                        break;
                    default:
                        $output = 'Action tidak dikenal.';
                }
            } catch (\Exception $e) {
                $output = 'Error: ' . $e->getMessage();
            }
        }

        return redirect()->route('git-pull.index')
            ->with($success ? 'success' : 'error', ucfirst($action) . ":\n" . $output);
    }

    private function canExec(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);

        return !in_array('exec', $disabled);
    }

    private function executePull(): array
    {
        if ($this->canExec()) {
            return $this->execGitPull();
        }

        return $this->httpGitPull();
    }

    private function execGitPull(): array
    {
        $appDir = base_path();

        if (!is_dir($appDir . '/.git')) {
            return ['success' => false, 'output' => 'Error: Direktori aplikasi bukan Git repository.'];
        }

        $safeDir = escapeshellarg($appDir);
        $safeBranch = escapeshellarg(self::BRANCH);

        $command = sprintf('cd %s && git pull origin %s 2>&1', $safeDir, $safeBranch);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        $outputText = implode("\n", $output);

        if ($returnCode === 0) {
            return ['success' => true, 'output' => "Git pull berhasil!\n\n" . $outputText];
        }

        return ['success' => false, 'output' => "Git pull gagal (code: {$returnCode})\n\n" . $outputText];
    }

    private function httpGitPull(): array
    {
        $repoUrl = Setting::get('git_repo_url', '');
        $token = Setting::get('git_access_token', '');
        $provider = Setting::get('git_provider', 'github');

        if (empty($repoUrl)) {
            return ['success' => false, 'output' => 'Error: URL repository belum dikonfigurasi.'];
        }

        if (empty($token)) {
            return ['success' => false, 'output' => 'Error: Access token belum dikonfigurasi.'];
        }

        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'output' => 'Error: PHP ZipArchive extension tidak tersedia.'];
        }

        $log = "Downloading dari {$provider}...\n";
        $log .= "Repository: {$repoUrl}\n";
        $log .= "Branch: " . self::BRANCH . "\n\n";

        if ($provider === 'github') {
            $downloadResult = $this->downloadFromGithub($repoUrl, $token);
        } else {
            $downloadResult = $this->downloadFromGitlab($repoUrl, $token);
        }

        if (!$downloadResult['success']) {
            return ['success' => false, 'output' => $log . $downloadResult['error']];
        }

        $zipContent = $downloadResult['content'];
        $log .= "Downloaded " . $this->formatBytes(strlen($zipContent)) . "\n";

        // Save to temp file
        $tmpZip = storage_path('app/git-pull-' . uniqid() . '.zip');
        $tmpExtract = storage_path('app/git-pull-extract-' . uniqid());

        $written = file_put_contents($tmpZip, $zipContent);
        unset($zipContent);

        if ($written === false) {
            return ['success' => false, 'output' => $log . 'Error: Tidak bisa menulis file temporary.'];
        }

        // Extract zip
        $zip = new \ZipArchive();
        $openResult = $zip->open($tmpZip);

        if ($openResult !== true) {
            @unlink($tmpZip);
            return ['success' => false, 'output' => $log . 'Error: Tidak bisa membuka file zip (code: ' . $openResult . ').'];
        }

        @mkdir($tmpExtract, 0755, true);
        $zip->extractTo($tmpExtract);
        $zip->close();
        @unlink($tmpZip);

        // Find extracted subdirectory
        $extractedDirs = glob($tmpExtract . '/*', GLOB_ONLYDIR);
        if (empty($extractedDirs)) {
            $this->recursiveDelete($tmpExtract);
            return ['success' => false, 'output' => $log . 'Error: Tidak ditemukan direktori dalam zip.'];
        }

        $sourceDir = $extractedDirs[0];
        $appDir = base_path();

        $fileCount = 0;
        $this->countFiles($sourceDir, $fileCount);
        $log .= "Extracted {$fileCount} files\n";
        $log .= "Updating direktori aplikasi...\n\n";

        // Copy files (skip .git, vendor, node_modules, storage, .env)
        $copyResult = $this->recursiveCopy($sourceDir, $appDir);

        $this->recursiveDelete($tmpExtract);

        if (!empty($copyResult['errors'])) {
            $log .= "Selesai dengan error:\n" . implode("\n", $copyResult['errors']);
            return ['success' => false, 'output' => $log];
        }

        $log .= "Berhasil update {$copyResult['copied']} files.";

        return ['success' => true, 'output' => $log];
    }

    private function downloadFromGithub(string $repoUrl, string $token): array
    {
        $repoPath = $this->getRepoPath($repoUrl);
        if (empty($repoPath)) {
            return ['success' => false, 'error' => 'Tidak bisa parse repository path dari URL.'];
        }

        $apiUrl = "https://api.github.com/repos/{$repoPath}/zipball/" . self::BRANCH;

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer ' . $token,
                'User-Agent: POS-Universal-GitPull',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 120,
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Download error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "Download gagal (HTTP {$httpCode})."];
        }

        return ['success' => true, 'content' => $content];
    }

    private function downloadFromGitlab(string $repoUrl, string $token): array
    {
        $repoPath = $this->getRepoPath($repoUrl);
        if (empty($repoPath)) {
            return ['success' => false, 'error' => 'Tidak bisa parse repository path dari URL.'];
        }

        $encodedPath = rawurlencode($repoPath);
        $apiUrl = "https://gitlab.com/api/v4/projects/{$encodedPath}/repository/archive.zip?sha=" . self::BRANCH;

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'PRIVATE-TOKEN: ' . $token,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 120,
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Download error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "Download gagal (HTTP {$httpCode})."];
        }

        return ['success' => true, 'content' => $content];
    }

    private function getRepoPath(string $url): string
    {
        $url = rtrim($url, '/');
        $url = preg_replace('/\.git$/', '', $url);

        $parsed = parse_url($url);
        if (empty($parsed['path'])) {
            return '';
        }

        return ltrim($parsed['path'], '/');
    }

    private function recursiveCopy(string $src, string $dst): array
    {
        $result = ['copied' => 0, 'errors' => []];
        $skipDirs = ['.git', 'vendor', 'node_modules', 'storage', '.env'];

        $dir = opendir($src);
        if ($dir === false) {
            $result['errors'][] = "Could not open: {$src}";
            return $result;
        }

        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (in_array($file, $skipDirs)) {
                continue;
            }

            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;

            if (is_dir($srcPath)) {
                $subResult = $this->recursiveCopy($srcPath, $dstPath);
                $result['copied'] += $subResult['copied'];
                $result['errors'] = array_merge($result['errors'], $subResult['errors']);
            } else {
                if (@copy($srcPath, $dstPath)) {
                    $result['copied']++;
                } else {
                    $result['errors'][] = "Failed to copy: {$file}";
                }
            }
        }

        closedir($dir);
        return $result;
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    private function countFiles(string $dir, int &$count): void
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->countFiles($path, $count);
            } else {
                $count++;
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getDiagnostics(): array
    {
        $appDir = base_path();
        $execOk = $this->canExec();
        $diagnostics = [];

        // Pull method
        $diagnostics['pull_method'] = [
            'label' => 'Metode Pull',
            'status' => true,
            'value' => $execOk ? 'exec() — Git CLI' : 'HTTP — Download ZIP',
            'help' => $execOk ? 'Menggunakan perintah git langsung' : 'exec() disabled, menggunakan download HTTP',
        ];

        if ($execOk) {
            // Git installed
            $gitOutput = [];
            $gitCode = 0;
            exec('git --version 2>&1', $gitOutput, $gitCode);
            $gitVersion = $gitCode === 0 ? implode('', $gitOutput) : 'Tidak ditemukan';
            $diagnostics['git_installed'] = [
                'label' => 'Git Installation',
                'status' => strpos($gitVersion, 'git version') !== false,
                'value' => $gitVersion,
                'help' => 'Git harus terinstall di server',
            ];

            // .git directory
            $gitDirExists = is_dir($appDir . '/.git');
            $diagnostics['git_directory'] = [
                'label' => 'Git Repository (.git)',
                'status' => $gitDirExists,
                'value' => $gitDirExists ? 'Ditemukan' : 'Tidak ditemukan',
                'help' => 'Direktori harus berupa git repository',
            ];

            // Git remote
            $remoteUrl = '';
            if ($gitDirExists) {
                $remoteOutput = [];
                $safeDir = escapeshellarg($appDir);
                exec("cd {$safeDir} && git remote get-url origin 2>&1", $remoteOutput, $remoteCode);
                $remoteUrl = $remoteCode === 0 ? implode('', $remoteOutput) : 'Tidak dikonfigurasi';
            }
            $diagnostics['git_remote'] = [
                'label' => 'Git Remote (origin)',
                'status' => !empty($remoteUrl) && $remoteUrl !== 'Tidak dikonfigurasi',
                'value' => $remoteUrl ?: 'Tidak bisa dicek',
                'help' => 'URL repository remote',
            ];

            // Current branch
            $currentBranch = '';
            if ($gitDirExists) {
                $branchOutput = [];
                $safeDir = escapeshellarg($appDir);
                exec("cd {$safeDir} && git branch --show-current 2>&1", $branchOutput, $branchCode);
                $currentBranch = $branchCode === 0 ? implode('', $branchOutput) : 'Unknown';
            }
            $diagnostics['current_branch'] = [
                'label' => 'Current Branch',
                'status' => $currentBranch === self::BRANCH,
                'value' => $currentBranch ?: 'Tidak bisa dicek',
                'help' => 'Harus sesuai branch target: ' . self::BRANCH,
            ];
        } else {
            // HTTP mode diagnostics
            $repoUrl = Setting::get('git_repo_url', '');
            $token = Setting::get('git_access_token', '');
            $provider = Setting::get('git_provider', 'github');

            $diagnostics['repo_url'] = [
                'label' => 'Repository URL',
                'status' => !empty($repoUrl),
                'value' => !empty($repoUrl) ? $repoUrl : 'Belum dikonfigurasi',
                'help' => 'Set di form settings',
            ];

            $diagnostics['access_token'] = [
                'label' => 'Access Token',
                'status' => !empty($token),
                'value' => !empty($token) ? str_repeat('*', 8) . substr($token, -4) : 'Belum dikonfigurasi',
                'help' => 'Personal Access Token dengan scope repo/read_api',
            ];

            $diagnostics['zip_archive'] = [
                'label' => 'PHP ZipArchive',
                'status' => class_exists('ZipArchive'),
                'value' => class_exists('ZipArchive') ? 'Tersedia' : 'Tidak tersedia',
                'help' => 'Diperlukan untuk extract archive',
            ];

            // Test API connectivity
            $apiOk = false;
            $apiValue = 'Belum ditest';
            if (!empty($repoUrl) && !empty($token)) {
                $repoPath = $this->getRepoPath($repoUrl);
                if (!empty($repoPath)) {
                    if ($provider === 'github') {
                        $testUrl = "https://api.github.com/repos/{$repoPath}";
                        $headers = [
                            'Accept: application/vnd.github+json',
                            'Authorization: Bearer ' . $token,
                            'User-Agent: POS-Universal-GitPull',
                        ];
                    } else {
                        $encodedPath = rawurlencode($repoPath);
                        $testUrl = "https://gitlab.com/api/v4/projects/{$encodedPath}";
                        $headers = ['PRIVATE-TOKEN: ' . $token];
                    }

                    $ch = curl_init($testUrl);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_TIMEOUT => 15,
                    ]);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    if ($curlError) {
                        $apiValue = 'Gagal: ' . $curlError;
                    } elseif ($httpCode === 200) {
                        $body = json_decode($response, true);
                        $apiOk = true;
                        $repoName = $provider === 'github'
                            ? ($body['full_name'] ?? $repoPath)
                            : ($body['name_with_namespace'] ?? $repoPath);
                        $apiValue = 'Terhubung — ' . $repoName;
                    } elseif ($httpCode === 401) {
                        $apiValue = 'Token tidak valid (HTTP 401)';
                    } elseif ($httpCode === 404) {
                        $apiValue = 'Repository tidak ditemukan (HTTP 404)';
                    } else {
                        $apiValue = "HTTP {$httpCode}";
                    }
                }
            }
            $diagnostics['api_connection'] = [
                'label' => 'API Connection',
                'status' => $apiOk,
                'value' => $apiValue,
                'help' => 'Verifikasi token dan akses repository',
            ];
        }

        // Directory writable (both modes)
        $dirWritable = is_writable($appDir);
        $diagnostics['dir_permissions'] = [
            'label' => 'Direktori Writable',
            'status' => $dirWritable,
            'value' => $dirWritable ? 'Ya' : 'Tidak',
            'help' => 'Web server harus punya permission write',
        ];

        return $diagnostics;
    }

    private function isPullReady(array $diagnostics): bool
    {
        if ($this->canExec()) {
            return ($diagnostics['git_installed']['status'] ?? false)
                && ($diagnostics['git_directory']['status'] ?? false)
                && ($diagnostics['git_remote']['status'] ?? false)
                && ($diagnostics['dir_permissions']['status'] ?? false);
        }

        return ($diagnostics['repo_url']['status'] ?? false)
            && ($diagnostics['access_token']['status'] ?? false)
            && ($diagnostics['zip_archive']['status'] ?? false)
            && ($diagnostics['api_connection']['status'] ?? false)
            && ($diagnostics['dir_permissions']['status'] ?? false);
    }
}
