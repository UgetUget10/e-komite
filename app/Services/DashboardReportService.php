<?php

namespace App\Services;

use App\Models\PaguTahunAnggaran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    public function buildPdfData(User $user): array
    {
        $isPrivileged = $user->hasRole(['Super Admin', 'Pelaksana Komite', 'Kepala Madrasah']);
        $allowedStandarIds = $isPrivileged ? null : $user->standars()->pluck('id')->toArray();

        $tahunAktif = PaguTahunAnggaran::where('status_aktif', 1)->first()
            ?? PaguTahunAnggaran::orderByDesc('tahun')->first();

        $generatedAt = now()->translatedFormat('d F Y H:i');

        if (!$tahunAktif) {
            return [
                'tahun' => 'N/A',
                'generatedAt' => $generatedAt,
                'userName' => $user->name,
                'paguOverview' => [],
                'alerts' => [],
                'monthlyTrend' => [],
                'byStandar' => [],
                'variance' => [],
                'topPrograms' => [],
                'recentActivity' => [],
                'yearOverYear' => [],
                'documentStatus' => [],
            ];
        }

        return [
            'tahun' => $tahunAktif->tahun,
            'generatedAt' => $generatedAt,
            'userName' => $user->name,
            'paguOverview' => $this->buildPaguOverview($tahunAktif, $isPrivileged, $allowedStandarIds),
            'alerts' => $this->buildAlerts($tahunAktif, $isPrivileged, $allowedStandarIds),
            'monthlyTrend' => $this->buildMonthlyTrend($tahunAktif, $isPrivileged, $allowedStandarIds),
            'byStandar' => $this->buildByStandar($tahunAktif, $isPrivileged, $allowedStandarIds),
            'variance' => $this->buildVariance($tahunAktif, $isPrivileged, $allowedStandarIds),
            'topPrograms' => $this->buildTopPrograms($tahunAktif, $isPrivileged, $allowedStandarIds),
            'recentActivity' => $this->buildRecentActivity($tahunAktif, $isPrivileged, $allowedStandarIds),
            'yearOverYear' => $this->buildYearOverYear(),
            'documentStatus' => $this->buildDocumentStatus($tahunAktif, $isPrivileged, $allowedStandarIds),
        ];
    }

    private function buildPaguOverview(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $baseQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id);

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $baseQuery->whereIn('id_standar', $allowedStandarIds);
        }

        $paguDiajukan = $baseQuery->sum(DB::raw('pagu_dipa + pagu_komite')) ?? 0;

        $realisasiQuery = DB::table('komite.tb_realisasi_nominal')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('deleted_at');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $realisasiQuery->whereIn('fk_id_standar', $allowedStandarIds);
        }

        $totalRealisasi = $realisasiQuery->sum('nominal') ?? 0;
        $executionRate = $paguDiajukan > 0 ? round(($totalRealisasi / $paguDiajukan) * 100, 1) : 0;
        $sisaAnggaran = max(0, $tahunAktif->nominal_dana - $paguDiajukan);

        return [
            'totalPagu' => $tahunAktif->nominal_dana,
            'totalKeperluanDana' => $paguDiajukan,
            'totalRealisasi' => $totalRealisasi,
            'executionRate' => $executionRate,
            'sisaAnggaran' => $sisaAnggaran,
            'isAktif' => $tahunAktif->status_aktif == 1,
        ];
    }

    private function buildAlerts(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $overBudgetQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereColumn('total_pengeluaran_perprogram', '>', 'pagu_komite');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $overBudgetQuery->whereIn('id_standar', $allowedStandarIds);
        }

        $overBudgetCount = $overBudgetQuery->count();

        $noDokumenQuery = DB::table('komite.tb_realisasi_nominal')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->where('nominal', '>', 0)
            ->whereNull('dokumen')
            ->whereNull('google_drive_url')
            ->whereNull('deleted_at');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $noDokumenQuery->whereIn('fk_id_standar', $allowedStandarIds);
        }

        $noDokumenCount = $noDokumenQuery->count();

        return [
            'overBudgetPrograms' => $overBudgetCount,
            'realisasiTanpaDokumen' => $noDokumenCount,
        ];
    }

    private function buildMonthlyTrend(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $realisasiPerBulan = DB::table('komite.tb_realisasi_nominal')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('deleted_at')
            ->whereYear('tanggal_realisasi', $tahunAktif->tahun)
            ->selectRaw('MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('fk_id_standar', $allowedStandarIds))
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $label = Carbon::create()->month($m)->translatedFormat('M');
            $data[] = [
                'bulan' => $label,
                'nominal' => (float) ($realisasiPerBulan[$m] ?? 0),
            ];
        }

        return $data;
    }

    private function buildByStandar(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $data = DB::table('komite.tb_realisasi_nominal as rn')
            ->join('komite.tb_standar as s', 'rn.fk_id_standar', '=', 's.id')
            ->where('rn.fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('rn.deleted_at')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('rn.fk_id_standar', $allowedStandarIds))
            ->selectRaw('s.nama_standar, SUM(rn.nominal) as total')
            ->groupBy('s.nama_standar')
            ->orderByDesc('total')
            ->get();

        return $data->map(fn ($item) => [
            'standar' => $item->nama_standar,
            'total' => (float) $item->total,
        ])->toArray();
    }

    private function buildVariance(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $data = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('id_standar', $allowedStandarIds))
            ->select('judul_program', 'pagu_komite', 'total_pengeluaran_perprogram')
            ->orderByDesc('pagu_komite')
            ->limit(15)
            ->get();

        return $data->map(fn ($item) => [
            'program' => substr($item->judul_program, 0, 40),
            'pagu' => (float) $item->pagu_komite,
            'realisasi' => (float) $item->total_pengeluaran_perprogram,
            'variance' => (float) ($item->total_pengeluaran_perprogram - $item->pagu_komite),
            'percentVariance' => $item->pagu_komite > 0 ? round((($item->total_pengeluaran_perprogram - $item->pagu_komite) / $item->pagu_komite) * 100, 1) : 0,
        ])->toArray();
    }

    private function buildTopPrograms(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $data = DB::table('komite.tb_program_kegiatan as pk')
            ->join('komite.tb_standar as s', 'pk.id_standar', '=', 's.id')
            ->where('pk.fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('pk.id_standar', $allowedStandarIds))
            ->select('pk.judul_program', 's.nama_standar', 'pk.pagu_komite', 'pk.total_pengeluaran_perprogram')
            ->orderByDesc('pk.total_pengeluaran_perprogram')
            ->limit(5)
            ->get();

        return $data->map(fn ($item) => [
            'program' => $item->judul_program,
            'standar' => $item->nama_standar,
            'pagu' => (float) $item->pagu_komite,
            'realisasi' => (float) $item->total_pengeluaran_perprogram,
            'executionRate' => $item->pagu_komite > 0 ? round(($item->total_pengeluaran_perprogram / $item->pagu_komite) * 100, 1) : 0,
        ])->toArray();
    }

    private function buildRecentActivity(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $data = DB::table('komite.tb_realisasi_nominal as rn')
            ->join('komite.tb_program_kegiatan as pk', 'rn.fk_id_program_kegiatan', '=', 'pk.id')
            ->join('komite.tb_standar as s', 'rn.fk_id_standar', '=', 's.id')
            ->where('rn.fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('rn.deleted_at')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('rn.fk_id_standar', $allowedStandarIds))
            ->select('rn.tanggal_realisasi', 'pk.judul_program', 's.nama_standar', 'rn.nominal', 'rn.dokumen', 'rn.google_drive_url', 'rn.updated_at')
            ->orderByDesc('rn.updated_at')
            ->limit(10)
            ->get();

        return $data->map(function ($item) {
            $hasDokumen = $item->dokumen || $item->google_drive_url;
            return [
                'tanggal' => Carbon::parse($item->tanggal_realisasi)->format('d M Y'),
                'program' => substr($item->judul_program, 0, 35),
                'standar' => $item->nama_standar,
                'nominal' => (float) $item->nominal,
                'hasDokumen' => $hasDokumen,
                'statusUpdate' => now()->diffInDays(Carbon::parse($item->updated_at)) <= 1 ? 'Baru' : 'Diubah',
            ];
        })->toArray();
    }

    private function buildYearOverYear(): array
    {
        $data = DB::table('komite.tb_pagu_tahun_anggaran')
            ->orderByDesc('tahun')
            ->get();

        return $data->map(function ($item) {
            $totalRealisasi = DB::table('komite.tb_realisasi_nominal')
                ->where('fk_id_pagu_tahun_anggaran', $item->id)
                ->whereNull('deleted_at')
                ->sum('nominal') ?? 0;

            $pagu = (float) $item->nominal_dana;
            $realisasi = (float) $totalRealisasi;
            $executionRate = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;

            return [
                'tahun' => $item->tahun,
                'pagu' => $pagu,
                'realisasi' => $realisasi,
                'executionRate' => $executionRate,
                'isAktif' => $item->status_aktif == 1,
            ];
        })->toArray();
    }

    private function buildDocumentStatus(PaguTahunAnggaran $tahunAktif, bool $isPrivileged, ?array $allowedStandarIds): array
    {
        $standars = DB::table('komite.tb_standar')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('id', $allowedStandarIds))
            ->orderBy('nama_standar')
            ->get();

        return $standars->map(function ($standar) use ($tahunAktif) {
            $total = DB::table('komite.tb_realisasi_nominal')
                ->where('fk_id_standar', $standar->id)
                ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
                ->whereNull('deleted_at')
                ->count();

            $withDokumen = DB::table('komite.tb_realisasi_nominal')
                ->where('fk_id_standar', $standar->id)
                ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNotNull('dokumen')
                      ->orWhereNotNull('google_drive_url');
                })
                ->count();

            $percentage = $total > 0 ? round(($withDokumen / $total) * 100, 1) : 0;

            return [
                'standar' => $standar->nama_standar,
                'totalRealisasi' => $total,
                'withDokumen' => $withDokumen,
                'completionPercentage' => $percentage,
            ];
        })->toArray();
    }
}
