<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Container\Attributes\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StandarPaguOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {

        $user = Auth::user();

        // Ambil ID tahun anggaran yang aktif (status_aktif = 1)
        $tahunAktifId = DB::table('komite.tb_pagu_tahun_anggaran')
            ->where('status_aktif', 1)
            ->value('id');

        // Jika tidak ada tahun aktif → kembalikan widget kosong
        if (!$tahunAktifId) {
            return [
                Stat::make('Tahun Anggaran', 'Belum ada tahun aktif')
                    ->description('Hubungi admin untuk mengaktifkan tahun anggaran')
                    ->color('danger'),
            ];
        }

        // Cek apakah user Super Admin
        $isSuperAdmin = $user->hasRole('Super Admin');

        // Ambil standar yang diizinkan (kecuali super admin)
        $allowedStandarIds = $isSuperAdmin
            ? null // akan diabaikan di query
            : DB::table('komite.tb_standar_user')
                ->where('user_id', $user->id)
                ->pluck('standar_id')
                ->toArray();

        // Query utama: hanya program dari tahun aktif
        $stats = DB::table('komite.tb_standar as s')
            ->leftJoin('komite.tb_program_kegiatan as pk', function ($join) use ($tahunAktifId) {
                $join->on('pk.id_standar', '=', 's.id')
                    ->where('pk.fk_id_pagu_tahun_anggaran', '=', $tahunAktifId);
            })
            ->select(
                's.nama_standar',
                DB::raw('COUNT(pk.id) as jumlah_program'),
                DB::raw('COALESCE(SUM(pk.total), 0) as total_pagu')
            )
            ->when(!$isSuperAdmin && $allowedStandarIds, function ($q) use ($allowedStandarIds) {
                return $q->whereIn('s.id', $allowedStandarIds);
            })
            ->groupBy('s.id', 's.nama_standar')
            ->orderBy('s.nama_standar')
            ->get();

        // Total keseluruhan
        $totalKeseluruhan = $stats->sum('total_pagu');
        $jumlahStandar = $stats->count();

        $cards = [];

        // Opsional: Tampilkan total keseluruhan di atas
        $cards[] = Stat::make('Total Pagu Tahun ' . DB::table('komite.tb_pagu_tahun_anggaran')->where('id', $tahunAktifId)->value('tahun'),
                'Rp ' . number_format($totalKeseluruhan, 0, ',', '.'))
            ->description("{$jumlahStandar} standar • {$stats->sum('jumlah_program')} program")
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('primary');

        // Card per standar
        foreach ($stats as $item) {
            $cards[] = Stat::make($item->nama_standar, 'Rp ' . number_format($item->total_pagu, 0, ',', '.'))
                ->description($item->jumlah_program . ' program')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($this->getColorByStandar($item->nama_standar));
        }

        return $cards;
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


}