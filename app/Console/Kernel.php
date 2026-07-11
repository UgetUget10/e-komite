<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Daily database backup to Google Drive (02:00 AM)
        $schedule->command('backup:to-google-drive')
            ->dailyAt('02:00')
            ->name('Daily Database Backup to Google Drive')
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Daily backup berhasil dijalankan.');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('❌ Daily backup gagal.');
            });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
