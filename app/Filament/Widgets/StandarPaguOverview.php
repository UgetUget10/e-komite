<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StandarPaguOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        $userId = $user->id;

        // Cek apakah user adalah Super Admin
        $isAdmin = DB::table('users')
            ->where('id', $userId)
            ->where('name', 'Super Admin')
            ->exists();

        // Ambil standar yang diizinkan (kecuali admin)
        $allowedStandarIds = $isAdmin ? [] : $this->getUserStandarIds($userId);

        // Query utama: ambil data per standar
        $stats = DB::table('komite.tb_standar as s')
            ->leftJoin('komite.tb_program_kegiatan as pk', 'pk.id_standar', '=', 's.id')
            ->select(
                's.nama_standar',
                DB::raw('COUNT(pk.id) as jumlah_program'),
                DB::raw('COALESCE(SUM(pk.total), 0) as total_pagu')
            )
            ->when(!empty($allowedStandarIds), function ($query) use ($allowedStandarIds) {
                return $query->whereIn('s.id', $allowedStandarIds);
            })
            ->groupBy('s.id', 's.nama_standar')
            ->orderBy('s.nama_standar')
            ->get();

        // Hitung total keseluruhan dari hasil query
        $totalKeseluruhan = $stats->sum('total_pagu'); // ← PAKAI total_pagu (alias)
        $jumlahStandar = $stats->count();

        $cards = [];

        // 1. Total Pagu Keseluruhan
        // $cards[] = Stat::make('Total Pagu Standar', 'Rp. ' . number_format($totalKeseluruhan, 0, ',', '.'))
        //     ->description("{$jumlahStandar} standar")
        //     ->descriptionIcon('heroicon-m-banknotes')
        //     ->color('primary');

        // 2. Per Standar
        foreach ($stats as $item) {
            $cards[] = Stat::make(
                $item->nama_standar,
                'Rp. ' . number_format($item->total_pagu, 0, ',', '.')
            )
                ->description("{$item->jumlah_program} program")
                ->descriptionIcon('heroicon-m-document-text')
                ->color($this->getColorByStandar($item->nama_standar));
        }

        return $cards;
    }

    /**
     * Ambil ID standar milik user
     */
    private function getUserStandarIds(string $userId): array
    {
        return DB::table('komite.tb_standar_user')
            ->where('user_id', $userId)
            ->pluck('standar_id')
            ->toArray();
    }

    /**
     * Tentukan warna berdasarkan nama standar
     */
    private function getColorByStandar(string $nama): string
    {
        return match (true) {
            str_contains($nama, 'Proses') => 'success',
            str_contains($nama, 'Isi') => 'info',
            str_contains($nama, 'Penilaian') => 'warning',
            str_contains($nama, 'Pengelolaan') => 'danger',
            str_contains($nama, 'PTT') => 'primary',
            str_contains($nama, 'Sarpras') => 'rose',
            str_contains($nama, 'Kelulusan') => 'emerald',
            str_contains($nama, 'Pembiayaan') => 'amber',
            default => 'gray',
        };
    }
}