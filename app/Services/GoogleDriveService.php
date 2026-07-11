<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class GoogleDriveService
{
    protected function binary(): string
    {
        return config('services.rclone.binary', 'rclone');
    }

    protected function remote(): string
    {
        return config('services.rclone.remote', 'gdrive');
    }

    protected function baseArgs(): array
    {
        $args = [];

        $configPath = config('services.rclone.config_path');
        if (filled($configPath)) {
            $args[] = '--config';
            $args[] = $configPath;
        }

        return $args;
    }

    /**
     * Upload file lokal ke path folder tertentu di remote rclone.
     * rclone otomatis membuat folder tujuan kalau belum ada.
     * Return array berisi 'id', 'webViewLink', dan 'remotePath'.
     */
    public function uploadFile(string $localAbsolutePath, string $filename, string $folderPath): array
    {
        if (!file_exists($localAbsolutePath)) {
            throw new RuntimeException("File lokal tidak ditemukan: {$localAbsolutePath}");
        }

        $remoteTarget = "{$this->remote()}:{$folderPath}/{$filename}";

        $result = Process::run([
            $this->binary(),
            ...$this->baseArgs(),
            'copyto',
            $localAbsolutePath,
            $remoteTarget,
        ]);

        if ($result->failed()) {
            throw new RuntimeException("rclone copyto gagal: {$result->errorOutput()}");
        }

        $remotePath = "{$folderPath}/{$filename}";
        $fileId = $this->getFileId($remotePath);
        $webViewLink = $fileId
            ? "https://drive.google.com/file/d/{$fileId}/view"
            : null;

        return [
            'id' => $fileId,
            'webViewLink' => $webViewLink,
            'remotePath' => $remotePath,
        ];
    }

    /**
     * Ambil Google Drive file ID dari path relatif di remote via `rclone lsjson`.
     */
    protected function getFileId(string $remotePath): ?string
    {
        $folder = dirname($remotePath);
        $filename = basename($remotePath);

        $result = Process::run([
            $this->binary(),
            ...$this->baseArgs(),
            'lsjson',
            "{$this->remote()}:{$folder}",
        ]);

        if ($result->failed()) {
            Log::warning('rclone lsjson gagal saat ambil file ID', [
                'remote_path' => $remotePath,
                'error' => $result->errorOutput(),
            ]);
            return null;
        }

        $entries = json_decode($result->output(), true) ?: [];

        foreach ($entries as $entry) {
            if (($entry['Name'] ?? null) === $filename) {
                return $entry['ID'] ?? null;
            }
        }

        return null;
    }

    /**
     * Hapus file di remote berdasarkan path relatif (folder/filename).
     */
    public function deleteFile(string $remotePath): void
    {
        $result = Process::run([
            $this->binary(),
            ...$this->baseArgs(),
            'deletefile',
            "{$this->remote()}:{$remotePath}",
        ]);

        if ($result->failed()) {
            // File mungkin sudah terhapus manual di Drive — abaikan agar tidak menggagalkan job.
            Log::warning('rclone deletefile gagal (kemungkinan file sudah tidak ada)', [
                'remote_path' => $remotePath,
                'error' => $result->errorOutput(),
            ]);
        }
    }
}
