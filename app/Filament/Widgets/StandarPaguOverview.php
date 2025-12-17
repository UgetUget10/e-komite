<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StandarPaguOverview extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    public function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getColumns(): int
    {
        return 2;
    }

    // CUSTOM HEADER — INI YANG BIKIN TOMBOL MUNCUL!
    protected function getHeader(): ?string
    {
        $tahunAktifId = $this->getTahunAktifId();

        return view('filament.widgets.standar-pagu-anggaran-overview-header', [
            'tahunAktifId' => $tahunAktifId,
        ])->render();
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $tahunAktifId = $this->getTahunAktifId();

        if (! $tahunAktifId) {
            return [
                Stat::make('Tahun Anggaran', 'Tidak Aktif')
                    ->description('Hubungi admin untuk mengaktifkan tahun anggaran')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $tahunAnggaran = Cache::remember("tahun_label_{$tahunAktifId}", now()->addDay(), fn() =>
            DB::table('komite.tb_pagu_tahun_anggaran')->where('id', $tahunAktifId)->value('tahun')
        );

        $isSuperAdmin = $user->hasRole('Super Admin');
        $allowedStandarIds = $isSuperAdmin ? null : $user->standars()->pluck('standar_id')->toArray();

        $stats = Cache::remember("widget_pagu_{$user->id}_{$tahunAktifId}", now()->addMinutes(15), function () use ($tahunAktifId, $isSuperAdmin, $allowedStandarIds) {
            return DB::table('komite.tb_standar as s')
                ->leftJoin('komite.tb_program_kegiatan as pk', fn($j) => $j
                    ->on('pk.id_standar', '=', 's.id')
                    ->where('pk.fk_id_pagu_tahun_anggaran', $tahunAktifId)
                )
                ->select(
                    's.id',
                    's.nama_standar',
                    DB::raw('COUNT(pk.id) as jumlah_program'),
                    DB::raw('COALESCE(SUM(pk.total), 0) as total_pagu')
                )
                ->when(!$isSuperAdmin && $allowedStandarIds, fn($q) => $q->whereIn('s.id', $allowedStandarIds))
                ->groupBy('s.id', 's.nama_standar')
                ->orderBy('s.nama_standar')
                ->get();
        });

        $totalPagu = $stats->sum('total_pagu');
        $totalProgram = $stats->sum('jumlah_program');

        // $cards = [];

        // $cards[] = Stat::make("Total Keperluan Dana {$tahunAnggaran}", 'Rp. ' . number_format($totalPagu, 0, ',', '.'))
        //     ->description("{$stats->count()} standar • {$totalProgram} program aktif")
        //     ->descriptionIcon('heroicon-m-banknotes')
        //     ->color('primary');

        foreach ($stats as $item) {
            $cards[] = Stat::make("Keperluan Dana " . $item->nama_standar, 'Rp. ' . number_format($item->total_pagu, 0, ',', '.'))
                ->description("{$item->jumlah_program} program")
                ->descriptionIcon($this->getIconByStandar($item->nama_standar))
                ->color($this->getColorByStandar($item->nama_standar));
        }

        return $cards;
    }

    private function getTahunAktifId(): ?int
    {
        $id = Cache::remember('tahun_anggaran_aktif_id', now()->addHours(6), fn() =>
            DB::table('komite.tb_pagu_tahun_anggaran')->where('status_aktif', 1)->value('id')
        );
        return $id !== null ? (int) $id : null;
    }

    private function getColorByStandar(string $nama): string
    {
        return match (true) {
            str_contains($nama, 'Proses')       => 'success',
            str_contains($nama, 'Isi')          => 'info',
            str_contains($nama, 'Penilaian')    => 'warning',
            str_contains($nama, 'Pengelolaan')  => 'danger',
            str_contains($nama, 'PTT')          => 'primary',
            str_contains($nama, 'Sarpras')      => 'rose',
            str_contains($nama, 'Kelulusan')    => 'emerald',
            str_contains($nama, 'Pembiayaan')   => 'amber',
            default                             => 'gray',
        };
    }

    private function getIconByStandar(string $nama): string
    {
        return match (true) {
            str_contains($nama, 'Proses')       => 'heroicon-m-cog-8-tooth',
            str_contains($nama, 'Isi')          => 'heroicon-m-book-open',
            str_contains($nama, 'Penilaian')    => 'heroicon-m-clipboard-document-check',
            str_contains($nama, 'Pengelolaan')  => 'heroicon-m-building-office-2',
            str_contains($nama, 'PTT')          => 'heroicon-m-user-group',
            str_contains($nama, 'Sarpras')      => 'heroicon-m-building-library',
            str_contains($nama, 'Kelulusan')    => 'heroicon-m-academic-cap',
            str_contains($nama, 'Pembiayaan')   => 'heroicon-m-banknotes',
            default                             => 'heroicon-m-document-text',
        };
    }
}