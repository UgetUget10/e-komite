<?php

namespace App\Filament\Resources\RealisasiNominals\Tables;

use App\Models\PaguTahunAnggaran;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RealisasiNominalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paguTahunAnggaran.tahun')
                    ->label('Tahun Anggaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('standar.nama_standar')
                    ->label('Standar')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('programKegiatan.kode_lengkap')
                    ->label('Kode Program')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('programKegiatan.judul_program')
                    ->label('Program Kegiatan')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'max-w-xs']),

                TextColumn::make('tanggal_realisasi')
                    ->label('Tanggal Realisasi')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        RealisasiNominal::STATUS_DIAJUKAN => 'gray',
                        RealisasiNominal::STATUS_BERJALAN => 'warning',
                        RealisasiNominal::STATUS_SELESAI => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => RealisasiNominal::STATUSES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->weight('semibold')
                    ->color('success')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int)$state, 0, '.', '.'))
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi')
                            ->money('IDR')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int)$state, 0, '.', '.'))
                            ->extraAttributes(['class' => 'font-bold text-primary-600'])
                    ),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('google_drive_url')
                    ->label('Google Drive')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'Lihat File' : 'Belum diupload')
                    ->color(fn ($state) => filled($state) ? 'success' : 'gray')
                    ->icon(fn ($state) => filled($state) ? 'heroicon-o-arrow-top-right-on-square' : null)
                    ->url(fn ($state) => filled($state) ? $state : null)
                    ->openUrlInNewTab()
                    ->toggleable(),

                TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter Tahun Anggaran
                SelectFilter::make('fk_id_pagu_tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->relationship('paguTahunAnggaran', 'tahun')
                    ->searchable()
                    ->preload(),

                // Filter Standar
                SelectFilter::make('fk_id_standar')
                    ->label('Standar')
                    ->relationship('standar', 'nama_standar')
                    ->searchable()
                    ->preload(),

                // Filter Status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(RealisasiNominal::STATUSES),

                // Filter Program Kegiatan
                SelectFilter::make('fk_id_program_kegiatan')
                    ->label('Program Kegiatan')
                    ->relationship('programKegiatan', 'judul_program')
                    ->searchable()
                    ->preload(),

                // Filter Tanggal Realisasi
                Filter::make('tanggal_realisasi')
                    ->schema([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal')->native(false),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_realisasi', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_realisasi', '<=', $date),
                            );
                    }),

                // Filter Soft Delete (sudah ada)
                TrashedFilter::make(),
            ], layout: FiltersLayout::Modal)
            ->headerActions([
                Action::make('ekspor_rekap_harian')
                    ->label('Rekap Harian')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $tanggal = Carbon::parse($data['tanggal']);

                        return static::streamRekapPdf(
                            livewire: $livewire,
                            tanggalDari: $tanggal->toDateString(),
                            tanggalSampai: $tanggal->toDateString(),
                            periodeLabel: 'Tanggal ' . $tanggal->translatedFormat('d F Y'),
                            fileNamePrefix: 'rkam-harian-' . $tanggal->format('Y-m-d'),
                        );
                    }),

                Action::make('ekspor_rekap_bulanan')
                    ->label('Rekap Bulanan')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->schema([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options(
                                collect(range(1, 12))->mapWithKeys(
                                    fn ($m) => [$m => Carbon::create()->month($m)->translatedFormat('F')]
                                )
                            )
                            ->default(now()->month)
                            ->required(),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(
                                collect(range(now()->year - 3, now()->year + 1))->mapWithKeys(fn ($y) => [$y => $y])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $awal = Carbon::create((int) $data['tahun'], (int) $data['bulan'], 1)->startOfMonth();
                        $akhir = $awal->copy()->endOfMonth();

                        return static::streamRekapPdf(
                            livewire: $livewire,
                            tanggalDari: $awal->toDateString(),
                            tanggalSampai: $akhir->toDateString(),
                            periodeLabel: 'Bulan ' . $awal->translatedFormat('F Y'),
                            fileNamePrefix: 'rkam-bulanan-' . $awal->format('Y-m'),
                        );
                    }),

                Action::make('ekspor_rekap_tahunan')
                    ->label('Rekap Tahunan')
                    ->icon('heroicon-o-calendar-date-range')
                    ->color('success')
                    ->schema([
                        Select::make('fk_id_pagu_tahun_anggaran')
                            ->label('Tahun Anggaran')
                            ->options(fn () => PaguTahunAnggaran::pluck('tahun', 'id'))
                            ->default(fn () => PaguTahunAnggaran::where('status_aktif', 1)->value('id'))
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $tahun = PaguTahunAnggaran::find($data['fk_id_pagu_tahun_anggaran']);

                        return static::streamRekapPdf(
                            livewire: $livewire,
                            tanggalDari: null,
                            tanggalSampai: null,
                            periodeLabel: 'Tahun Anggaran ' . ($tahun->tahun ?? '-'),
                            fileNamePrefix: 'rkam-tahunan-' . ($tahun->tahun ?? 'unknown'),
                            tahunId: $tahun?->id,
                        );
                    }),

                Action::make('ekspor_rekap_detail_bulanan')
                    ->label('Rekap Detail Bulanan')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->schema([
                        Select::make('fk_id_pagu_tahun_anggaran')
                            ->label('Tahun Anggaran')
                            ->options(fn () => PaguTahunAnggaran::pluck('tahun', 'id'))
                            ->default(fn () => PaguTahunAnggaran::where('status_aktif', 1)->value('id'))
                            ->required(),

                        Select::make('tahun_kalender')
                            ->label('Tahun Kalender (untuk data bulanan)')
                            ->options(
                                collect(range(now()->year - 3, now()->year + 1))->mapWithKeys(fn ($y) => [$y => $y])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $tahun = PaguTahunAnggaran::find($data['fk_id_pagu_tahun_anggaran']);
                        $tahunKalender = (int) $data['tahun_kalender'];

                        return static::streamRekapDetailPdf(
                            livewire: $livewire,
                            tahunId: $tahun?->id,
                            tahunKalender: $tahunKalender,
                            periodeLabel: 'Tahun Anggaran ' . ($tahun->tahun ?? '-') . ' - Detail Bulanan ' . $tahunKalender,
                            fileNamePrefix: 'rkam-detail-bulanan-' . $tahunKalender,
                        );
                    }),
            ])
            ->recordActions([
                Action::make('setujui_cairkan')
                    ->label('Setujui & Cairkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn ($record) => static::isPrivilegedUser() && $record->status === RealisasiNominal::STATUS_DIAJUKAN)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui & Cairkan Dana')
                    ->modalDescription('Status akan berubah menjadi "Berjalan" — dana dianggap sudah dicairkan, menunggu bukti/dokumen realisasi.')
                    ->action(fn ($record) => $record->update(['status' => RealisasiNominal::STATUS_BERJALAN])),

                Action::make('tandai_selesai')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn ($record) => static::isPrivilegedUser() && $record->status === RealisasiNominal::STATUS_BERJALAN)
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Realisasi Selesai')
                    ->modalDescription('Status akan berubah menjadi "Selesai" — realisasi dianggap final dan bukti/dokumen sudah lengkap.')
                    ->action(fn ($record) => $record->update(['status' => RealisasiNominal::STATUS_SELESAI])),

                Action::make('isi_pengembalian')
                    ->label('Isi Pengembalian')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('info')
                    ->visible(fn () => static::isPrivilegedUser())
                    ->schema([
                        TextInput::make('pengembalian_sisa_anggaran')
                            ->label('Pengembalian Sisa Anggaran')
                            ->prefix('Rp ')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(fn ($record) => (int) $record->programKegiatan?->pengembalian_sisa_anggaran)
                            ->helperText('Nominal dana yang dikembalikan ke komite untuk program kegiatan ini.'),
                    ])
                    ->modalHeading(fn ($record) => 'Isi Pengembalian — ' . ($record->programKegiatan?->judul_program ?? '-'))
                    ->action(function ($record, array $data) {
                        $record->programKegiatan?->update(['pengembalian_sisa_anggaran' => $data['pengembalian_sisa_anggaran']]);
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Pengembalian sisa anggaran berhasil disimpan')
                    ),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('setujui_cairkan_massal')
                        ->label('Setujui & Cairkan (Terpilih)')
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->visible(fn () => static::isPrivilegedUser())
                        ->requiresConfirmation()
                        ->modalHeading('Setujui & Cairkan Dana Terpilih')
                        ->modalDescription('Semua record berstatus "Diajukan" di antara yang dipilih akan diubah menjadi "Berjalan". Record dengan status lain akan dilewati.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $updated = $records
                                ->where('status', RealisasiNominal::STATUS_DIAJUKAN)
                                ->each(fn ($record) => $record->update(['status' => RealisasiNominal::STATUS_BERJALAN]))
                                ->count();

                            \Filament\Notifications\Notification::make()
                                ->title($updated . ' realisasi disetujui & dicairkan')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('tandai_selesai_massal')
                        ->label('Tandai Selesai (Terpilih)')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->visible(fn () => static::isPrivilegedUser())
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Selesai Realisasi Terpilih')
                        ->modalDescription('Semua record berstatus "Berjalan" di antara yang dipilih akan diubah menjadi "Selesai". Record dengan status lain akan dilewati.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $updated = $records
                                ->where('status', RealisasiNominal::STATUS_BERJALAN)
                                ->each(fn ($record) => $record->update(['status' => RealisasiNominal::STATUS_SELESAI]))
                                ->count();

                            \Filament\Notifications\Notification::make()
                                ->title($updated . ' realisasi ditandai selesai')
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
            ->emptyStateHeading('Belum ada realisasi nominal')
            ->striped()
            ->defaultSort('created_at', 'desc');
    }

    protected static function isPrivilegedUser(): bool
    {
        return auth()->user()?->hasRole(['Super Admin', 'Pelaksana Komite', 'Kepala Madrasah']) ?? false;
    }

    protected static function streamRekapPdf(
        $livewire,
        ?string $tanggalDari,
        ?string $tanggalSampai,
        string $periodeLabel,
        string $fileNamePrefix,
        ?string $tahunId = null,
    ) {
        $user = auth()->user();

        $standarFilter = $livewire->getTableFilterState('fk_id_standar')['value'] ?? null;
        $programFilter = $livewire->getTableFilterState('fk_id_program_kegiatan')['value'] ?? null;
        $tahunFilter = $livewire->getTableFilterState('fk_id_pagu_tahun_anggaran')['value'] ?? null;

        $tahunId = $tahunId ?: ($tahunFilter ?: PaguTahunAnggaran::where('status_aktif', 1)->value('id'));
        $tahun = PaguTahunAnggaran::find($tahunId);

        $programQuery = ProgramKegiatan::query()
            ->with('standar')
            ->when($tahunId, fn ($q) => $q->where('fk_id_pagu_tahun_anggaran', $tahunId))
            ->when($standarFilter, fn ($q) => $q->where('id_standar', $standarFilter))
            ->when($programFilter, fn ($q) => $q->where('id', $programFilter));

        if (!$user->hasRole('Super Admin')) {
            $standarIds = $user->standars()->pluck('id');
            $programQuery->whereIn('id_standar', $standarIds);
        }

        $programs = $programQuery->get();

        $groups = $programs
            ->groupBy('id_standar')
            ->map(function ($programsInStandar) use ($tanggalDari, $tanggalSampai) {
                $standar = $programsInStandar->first()->standar;

                $rows = $programsInStandar
                    ->sortBy(fn ($p) => $p->kode)
                    ->values()
                    ->map(function ($program) use ($tanggalDari, $tanggalSampai) {
                        $realisasi = RealisasiNominal::where('fk_id_program_kegiatan', $program->id)
                            ->where('status', RealisasiNominal::STATUS_SELESAI)
                            ->when($tanggalDari, fn ($q, $date) => $q->whereDate('tanggal_realisasi', '>=', $date))
                            ->when($tanggalSampai, fn ($q, $date) => $q->whereDate('tanggal_realisasi', '<=', $date))
                            ->sum('nominal');

                        $komite = (float) ($program->pagu_komite ?? 0);

                        return [
                            'judul_program' => $program->judul_program,
                            'komite' => $komite,
                            'realisasi' => (float) $realisasi,
                            'sisa' => $komite - (float) $realisasi,
                        ];
                    });

                return [
                    'standar' => $standar,
                    'rows' => $rows,
                    'subtotal_komite' => $rows->sum('komite'),
                    'subtotal_realisasi' => $rows->sum('realisasi'),
                    'subtotal_sisa' => $rows->sum('sisa'),
                ];
            })
            ->sortBy(fn ($group) => $group['standar']?->kode)
            ->values();

        $grandTotalKomite = $groups->sum('subtotal_komite');
        $grandTotalRealisasi = $groups->sum('subtotal_realisasi');
        $grandTotalSisa = $groups->sum('subtotal_sisa');

        $pdf = Pdf::loadView('pdf.rekap-realisasi', [
            'groups' => $groups,
            'tahun' => $tahun,
            'periodeLabel' => $periodeLabel,
            'grandTotalKomite' => $grandTotalKomite,
            'grandTotalRealisasi' => $grandTotalRealisasi,
            'grandTotalSisa' => $grandTotalSisa,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileNamePrefix . '.pdf',
        );
    }

    protected static function streamRekapDetailPdf(
        $livewire,
        ?string $tahunId,
        int $tahunKalender,
        string $periodeLabel,
        string $fileNamePrefix,
    ) {
        $user = auth()->user();

        $standarFilter = $livewire->getTableFilterState('fk_id_standar')['value'] ?? null;
        $programFilter = $livewire->getTableFilterState('fk_id_program_kegiatan')['value'] ?? null;

        $tahunId = $tahunId ?: PaguTahunAnggaran::where('status_aktif', 1)->value('id');
        $tahun = PaguTahunAnggaran::find($tahunId);

        $bulanLabels = collect(range(1, 12))->map(
            fn ($m) => Carbon::create()->month($m)->translatedFormat('M')
        )->all();

        $programQuery = ProgramKegiatan::query()
            ->with('standar')
            ->when($tahunId, fn ($q) => $q->where('fk_id_pagu_tahun_anggaran', $tahunId))
            ->when($standarFilter, fn ($q) => $q->where('id_standar', $standarFilter))
            ->when($programFilter, fn ($q) => $q->where('id', $programFilter));

        if (!$user->hasRole('Super Admin')) {
            $standarIds = $user->standars()->pluck('id');
            $programQuery->whereIn('id_standar', $standarIds);
        }

        $programs = $programQuery->get();

        $groups = $programs
            ->groupBy('id_standar')
            ->map(function ($programsInStandar) use ($tahunKalender) {
                $standar = $programsInStandar->first()->standar;

                $rows = $programsInStandar
                    ->sortBy(fn ($p) => $p->kode)
                    ->values()
                    ->map(function ($program) use ($tahunKalender) {
                        $realisasiPerBulan = RealisasiNominal::where('fk_id_program_kegiatan', $program->id)
                            ->where('status', RealisasiNominal::STATUS_SELESAI)
                            ->whereYear('tanggal_realisasi', $tahunKalender)
                            ->selectRaw('MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total')
                            ->groupBy('bulan')
                            ->pluck('total', 'bulan');

                        $bulan = collect(range(1, 12))
                            ->map(fn ($m) => (float) ($realisasiPerBulan[$m] ?? 0))
                            ->all();

                        $komite = (float) ($program->pagu_komite ?? 0);
                        $totalRealisasiTahun = array_sum($bulan);

                        return [
                            'judul_program' => $program->judul_program,
                            'komite' => $komite,
                            'bulan' => $bulan,
                            'sisa' => $komite - $totalRealisasiTahun,
                        ];
                    });

                $subtotalBulan = collect(range(0, 11))
                    ->map(fn ($idx) => $rows->sum(fn ($row) => $row['bulan'][$idx]))
                    ->all();

                return [
                    'standar' => $standar,
                    'rows' => $rows,
                    'subtotal_komite' => $rows->sum('komite'),
                    'subtotal_bulan' => $subtotalBulan,
                    'subtotal_sisa' => $rows->sum('sisa'),
                ];
            })
            ->sortBy(fn ($group) => $group['standar']?->kode)
            ->values();

        $grandTotalKomite = $groups->sum('subtotal_komite');
        $grandTotalBulan = collect(range(0, 11))
            ->map(fn ($idx) => $groups->sum(fn ($group) => $group['subtotal_bulan'][$idx]))
            ->all();
        $grandTotalSisa = $groups->sum('subtotal_sisa');

        $pdf = Pdf::loadView('pdf.rekap-realisasi-detail', [
            'groups' => $groups,
            'tahun' => $tahun,
            'periodeLabel' => $periodeLabel,
            'bulanLabels' => $bulanLabels,
            'grandTotalKomite' => $grandTotalKomite,
            'grandTotalBulan' => $grandTotalBulan,
            'grandTotalSisa' => $grandTotalSisa,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileNamePrefix . '.pdf',
        );
    }
}
