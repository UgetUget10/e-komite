<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\Standar;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DocumentStatusWidget extends BaseWidget
{
    use ScopesToUserStandar;

    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Status Kelengkapan Dokumen Per Standar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Standar::query()
                    ->when(!$this->isPrivilegedUser() && !empty($this->allowedStandarIds()), fn ($q) => $q->whereIn('id', $this->allowedStandarIds()))
                    ->orderBy('nama_standar')
            )
            ->columns([
                TextColumn::make('nama_standar')
                    ->label('Standar')
                    ->weight('bold'),

                TextColumn::make('total_realisasi')
                    ->label('Total Realisasi (Rp)')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $total = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->sum('nominal') ?? 0;
                        return 'Rp ' . number_format($total, 0, ',', '.');
                    })
                    ->alignRight(),

                TextColumn::make('total_records')
                    ->label('Total Realisasi (Jumlah)')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        return DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->count();
                    })
                    ->alignCenter(),

                TextColumn::make('records_with_dokumen')
                    ->label('Ada Dokumen')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        return DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->where(function ($q) {
                                $q->whereNotNull('dokumen')
                                  ->orWhereNotNull('google_drive_url');
                            })
                            ->count();
                    })
                    ->alignCenter(),

                TextColumn::make('completion_percentage')
                    ->label('% Lengkap')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $total = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->count();

                        if ($total == 0) {
                            return '-';
                        }

                        $withDokumen = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->where(function ($q) {
                                $q->whereNotNull('dokumen')
                                  ->orWhereNotNull('google_drive_url');
                            })
                            ->count();

                        $percentage = round(($withDokumen / $total) * 100, 1);
                        return $percentage . '%';
                    })
                    ->alignCenter(),

                TextColumn::make('completion_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $total = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->count();

                        if ($total == 0) {
                            return 'Kosong';
                        }

                        $withDokumen = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->where(function ($q) {
                                $q->whereNotNull('dokumen')
                                  ->orWhereNotNull('google_drive_url');
                            })
                            ->count();

                        $percentage = ($withDokumen / $total) * 100;

                        if ($percentage == 100) {
                            return 'Lengkap';
                        } elseif ($percentage >= 80) {
                            return 'Hampir Lengkap';
                        } elseif ($percentage >= 50) {
                            return 'Setengah';
                        }
                        return 'Kurang';
                    })
                    ->color(function ($record) {
                        $total = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->count();

                        if ($total == 0) {
                            return 'gray';
                        }

                        $withDokumen = DB::table('komite.tb_realisasi_nominal')
                            ->where('fk_id_standar', $record->id)
                            ->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id)
                            ->whereNull('deleted_at')
                            ->where(function ($q) {
                                $q->whereNotNull('dokumen')
                                  ->orWhereNotNull('google_drive_url');
                            })
                            ->count();

                        $percentage = ($withDokumen / $total) * 100;

                        if ($percentage == 100) {
                            return 'success';
                        } elseif ($percentage >= 80) {
                            return 'warning';
                        } elseif ($percentage >= 50) {
                            return 'info';
                        }
                        return 'danger';
                    }),
            ])
            ->paginated(false);
    }
}
