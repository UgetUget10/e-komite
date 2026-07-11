<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BudgetVsActualChart extends ChartWidget
{
    use ScopesToUserStandar;

    protected ?string $heading = 'Perbandingan Pagu vs Realisasi (Top 15 Program)';
    protected static ?int $sort = 5;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();
        $tahunAktif = $this->activeTahunAnggaran();

        if (!$tahunAktif) {
            return ['datasets' => [], 'labels' => []];
        }

        // Query top 15 programs by pagu
        $data = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('id_standar', $allowedStandarIds))
            ->select('judul_program', 'pagu_komite', 'total_pengeluaran_perprogram')
            ->orderByDesc('pagu_komite')
            ->limit(15)
            ->get();

        if ($data->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $labels = $data->pluck('judul_program')->map(fn ($title) => substr($title, 0, 30))->toArray();
        $paguValues = $data->pluck('pagu_komite')->toArray();
        $realisasiValues = $data->pluck('total_pengeluaran_perprogram')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pagu Komite (Rp)',
                    'data' => $paguValues,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Realisasi (Rp)',
                    'data' => $realisasiValues,
                    'backgroundColor' => array_map(
                        fn ($idx) => $realisasiValues[$idx] > $paguValues[$idx] ? 'rgba(239, 68, 68, 0.6)' : 'rgba(34, 197, 94, 0.6)',
                        array_keys($paguValues)
                    ),
                    'borderColor' => array_map(
                        fn ($idx) => $realisasiValues[$idx] > $paguValues[$idx] ? 'rgba(239, 68, 68, 1)' : 'rgba(34, 197, 94, 1)',
                        array_keys($paguValues)
                    ),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
            ],
        ];
    }
}
