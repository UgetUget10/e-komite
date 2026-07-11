<?php

namespace App\Filament\Widgets;

use App\Models\PaguTahunAnggaran;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class YearOverYearWidget extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Perbandingan Antar Tahun Anggaran';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaguTahunAnggaran::query()
                    ->orderByDesc('tahun')
            )
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('status_badge')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_aktif ? 'Aktif' : 'Tidak Aktif')
                    ->color(fn ($record) => $record->status_aktif ? 'success' : 'gray'),

                TextColumn::make('nominal_dana')
                    ->label('Total Pagu')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('total_realisasi')
                    ->label('Total Realisasi')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $total = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_pagu_tahun_anggaran', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('nominal') ?? 0;
                        return 'Rp ' . number_format($total, 0, ',', '.');
                    })
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('execution_rate_pct')
                    ->label('% Execution')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $pagu = (float) ($record->nominal_dana ?? 0);
                        $realisasi = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_pagu_tahun_anggaran', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('nominal') ?? 0;
                        $rate = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;
                        return $rate . '%';
                    })
                    ->alignCenter(),

                TextColumn::make('execution_status')
                    ->label('Status Eksekusi')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $pagu = (float) ($record->nominal_dana ?? 0);
                        $realisasi = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_pagu_tahun_anggaran', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('nominal') ?? 0;
                        $rate = $pagu > 0 ? ($realisasi / $pagu) * 100 : 0;

                        if ($rate >= 100) {
                            return 'Over';
                        } elseif ($rate >= 80) {
                            return 'Near';
                        } else {
                            return 'OK';
                        }
                    })
                    ->color(function ($record) {
                        $pagu = (float) ($record->nominal_dana ?? 0);
                        $realisasi = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_pagu_tahun_anggaran', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('nominal') ?? 0;
                        $rate = $pagu > 0 ? ($realisasi / $pagu) * 100 : 0;

                        if ($rate >= 100) {
                            return 'danger';
                        } elseif ($rate >= 80) {
                            return 'warning';
                        }
                        return 'success';
                    }),
            ])
            ->paginated(false)
            ->defaultSort('tahun', 'desc');
    }
}
