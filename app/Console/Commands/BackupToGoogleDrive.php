<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class BackupToGoogleDrive extends Command
{
    protected $signature = 'backup:to-google-drive';

    protected $description = 'Backup database ke file .sql dan upload ke Google Drive';

    public function handle(GoogleDriveService $googleDrive): int
    {
        try {
            $this->info('🔄 Mulai backup database...');

            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "komite-backup-{$timestamp}.sql";
            $backupFile = "{$backupDir}/{$filename}";

            $dbHost = config('database.connections.mysql.host');
            $dbUser = config('database.connections.mysql.username');
            $dbPassword = config('database.connections.mysql.password');
            $dbName = config('database.connections.mysql.database');
            $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump');

            $cmd = "\"{$mysqldumpPath}\" --host={$dbHost} --user={$dbUser}";
            if ($dbPassword) {
                $cmd .= " --password={$dbPassword}";
            }
            $cmd .= " {$dbName} > \"{$backupFile}\"";

            $this->line("Menjalankan: mysqldump...");
            $result = Process::run($cmd);

            if ($result->exitCode() !== 0) {
                throw new \Exception("mysqldump gagal: " . $result->errorOutput());
            }

            if (!file_exists($backupFile)) {
                throw new \Exception("Backup file tidak terbuat: {$backupFile}");
            }

            $fileSize = filesize($backupFile);
            $this->info("✅ Database backup berhasil: {$filename} ({$fileSize} bytes)");

            $this->line("📤 Upload ke Google Drive...");
            $folderPath = 'realisasi RKAM/backup';

            $uploaded = $googleDrive->uploadFile($backupFile, $filename, $folderPath);

            $this->info('✅ Upload sukses!');
            $this->info('File ID: ' . $uploaded['id']);
            $this->info('URL: ' . $uploaded['webViewLink']);

            unlink($backupFile);
            $this->info('✅ File lokal dihapus.');

            Log::info('Database backup uploaded to Google Drive', [
                'file' => $filename,
                'file_size' => $fileSize,
                'file_id' => $uploaded['id'],
                'folder_path' => $folderPath,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Backup gagal: ' . $e->getMessage());

            Log::error('Gagal backup/upload ke Google Drive', [
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }
}
