<?php

namespace App\Filament\Widgets;

use App\Models\RealisasiNominal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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

        $isSuperAdmin = $user->hasRole('Super Admin');
        $allowedStandarIds = $isSuperAdmin ? null : $user->standars()->pluck('standar_id')->toArray();

        $stats = DB::table('komite.tb_standar as s')
            ->leftJoin(
                'komite.tb_program_kegiatan as pk',
                fn($j) => $j
                    ->on('pk.id_standar', '=', 's.id')
                    ->where('pk.fk_id_pagu_tahun_anggaran', $tahunAktifId)
            )
            ->select(
                's.id',
                's.nama_standar',
                DB::raw('COUNT(pk.id) as jumlah_program'),
                DB::raw('COALESCE(SUM(pk.pagu_komite), 0) as total_pagu'),
                DB::raw('COALESCE(SUM(pk.pengembalian_sisa_anggaran), 0) as total_pengembalian')
            )
            ->when(!is_null($allowedStandarIds), fn($q) => $q->whereIn('s.id', $allowedStandarIds))
            ->groupBy('s.id', 's.nama_standar')
            ->orderBy('s.nama_standar')
            ->get();

        // Query realisasi final per standar (terpisah untuk menghindari fan-out akibat join multiple tables)
        $realisasiPerStandar = DB::table('komite.tb_realisasi_nominal as rn')
            ->where('rn.fk_id_pagu_tahun_anggaran', $tahunAktifId)
            ->where('rn.status', RealisasiNominal::STATUS_SELESAI)
            ->whereNull('rn.deleted_at') // Exclude soft-deleted realisasi
            ->select('rn.fk_id_standar', DB::raw('COALESCE(SUM(rn.nominal), 0) as total_realisasi'))
            ->when(!is_null($allowedStandarIds), fn($q) => $q->whereIn('rn.fk_id_standar', $allowedStandarIds))
            ->groupBy('rn.fk_id_standar')
            ->pluck('total_realisasi', 'fk_id_standar');

        // Query pengajuan berjalan (status diajukan/berjalan) per standar
        $pengajuanBerjalanPerStandar = DB::table('komite.tb_realisasi_nominal as rn')
            ->where('rn.fk_id_pagu_tahun_anggaran', $tahunAktifId)
            ->whereIn('rn.status', RealisasiNominal::STATUSES_BERJALAN)
            ->whereNull('rn.deleted_at')
            ->select('rn.fk_id_standar', DB::raw('COALESCE(SUM(rn.nominal), 0) as total'))
            ->when(!is_null($allowedStandarIds), fn($q) => $q->whereIn('rn.fk_id_standar', $allowedStandarIds))
            ->groupBy('rn.fk_id_standar')
            ->pluck('total', 'fk_id_standar');

        $cards = [];

        foreach ($stats as $item) {
            $totalRealisasi = (float) ($realisasiPerStandar[$item->id] ?? 0);
            $totalPengajuanBerjalan = (float) ($pengajuanBerjalanPerStandar[$item->id] ?? 0);
            $totalPengembalian = (float) $item->total_pengembalian;
            $sisaStandar = $item->total_pagu - $totalRealisasi - $totalPengajuanBerjalan + $totalPengembalian;

            $icon = $this->getIconByStandar($item->nama_standar);
            $detailUrl = \App\Filament\Pages\DetailKeperluanDana::getUrl(['standarId' => $item->id]);
            $color = $this->getColorByStandar($item->nama_standar);
            $accentHex = $this->getAccentHex($color);

            $persenRealisasi = $item->total_pagu > 0
                ? min(100, round(($totalRealisasi / $item->total_pagu) * 100))
                : 0;

            $progressBar = "<div class=\"stat-progress-track\">"
                . "<div class=\"stat-progress-fill\" style=\"width: {$persenRealisasi}%; background: {$accentHex};\"></div>"
                . "</div>"
                . "<div class=\"stat-progress-label\">{$persenRealisasi}% terealisasi</div>";

            $descText = new HtmlString(
                $progressBar .
                "Realisasi: Rp " . number_format($totalRealisasi, 0, ',', '.') .
                ($totalPengajuanBerjalan > 0 ? "<br>Pengajuan Berjalan: Rp " . number_format($totalPengajuanBerjalan, 0, ',', '.') : '') .
                "<br>Sisa: Rp " . number_format($sisaStandar, 0, ',', '.') .
                "<br>{$item->jumlah_program} program {$icon}" .
                "<br><a href=\"{$detailUrl}\" class=\"inline-flex items-center gap-1 mt-1 text-xs font-semibold text-primary-600 hover:underline\">Lihat Detail &rarr;</a>"
            );

            $cards[] = Stat::make("Keperluan Dana " . $item->nama_standar, 'Rp. ' . number_format($item->total_pagu, 0, ',', '.'))
                ->description($descText)
                ->color($color)
                ->extraAttributes(['style' => "--stat-accent: {$this->getAccentHex($color)};"]);
        }

        return $cards;
    }

    private function getTahunAktifId(): ?string
    {
        // Prioritaskan tahun yang dipilih user via switcher (session), lalu
        // fallback ke tahun anggaran yang berstatus aktif.
        // id adalah UUID (string), jangan di-cast ke int — UUID v7 yang dibuat
        // berdekatan waktu bisa punya digit awal sama sehingga (int) cast
        // membuat tahun anggaran berbeda dianggap sama.
        $selectedTahun = session('selected_tahun_anggaran');

        if ($selectedTahun) {
            $id = DB::table('komite.tb_pagu_tahun_anggaran')->where('tahun', $selectedTahun)->value('id');
            if ($id) {
                return $id;
            }

            session()->forget('selected_tahun_anggaran');
        }

        return DB::table('komite.tb_pagu_tahun_anggaran')->where('status_aktif', 1)->value('id');
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

    private function getAccentHex(string $color): string
    {
        return match ($color) {
            'success' => '#10b981',
            'info' => '#3b82f6',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'primary' => '#f59e0b',
            'rose' => '#f43f5e',
            'emerald' => '#10b981',
            'amber' => '#f59e0b',
            default => '#9ca3af',
        };
    }

    private function getIconByStandar(string $nama): string
    {
        return match (true) {
            str_contains($nama, 'Proses')       => '⚙️',
            str_contains($nama, 'Isi')          => '📖',
            str_contains($nama, 'Penilaian')    => '✓',
            str_contains($nama, 'Pengelolaan')  => '🏢',
            str_contains($nama, 'PTT')          => '👥',
            str_contains($nama, 'Sarpras')      => '📚',
            str_contains($nama, 'Kelulusan')    => '🎓',
            str_contains($nama, 'Pembiayaan')   => '💰',
            default                             => '📄',
        };
    }
}
