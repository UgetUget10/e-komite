<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\PaguTahunAnggaran;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use Filament\Widgets\Widget;

class BudgetVsRealisasiChart extends Widget
{
    use ScopesToUserStandar;

    protected string $view = 'filament.widgets.budget-vs-realisasi-chart';
    protected static ?int $sort = 2;

    public string $chartType = 'line';
    public int $selectedYear = 0;
    public int $selectedMonth = 0;
    public array $tahunList = [];
    public array $chartData = [];

    public function mount(): void
    {
        $tahunAktif = $this->activeTahunAnggaran();
        $this->selectedYear = $tahunAktif?->tahun ?? now()->year;
        $this->loadTahunList();
        $this->updateChartData();
    }

    public function loadTahunList(): void
    {
        $this->tahunList = PaguTahunAnggaran::pluck('tahun')
            ->sort()
            ->reverse()
            ->toArray();
    }

    public function updatedChartType(): void
    {
        $this->updateChartData();
    }

    public function updatedSelectedYear(): void
    {
        $this->updateChartData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->updateChartData();
    }

    public function updateChartData(): void
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

        $tahunAktif = PaguTahunAnggaran::where('tahun', $this->selectedYear)->first();
        if (!$tahunAktif) {
            $this->chartData = ['labels' => [], 'datasets' => []];
            return;
        }

        // Get monthly realisasi data
        $realisasiQuery = RealisasiNominal::query()
            ->selectRaw('MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total', [])
            ->whereYear('tanggal_realisasi', $this->selectedYear)
            ->whereNull('deleted_at');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $realisasiQuery->whereIn('fk_id_standar', $allowedStandarIds);
        }

        $realisasiData = $realisasiQuery->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Get monthly budget data
        $budgetQuery = ProgramKegiatan::query()
            ->selectRaw('MONTH(created_at) as bulan, SUM(pagu_komite) as total', [])
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id);

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $budgetQuery->whereIn('id_standar', $allowedStandarIds);
        }

        $budgetData = $budgetQuery->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Build monthly data
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $budgetDataPoints = [];
        $realisasiDataPoints = [];
        $chartMonths = [];

        // Jika filter bulan dipilih, tampilkan hanya bulan tersebut
        if ($this->selectedMonth > 0) {
            $months = [array_values($months)[$this->selectedMonth - 1]];
            $budgetDataPoints = [$budgetData[$this->selectedMonth] ?? 0];
            $realisasiDataPoints = [$realisasiData[$this->selectedMonth] ?? 0];
            $chartMonths = $months;
        } else {
            // Tampilkan semua 12 bulan
            for ($i = 1; $i <= 12; $i++) {
                $budgetDataPoints[] = $budgetData[$i] ?? 0;
                $realisasiDataPoints[] = $realisasiData[$i] ?? 0;
            }
            $chartMonths = $months;
        }

        $this->chartData = [
            'labels' => $chartMonths,
            'datasets' => [
                [
                    'label' => 'Budget Komite (Rp)',
                    'data' => $budgetDataPoints,
                ],
                [
                    'label' => 'Realisasi (Rp)',
                    'data' => $realisasiDataPoints,
                ],
            ],
        ];
    }
}
