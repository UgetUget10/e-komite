<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\ProgramKegiatan;
use App\Models\SignatureSetting;
use Filament\Widgets\Widget;

class BudgetAlertWidget extends Widget
{
    use ScopesToUserStandar;

    protected string $view = 'filament.widgets.budget-alert';
    protected static ?int $sort = 3;

    public function getAlerts(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();
        $tahunAktif = $this->activeTahunAnggaran();

        if (!$tahunAktif) {
            return [];
        }

        // Get alert threshold from settings (default 80%)
        $settings = SignatureSetting::first();
        $threshold = $settings?->alert_threshold ?? 80;

        $alerts = [];

        // Get all programs with spending info
        $query = ProgramKegiatan::query()
            ->with('realisasi')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id);

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $query->whereIn('id_standar', $allowedStandarIds);
        }

        $programs = $query->get();

        foreach ($programs as $program) {
            $realisasi = $program->realisasi()->whereNull('deleted_at')->sum('nominal');
            $pagu = $program->pagu_komite;

            if ($pagu > 0) {
                $percentage = ($realisasi / $pagu) * 100;

                // Alert jika sudah melampaui pagu (100%+)
                if ($realisasi >= $pagu) {
                    $alerts[] = [
                        'type' => 'danger',
                        'title' => '⚠️ Budget Terlampaui',
                        'message' => "{$program->judul_program} - Realisasi Rp " . number_format($realisasi, 0, '.', '.') . " / Rp " . number_format($pagu, 0, '.', '.'),
                        'percentage' => 100,
                    ];
                }
                // Alert jika sudah mencapai threshold yang di-set
                elseif ($percentage >= $threshold) {
                    $alerts[] = [
                        'type' => 'warning',
                        'title' => "⚡ Budget Mendekati Batas ({$threshold}%)",
                        'message' => "{$program->judul_program} - " . round($percentage, 1) . "% terpakai",
                        'percentage' => $percentage,
                    ];
                }
            }
        }

        // Sort by percentage (highest first)
        usort($alerts, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        return array_slice($alerts, 0, 10); // Show top 10 alerts
    }
}
