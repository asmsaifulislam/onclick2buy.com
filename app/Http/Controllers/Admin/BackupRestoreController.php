<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupRestoreController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'zip') {
                    $backups[] = [
                        'name' => $file->getFilename(),
                        'size' => $this->formatSize($file->getSize()),
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                        'path' => $file->getPathname(),
                    ];
                }
            }
        }

        rsort($backups);

        return view('admin.backup-restore', [
            'backups' => $backups,
            'dbSize' => $this->formatSize(File::exists(database_path('database.sqlite')) ? File::size(database_path('database.sqlite')) : 0),
            'storageSize' => $this->formatSize($this->dirSize(storage_path('app/public'))),
        ]);
    }

    public function backup()
    {
        return $this->createBackup('data', false);
    }

    public function backupFull()
    {
        set_time_limit(300);
        return $this->createBackup('full', true);
    }

    private function createBackup($type, $includeCode)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');
        $prefix = $type === 'full' ? 'full-site' : 'data-backup';
        $backupFile = "{$backupDir}/{$prefix}-{$timestamp}.zip";

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create backup archive');
        }

        $dbPath = database_path('database.sqlite');
        if (File::exists($dbPath)) {
            $zip->addFile($dbPath, 'database/database.sqlite');
        }

        $publicPath = storage_path('app/public');
        if (is_dir($publicPath)) {
            $this->zipDir($zip, $publicPath, 'storage/app/public');
        }

        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $zip->addFile($envPath, '.env');
        }

        if ($includeCode) {
            $this->zipProject($zip, base_path());
        }

        $zip->close();

        return back()->with('success', "Backup created: {$prefix}-{$timestamp}.zip");
    }

    private function zipProject($zip, $root)
    {
        $excludeDirs = [
            $root . '/vendor',
            $root . '/node_modules',
            $root . '/.git',
            $root . '/storage/app/backups',
            $root . '/storage/app/restore_temp',
        ];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $path = $file->getPathname();

            $excluded = false;
            foreach ($excludeDirs as $exDir) {
                if (strpos($path, $exDir . DIRECTORY_SEPARATOR) === 0 || $path === $exDir) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) continue;

            $localPath = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
            $localPath = str_replace('\\', '/', $localPath);

            if ($file->isDir()) {
                $zip->addEmptyDir($localPath);
            } else {
                $zip->addFile($path, $localPath);
            }
        }
    }

    public function download($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (!File::exists($path)) {
            return back()->with('error', 'Backup file not found');
        }

        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|mimes:zip|max:512000',
        ]);

        $file = $request->file('backup_file');
        $zip = new ZipArchive();

        if ($zip->open($file->getPathname()) !== true) {
            return back()->with('error', 'Failed to open backup archive');
        }

        $extractPath = storage_path('app/restore_temp');
        if (is_dir($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        mkdir($extractPath, 0755, true);

        $zip->extractTo($extractPath);
        $zip->close();

        if (File::exists("{$extractPath}/database/database.sqlite")) {
            File::copy("{$extractPath}/database/database.sqlite", database_path('database.sqlite'));
        }

        if (is_dir("{$extractPath}/public")) {
            $dest = storage_path('app/public');
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            File::copyDirectory("{$extractPath}/public", $dest);
        }

        if (File::exists("{$extractPath}/.env")) {
            File::copy("{$extractPath}/.env", base_path('.env'));
        }

        File::deleteDirectory($extractPath);

        return back()->with('success', 'Backup restored successfully. Please clear your cache if needed.');
    }

    public function destroy($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (File::exists($path)) {
            File::delete($path);
            return back()->with('success', "Backup deleted: {$filename}");
        }

        return back()->with('error', 'Backup file not found');
    }

    private function zipDir($zip, $dir, $relativePath)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $localPath = $relativePath . '/' . $files->getSubPathName();
            if ($file->isDir()) {
                $zip->addEmptyDir($localPath);
            } else {
                $zip->addFile($file->getPathname(), $localPath);
            }
        }
    }

    private function dirSize($path)
    {
        if (!is_dir($path)) return 0;

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)) as $f) {
            $size += $f->getSize();
        }
        return $size;
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
