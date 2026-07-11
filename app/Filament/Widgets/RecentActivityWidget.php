<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\RealisasiNominal;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    use ScopesToUserStandar;

    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Aktivitas Realisasi Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RealisasiNominal::query()
                    ->when($this->activeTahunAnggaran(), fn ($q) => $q->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id))
                    ->when(!$this->isPrivilegedUser() && !empty($this->allowedStandarIds()), fn ($q) => $q->whereIn('fk_id_standar', $this->allowedStandarIds()))
                    ->with(['programKegiatan', 'standar'])
                    ->orderByDesc('updated_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('tanggal_realisasi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->size('sm'),

                TextColumn::make('programKegiatan.judul_program')
                    ->label('Program')
                    ->limit(30)
                    ->searchable()
                    ->size('sm'),

                TextColumn::make('standar.nama_standar')
                    ->label('Standar')
                    ->limit(20)
                    ->toggleable()
                    ->size('sm'),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight()
                    ->weight('medium')
                    ->size('sm'),

                TextColumn::make('activity_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $diff = now()->diffInDays($record->updated_at);
                        return $diff <= 1 ? 'Baru' : ($diff <= 7 ? 'Ubah' : '');
                    })
                    ->color(function ($record) {
                        $diff = now()->diffInDays($record->updated_at);
                        return $diff <= 1 ? 'success' : ($diff <= 7 ? 'warning' : 'gray');
                    })
                    ->size('sm'),

                TextColumn::make('dokumen_status')
                    ->label('Dok')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->dokumen || $record->google_drive_url ? '✓' : '✗')
                    ->color(fn ($record) => $record->dokumen || $record->google_drive_url ? 'success' : 'danger')
                    ->size('sm'),
            ])
            ->paginated(false)
            ->defaultSort('updated_at', 'desc')
            ->striped();
    }
}
