<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\ProgramKegiatan;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProgramsWidget extends BaseWidget
{
    use ScopesToUserStandar;

    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top 5 Program Berdasarkan Pengeluaran';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProgramKegiatan::query()
                    ->when($this->activeTahunAnggaran(), fn ($q) => $q->where('fk_id_pagu_tahun_anggaran', $this->activeTahunAnggaran()?->id))
                    ->when(!$this->isPrivilegedUser() && !empty($this->allowedStandarIds()), fn ($q) => $q->whereIn('id_standar', $this->allowedStandarIds()))
                    ->with('standar')
                    ->orderByDesc('total_pengeluaran_perprogram')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('judul_program')
                    ->label('Program Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('standar.nama_standar')
                    ->label('Standar')
                    ->toggleable(),

                TextColumn::make('pagu_komite')
                    ->label('Pagu Komite')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight(),

                TextColumn::make('total_pengeluaran_perprogram')
                    ->label('Realisasi')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('execution_rate')
                    ->label('% Execution')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $pagu = (float) ($record->pagu_komite ?? 0);
                        $realisasi = (float) ($record->total_pengeluaran_perprogram ?? 0);
                        $rate = $pagu > 0 ? ($realisasi / $pagu) * 100 : 0;
                        return round($rate, 1) . '%';
                    })
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $pagu = (float) ($record->pagu_komite ?? 0);
                        $realisasi = (float) ($record->total_pengeluaran_perprogram ?? 0);
                        if ($realisasi > $pagu) {
                            return 'Over Budget';
                        } elseif ($realisasi >= $pagu * 0.8) {
                            return 'Near Limit';
                        }
                        return 'On Track';
                    })
                    ->color(function ($record) {
                        $pagu = (float) ($record->pagu_komite ?? 0);
                        $realisasi = (float) ($record->total_pengeluaran_perprogram ?? 0);
                        if ($realisasi > $pagu) {
                            return 'danger';
                        } elseif ($realisasi >= $pagu * 0.8) {
                            return 'warning';
                        }
                        return 'success';
                    }),
            ])
            ->paginated(false)
            ->defaultSort('total_pengeluaran_perprogram', 'desc');
    }
}
