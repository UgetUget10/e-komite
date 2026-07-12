<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Dashboard;
use App\Models\PaguTahunAnggaran;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use App\Models\Standar;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DetailKeperluanDana extends Page
{
    protected string $view = 'filament.pages.detail-keperluan-dana';

    protected static bool $shouldRegisterNavigation = false;

    public ?Standar $standar = null;

    public ?PaguTahunAnggaran $tahunAktif = null;

    public string $activeTab = 'pagu';

    /** @var array<int, array{judul_program: string, pagu_dipa: float, pagu_bos: float, pagu_komite: float}> */
    public array $rows = [];

    public float $totalDipa = 0;
    public float $totalBos = 0;
    public float $totalKomite = 0;

    /** @var array<int, array{judul_program: string, pagu_komite: float, realisasi: float, sisa: float}> */
    public array $realisasiRows = [];

    public float $totalKomiteRealisasi = 0;
    public float $totalRealisasi = 0;
    public float $totalSisa = 0;

    /** @var array<int, string> */
    public array $bulanLabels = [];

    /** @var array<int, array{judul_program: string, bulan: array<int, float>, total: float}> */
    public array $bulananRows = [];

    /** @var array<int, float> */
    public array $totalPerBulan = [];

    public float $totalRealisasiBulanan = 0;

    public static function getRoutePath(Panel $panel): string
    {
        return '/detail-keperluan-dana/{standarId}';
    }

    public function mount(string $standarId): void
    {
        $this->standar = Standar::findOrFail($standarId);

        $user = Auth::user();
        if (!$user->hasRole('Super Admin')) {
            $allowedIds = $user->standars()->pluck('id')->toArray();
            abort_unless(in_array($this->standar->id, $allowedIds), 403);
        }

        $this->tahunAktif = PaguTahunAnggaran::where('status_aktif', 1)->first();

        $programs = ProgramKegiatan::where('id_standar', $this->standar->id)
            ->when($this->tahunAktif, fn ($q) => $q->where('fk_id_pagu_tahun_anggaran', $this->tahunAktif->id))
            ->orderBy('kode')
            ->get();

        $this->rows = $programs->map(fn ($program) => [
            'judul_program' => $program->judul_program,
            'pagu_dipa' => (float) ($program->pagu_dipa ?? 0),
            'pagu_bos' => (float) ($program->pagu_bos ?? 0),
            'pagu_komite' => (float) ($program->pagu_komite ?? 0),
        ])->all();

        $this->totalDipa = $programs->sum('pagu_dipa');
        $this->totalBos = $programs->sum('pagu_bos');
        $this->totalKomite = $programs->sum('pagu_komite');

        $realisasiPerProgram = RealisasiNominal::whereIn('fk_id_program_kegiatan', $programs->pluck('id'))
            ->whereNull('deleted_at')
            ->selectRaw('fk_id_program_kegiatan, SUM(nominal) as total')
            ->groupBy('fk_id_program_kegiatan')
            ->pluck('total', 'fk_id_program_kegiatan');

        $this->realisasiRows = $programs->map(function ($program) use ($realisasiPerProgram) {
            $komite = (float) ($program->pagu_komite ?? 0);
            $realisasi = (float) ($realisasiPerProgram[$program->id] ?? 0);

            return [
                'judul_program' => $program->judul_program,
                'pagu_komite' => $komite,
                'realisasi' => $realisasi,
                'sisa' => $komite - $realisasi,
            ];
        })->all();

        $this->totalKomiteRealisasi = $this->totalKomite;
        $this->totalRealisasi = array_sum(array_column($this->realisasiRows, 'realisasi'));
        $this->totalSisa = $this->totalKomiteRealisasi - $this->totalRealisasi;

        // ===== Realisasi per bulan (Jan-Des) =====
        $this->bulanLabels = collect(range(1, 12))
            ->map(fn ($m) => Carbon::create()->month($m)->translatedFormat('M'))
            ->all();

        $tahunKalender = $this->tahunAktif ? (int) $this->tahunAktif->tahun : (int) now()->year;

        $realisasiPerBulanPerProgram = RealisasiNominal::whereIn('fk_id_program_kegiatan', $programs->pluck('id'))
            ->whereNull('deleted_at')
            ->whereYear('tanggal_realisasi', $tahunKalender)
            ->selectRaw('fk_id_program_kegiatan, MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total')
            ->groupBy('fk_id_program_kegiatan', 'bulan')
            ->get()
            ->groupBy('fk_id_program_kegiatan');

        $this->bulananRows = $programs->map(function ($program) use ($realisasiPerBulanPerProgram) {
            $perBulan = $realisasiPerBulanPerProgram->get($program->id, collect())
                ->pluck('total', 'bulan');

            $bulan = collect(range(1, 12))
                ->map(fn ($m) => (float) ($perBulan[$m] ?? 0))
                ->all();

            return [
                'judul_program' => $program->judul_program,
                'bulan' => $bulan,
                'total' => array_sum($bulan),
            ];
        })->all();

        $this->totalPerBulan = collect(range(0, 11))
            ->map(function ($idx) {
                return collect($this->bulananRows)->sum(fn ($row) => $row['bulan'][$idx]);
            })
            ->all();

        $this->totalRealisasiBulanan = array_sum($this->totalPerBulan);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getPageClasses(): array
    {
        return ['fi-page-detail-keperluan-dana'];
    }

    public function getTitle(): string
    {
        return 'Keperluan Dana ' . ($this->standar->nama_standar ?? '-');
    }

    public function getHeading(): string
    {
        return 'Keperluan Dana ' . ($this->standar->nama_standar ?? '-');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Dasbor')
                ->icon('heroicon-o-arrow-left')
                ->url(Dashboard::getUrl())
                ->color('success'),

            Action::make('ekspor_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $judulSuffix = match ($this->activeTab) {
                        'realisasi' => ' - Realisasi',
                        'bulanan' => ' - Realisasi Bulanan',
                        default => '',
                    };

                    $pdf = Pdf::loadView('pdf.detail-keperluan-dana', [
                        'judul' => 'Keperluan Dana ' . $this->standar->nama_standar . $judulSuffix,
                        'periodeLabel' => 'Tahun Anggaran ' . ($this->tahunAktif->tahun ?? '-'),
                        'activeTab' => $this->activeTab,
                        'rows' => $this->rows,
                        'totalDipa' => $this->totalDipa,
                        'totalBos' => $this->totalBos,
                        'totalKomite' => $this->totalKomite,
                        'realisasiRows' => $this->realisasiRows,
                        'totalKomiteRealisasi' => $this->totalKomiteRealisasi,
                        'totalRealisasi' => $this->totalRealisasi,
                        'totalSisa' => $this->totalSisa,
                        'bulanLabels' => $this->bulanLabels,
                        'bulananRows' => $this->bulananRows,
                        'totalPerBulan' => $this->totalPerBulan,
                        'totalRealisasiBulanan' => $this->totalRealisasiBulanan,
                    ])->setPaper('a4', $this->activeTab === 'bulanan' ? 'landscape' : 'portrait');

                    $fileSuffix = match ($this->activeTab) {
                        'realisasi' => 'realisasi',
                        'bulanan' => 'realisasi-bulanan',
                        default => 'pagu',
                    };

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'keperluan-dana-' . str($this->standar->nama_standar)->slug() . '-' . $fileSuffix . '.pdf',
                    );
                }),
        ];
    }
}
