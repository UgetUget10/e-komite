<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SpendingByStandarChart extends ChartWidget
{
    use ScopesToUserStandar;

    protected ?string $heading = 'Komposisi Realisasi Per Standar';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        // Jangan tampilkan untuk user dengan ≤1 standar (pie 1 slice tidak berguna)
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Pelaksana Komite', 'Kepala Madrasah'])) {
            return true;
        }
        $standarCount = $user->standars()->count();
        return $standarCount > 1;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();
        $tahunAktif = $this->activeTahunAnggaran();

        if (!$tahunAktif) {
            return ['datasets' => [], 'labels' => []];
        }

        // Query realisasi per standar
        $data = DB::table('komite.tb_realisasi_nominal as rn')
            ->join('komite.tb_standar as s', 'rn.fk_id_standar', '=', 's.id')
            ->where('rn.fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('rn.deleted_at')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('rn.fk_id_standar', $allowedStandarIds))
            ->selectRaw('s.id, s.nama_standar, SUM(rn.nominal) as total')
            ->groupBy('s.id', 's.nama_standar')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $labels = $data->pluck('nama_standar')->toArray();
        $values = $data->pluck('total')->toArray();

        // Color mapping (reuse dari StandarPaguOverview)
        $colors = $data->map(fn ($item) => $this->getColorByStandar($item->nama_standar))->toArray();
        $bgColors = array_map(fn ($color) => $this->getBackgroundColor($color), $colors);
        $borderColors = array_map(fn ($color) => $this->getBorderColor($color), $colors);

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => $bgColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                    'hoverOffset' => 15,
                ],
            ],
            'labels' => $labels,
        ];
    }

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

    private function getBackgroundColor(string $color): string
    {
        return match ($color) {
            'success' => 'rgba(16, 185, 129, 0.7)',
            'info' => 'rgba(59, 130, 246, 0.7)',
            'warning' => 'rgba(245, 158, 11, 0.7)',
            'danger' => 'rgba(239, 68, 68, 0.7)',
            'primary' => 'rgba(245, 158, 11, 0.7)',
            'rose' => 'rgba(244, 63, 94, 0.7)',
            'emerald' => 'rgba(16, 185, 129, 0.7)',
            'amber' => 'rgba(249, 115, 22, 0.7)',
            default => 'rgba(156, 163, 175, 0.7)',
        };
    }

    private function getBorderColor(string $color): string
    {
        return match ($color) {
            'success' => 'rgb(16, 185, 129)',
            'info' => 'rgb(59, 130, 246)',
            'warning' => 'rgb(245, 158, 11)',
            'danger' => 'rgb(239, 68, 68)',
            'primary' => 'rgb(245, 158, 11)',
            'rose' => 'rgb(244, 63, 94)',
            'emerald' => 'rgb(16, 185, 129)',
            'amber' => 'rgb(249, 115, 22)',
            default => 'rgb(156, 163, 175)',
        };
    }
}
