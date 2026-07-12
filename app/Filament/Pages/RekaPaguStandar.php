<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use App\Models\SignatureSetting;
use App\Models\Standar;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;

class RekaPaguStandar extends Page
{
    use ScopesToUserStandar;

    protected static ?string $navigationLabel = 'Rekap Pagu Standar';
    protected static ?string $title = 'Rekap Pagu Per Standar';
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.reka-pagu-standar';

    public function getData(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

        // Get active tahun anggaran
        $tahunAktif = $this->activeTahunAnggaran();
        if (!$tahunAktif) {
            return [
                'tahun' => 'Tidak ada',
                'namaSekolah' => 'PT. KomiTeeh',
                'rows' => [],
                'totals' => ['dipa' => 0, 'bos' => 0, 'komite' => 0, 'grand_total' => 0],
            ];
        }

        // Get standar data with eager loading
        $standarQuery = Standar::query();

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $standarQuery->whereIn('id', $allowedStandarIds);
        }

        $standars = $standarQuery->with(['programkegiatan' => function ($q) use ($tahunAktif) {
            $q->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id);
        }])->orderBy('kode')->get();

        // Kumpulkan semua id program untuk 1 query realisasi (hindari N+1)
        $allProgramIds = $standars->pluck('programkegiatan')->flatten()->pluck('id')->all();

        $realisasiSelesai = RealisasiNominal::whereIn('fk_id_program_kegiatan', $allProgramIds)
            ->where('status', RealisasiNominal::STATUS_SELESAI)
            ->selectRaw('fk_id_program_kegiatan, SUM(nominal) as total')
            ->groupBy('fk_id_program_kegiatan')
            ->pluck('total', 'fk_id_program_kegiatan');

        $realisasiBerjalan = RealisasiNominal::whereIn('fk_id_program_kegiatan', $allProgramIds)
            ->whereIn('status', RealisasiNominal::STATUSES_BERJALAN)
            ->selectRaw('fk_id_program_kegiatan, SUM(nominal) as total')
            ->groupBy('fk_id_program_kegiatan')
            ->pluck('total', 'fk_id_program_kegiatan');

        $rows = [];
        $totalDipa = 0;
        $totalBos = 0;
        $totalKomite = 0;
        $totalRealisasi = 0;
        $totalPengajuanBerjalan = 0;
        $totalPengembalian = 0;
        $totalSisa = 0;

        foreach ($standars as $index => $standar) {
            // Use eager-loaded programs
            $programs = $standar->programkegiatan;

            $dipa = (int) $programs->sum('pagu_dipa');
            $bos = (int) $programs->sum('pagu_bos');
            $komite = (int) $programs->sum('pagu_komite');
            $pengembalian = (int) $programs->sum('pengembalian_sisa_anggaran');

            $realisasi = 0;
            $pengajuanBerjalan = 0;
            foreach ($programs as $program) {
                $realisasi += (float) ($realisasiSelesai[$program->id] ?? 0);
                $pengajuanBerjalan += (float) ($realisasiBerjalan[$program->id] ?? 0);
            }

            $sisa = $komite - $realisasi - $pengajuanBerjalan + $pengembalian;

            $rows[] = [
                'no' => $index + 1,
                'nama_standar' => $standar->nama_standar,
                'dipa' => $dipa,
                'bos' => $bos,
                'komite' => $komite,
                'realisasi' => $realisasi,
                'pengajuan_berjalan' => $pengajuanBerjalan,
                'pengembalian' => $pengembalian,
                'sisa' => $sisa,
                'total' => $dipa + $bos + $komite,
            ];

            $totalDipa += $dipa;
            $totalBos += $bos;
            $totalKomite += $komite;
            $totalRealisasi += $realisasi;
            $totalPengajuanBerjalan += $pengajuanBerjalan;
            $totalPengembalian += $pengembalian;
            $totalSisa += $sisa;
        }

        return [
            'tahun' => $tahunAktif->tahun,
            'namaSekolah' => 'MTsN 1 Kota Malang',
            'rows' => $rows,
            'totals' => [
                'dipa' => $totalDipa,
                'bos' => $totalBos,
                'komite' => $totalKomite,
                'realisasi' => $totalRealisasi,
                'pengajuan_berjalan' => $totalPengajuanBerjalan,
                'pengembalian' => $totalPengembalian,
                'sisa' => $totalSisa,
                'grand_total' => $totalDipa + $totalBos + $totalKomite,
            ],
        ];
    }

    public function getViewData(): array
    {
        return [
            'data' => $this->getData(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),

            Action::make('export_csv')
                ->label('Ekspor Excel (CSV)')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportPdf()
    {
        $data = $this->getData();
        $signatures = SignatureSetting::first() ?? SignatureSetting::create([]);

        $data['ketua_komite_name'] = $signatures->ketua_komite_name;
        $data['kepala_sekolah_name'] = $signatures->kepala_sekolah_name;
        $data['signature_day'] = $signatures->signature_day;
        $data['signature_month'] = $signatures->signature_month;
        $data['signature_year'] = $signatures->signature_year;

        // Format tanggal lengkap
        $tanggal = '';
        if ($signatures->signature_day > 0) {
            $tanggal = $signatures->signature_day . ' ' . $signatures->signature_month . ' ' . $signatures->signature_year;
        } else {
            $tanggal = $signatures->signature_month . ' ' . $signatures->signature_year;
        }
        $data['signature_date'] = $tanggal;

        $pdf = Pdf::loadView('pdf.reka-pagu-standar', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reka-pagu-standar-' . now()->format('Y-m-d-H-i-s') . '.pdf'
        );
    }

    public function exportCsv()
    {
        $data = $this->getData();
        $signatures = SignatureSetting::first() ?? SignatureSetting::create([]);
        $filename = 'reka-pagu-standar-' . now()->format('Y-m-d-H-i-s') . '.csv';

        // Format tanggal lengkap
        $tanggal = '';
        if ($signatures->signature_day > 0) {
            $tanggal = $signatures->signature_day . ' ' . $signatures->signature_month . ' ' . $signatures->signature_year;
        } else {
            $tanggal = $signatures->signature_month . ' ' . $signatures->signature_year;
        }

        $output = fopen('php://temp', 'r+');

        // Write BOM for UTF-8 encoding (ensures Excel recognizes UTF-8)
        fwrite($output, "\xEF\xBB\xBF");

        // Header section
        fputcsv($output, ['Rencana Kegiatan dan Anggaran Madrasah (RKAM)']);
        fputcsv($output, [$data['namaSekolah']]);
        fputcsv($output, ['Tahun ' . $data['tahun']]);
        fputcsv($output, []); // Empty row

        // Table header
        fputcsv($output, ['NO.', 'URAIAN ANGGARAN BELANJA', 'DIPA (Rp)', 'BOS (Rp)', 'KOMITE (Rp)', 'REALISASI (Rp)', 'PENGAJUAN BERJALAN (Rp)', 'PENGEMBALIAN (Rp)', 'SISA ANGGARAN (Rp)']);

        // Data rows
        foreach ($data['rows'] as $row) {
            fputcsv($output, [
                $row['no'],
                $row['nama_standar'],
                number_format($row['dipa'], 0, '.', '.'),
                number_format($row['bos'], 0, '.', '.'),
                number_format($row['komite'], 0, '.', '.'),
                number_format($row['realisasi'], 0, '.', '.'),
                number_format($row['pengajuan_berjalan'], 0, '.', '.'),
                number_format($row['pengembalian'], 0, '.', '.'),
                number_format($row['sisa'], 0, '.', '.'),
            ]);
        }

        // Total row
        fputcsv($output, [
            '',
            'JUMLAH',
            number_format($data['totals']['dipa'], 0, '.', '.'),
            number_format($data['totals']['bos'], 0, '.', '.'),
            number_format($data['totals']['komite'], 0, '.', '.'),
            number_format($data['totals']['realisasi'], 0, '.', '.'),
            number_format($data['totals']['pengajuan_berjalan'], 0, '.', '.'),
            number_format($data['totals']['pengembalian'], 0, '.', '.'),
            number_format($data['totals']['sisa'], 0, '.', '.'),
        ]);

        // Grand total
        fputcsv($output, []);
        fputcsv($output, ['', 'JUMLAH KESELURUHAN (DIPA/BOS dan KOMITE)']);
        fputcsv($output, ['', '', '', '', number_format($data['totals']['grand_total'], 0, '.', '.')]);

        // Signature section
        fputcsv($output, []);
        fputcsv($output, []);
        fputcsv($output, ['Menyetujui', '', '', '', 'Malang, ' . $tanggal]);
        fputcsv($output, ['Ketua Komite MTsN 1 Kota Malang', '', '', '', 'Kepala MTsN 1 Kota Malang']);
        fputcsv($output, []);
        fputcsv($output, []);
        fputcsv($output, []);
        fputcsv($output, [$signatures->ketua_komite_name, '', '', '', $signatures->kepala_sekolah_name]);

        // Rewind and get content
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'text/csv; charset=utf-8']
        );
    }
}
