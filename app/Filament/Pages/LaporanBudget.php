<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\PaguTahunAnggaran;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use App\Models\Standar;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanBudget extends Page
{
    use ScopesToUserStandar;

    protected static ?string $navigationLabel = 'Laporan Budget';
    protected static ?int $navigationSort = 8;
    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-chart-bar';
    }

    protected string $view = 'filament.pages.laporan-budget';

    public ?PaguTahunAnggaran $tahunAktif = null;
    public array $laporanData = [];
    public array $breakdownByStandar = [];
    public array $breakdownByBulan = [];
    public array $standarOptions = [];
    public string $searchProgram = '';
    public array $selectedStandars = [];
    public string $viewType = 'program';

    public function mount(): void
    {
        $this->tahunAktif = $this->activeTahunAnggaran();
        $this->loadStandarOptions();
        if ($this->tahunAktif) {
            $this->generateLaporanData();
        }
    }

    public function loadStandarOptions(): void
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

        $query = Standar::query();
        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $query->whereIn('id', $allowedStandarIds);
        }

        $this->standarOptions = $query->pluck('nama_standar', 'id')->toArray();
    }

    public function updatedSearchProgram(): void
    {
        $this->generateLaporanData();
    }

    public function updatedSelectedStandars(): void
    {
        $this->generateLaporanData();
    }

    public function updatedViewType(): void
    {
        $this->generateLaporanData();
    }

    public function generateLaporanData(): void
    {
        if (!$this->tahunAktif) {
            return;
        }

        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

        // Get all programs
        $query = ProgramKegiatan::query()
            ->where('fk_id_pagu_tahun_anggaran', $this->tahunAktif->id);

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $query->whereIn('id_standar', $allowedStandarIds);
        }

        // Apply filters
        if (!empty($this->selectedStandars)) {
            $query->whereIn('id_standar', $this->selectedStandars);
        }

        if (!empty($this->searchProgram)) {
            $query->where('judul_program', 'like', '%' . $this->searchProgram . '%');
        }

        $programs = $query->get();

        $this->laporanData = [
            'tahun' => $this->tahunAktif->tahun,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'total_pagu' => 0,
            'total_realisasi' => 0,
            'programs' => [],
        ];

        // Breakdown by standar
        $this->breakdownByStandar = [];
        $this->breakdownByBulan = [];

        foreach ($programs as $program) {
            $realisasi = RealisasiNominal::where('fk_id_program_kegiatan', $program->id)
                ->whereNull('deleted_at')
                ->sum('nominal');

            $standarName = $program->standar->nama_standar ?? '-';

            $this->laporanData['programs'][] = [
                'id_standar' => $program->id_standar,
                'standar' => $standarName,
                'judul' => $program->judul_program,
                'pagu' => $program->pagu_komite,
                'realisasi' => $realisasi,
                'sisa' => $program->pagu_komite - $realisasi,
                'percentage' => $program->pagu_komite > 0 ? ($realisasi / $program->pagu_komite) * 100 : 0,
            ];

            // Breakdown by standar
            if (!isset($this->breakdownByStandar[$standarName])) {
                $this->breakdownByStandar[$standarName] = [
                    'pagu' => 0,
                    'realisasi' => 0,
                    'sisa' => 0,
                    'count' => 0,
                ];
            }
            $this->breakdownByStandar[$standarName]['pagu'] += $program->pagu_komite;
            $this->breakdownByStandar[$standarName]['realisasi'] += $realisasi;
            $this->breakdownByStandar[$standarName]['sisa'] += $program->pagu_komite - $realisasi;
            $this->breakdownByStandar[$standarName]['count']++;

            // Breakdown by bulan
            $realisasiBulan = RealisasiNominal::where('fk_id_program_kegiatan', $program->id)
                ->whereNull('deleted_at')
                ->get()
                ->groupBy(fn ($item) => $item->tanggal_realisasi->format('M Y'));

            foreach ($realisasiBulan as $bulan => $items) {
                if (!isset($this->breakdownByBulan[$bulan])) {
                    $this->breakdownByBulan[$bulan] = [
                        'nominal' => 0,
                        'count' => 0,
                    ];
                }
                $this->breakdownByBulan[$bulan]['nominal'] += $items->sum('nominal');
                $this->breakdownByBulan[$bulan]['count'] += $items->count();
            }

            $this->laporanData['total_pagu'] += $program->pagu_komite;
            $this->laporanData['total_realisasi'] += $realisasi;
        }

        // Sort breakdown by bulan
        ksort($this->breakdownByBulan);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_csv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(fn () => $this->downloadCsv()),
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(fn () => $this->downloadExcel()),
            Action::make('generate_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->downloadPdf()),
        ];
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.laporan-budget', [
            'data' => $this->laporanData,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-budget-' . $this->tahunAktif->tahun . '-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function downloadCsv()
    {
        $headers = ['NO', 'STANDAR', 'PROGRAM KEGIATAN', 'PAGU (Rp)', 'REALISASI (Rp)', 'SISA (Rp)', '%'];
        $rows = [];

        foreach ($this->laporanData['programs'] as $index => $program) {
            $rows[] = [
                $index + 1,
                $program['standar'],
                $program['judul'],
                $program['pagu'],
                $program['realisasi'],
                $program['sisa'],
                round($program['percentage'], 1),
            ];
        }

        $rows[] = ['', '', 'TOTAL', $this->laporanData['total_pagu'], $this->laporanData['total_realisasi'], $this->laporanData['total_pagu'] - $this->laporanData['total_realisasi'], ''];

        return response()->streamDownload(function () use ($headers, $rows) {
            $fp = fopen('php://output', 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($fp, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($fp, $row, ';');
            }

            fclose($fp);
        }, 'laporan-budget-' . $this->tahunAktif->tahun . '-' . now()->format('Y-m-d') . '.csv');
    }

    public function downloadExcel()
    {
        // Generate Excel-compatible HTML format yang bisa dibuka di Excel
        $filename = 'laporan-budget-' . $this->tahunAktif->tahun . '-' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            echo "<?mso-application progid=\"Excel.Sheet\"?>\n";
            echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
            echo "<Worksheet ss:Name=\"Laporan Budget\">\n";
            echo "<Table>\n";

            // Header
            echo "<Row>\n";
            $headers = ['NO', 'STANDAR', 'PROGRAM KEGIATAN', 'PAGU (Rp)', 'REALISASI (Rp)', 'SISA (Rp)', '%'];
            foreach ($headers as $header) {
                echo "<Cell ss:StyleID=\"s1\"><Data ss:Type=\"String\">" . htmlspecialchars($header) . "</Data></Cell>\n";
            }
            echo "</Row>\n";

            // Data
            foreach ($this->laporanData['programs'] as $index => $program) {
                echo "<Row>\n";
                echo "<Cell><Data ss:Type=\"Number\">" . ($index + 1) . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($program['standar']) . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($program['judul']) . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"Number\">" . $program['pagu'] . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"Number\">" . $program['realisasi'] . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"Number\">" . $program['sisa'] . "</Data></Cell>\n";
                echo "<Cell><Data ss:Type=\"Number\">" . round($program['percentage'], 1) . "</Data></Cell>\n";
                echo "</Row>\n";
            }

            // Total row
            echo "<Row>\n";
            echo "<Cell></Cell>\n";
            echo "<Cell></Cell>\n";
            echo "<Cell ss:StyleID=\"s2\"><Data ss:Type=\"String\">TOTAL</Data></Cell>\n";
            echo "<Cell ss:StyleID=\"s2\"><Data ss:Type=\"Number\">" . $this->laporanData['total_pagu'] . "</Data></Cell>\n";
            echo "<Cell ss:StyleID=\"s2\"><Data ss:Type=\"Number\">" . $this->laporanData['total_realisasi'] . "</Data></Cell>\n";
            echo "<Cell ss:StyleID=\"s2\"><Data ss:Type=\"Number\">" . ($this->laporanData['total_pagu'] - $this->laporanData['total_realisasi']) . "</Data></Cell>\n";
            echo "<Cell></Cell>\n";
            echo "</Row>\n";

            echo "</Table>\n";
            echo "<Styles>\n";
            echo "<Style ss:ID=\"s1\"><Font ss:Bold=\"1\"/><Interior ss:Color=\"FFC000\" ss:Pattern=\"Solid\"/></Style>\n";
            echo "<Style ss:ID=\"s2\"><Font ss:Bold=\"1\"/></Style>\n";
            echo "</Styles>\n";
            echo "</Worksheet>\n";
            echo "</Workbook>\n";
        }, $filename);
    }
}
