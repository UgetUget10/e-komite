<?php

namespace App\Filament\Resources\RealisasiNominals\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RealisasiNominalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Program')
                    ->description('Pilih standar dan kode program kegiatan — data program akan terisi otomatis')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Select::make('fk_id_pagu_tahun_anggaran')
                            ->label('Tahun Anggaran (Aktif)')
                            ->prefixIcon('heroicon-o-calendar')
                            ->options(function () {
                                return \App\Models\PaguTahunAnggaran::where('status_aktif', 1)
                                    ->pluck('tahun', 'id');
                            })
                            ->default(function () {
                                return \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id');
                            })
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->reactive(),

                        Select::make('fk_id_standar')
                            ->label('Standar')
                            ->prefixIcon('heroicon-o-building-library')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('Super Admin')) {
                                    return \App\Models\Standar::pluck('nama_standar', 'id');
                                } else {
                                    return $user->standars()->pluck('nama_standar', 'id');
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('fk_id_program_kegiatan_kode', null);
                                $set('fk_id_program_kegiatan', null);
                            }),

                        Select::make('fk_id_program_kegiatan_kode')
                            ->label('Kode Program Kegiatan')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->options(function (callable $get) {
                                $standarId = $get('fk_id_standar');
                                $paguId = $get('fk_id_pagu_tahun_anggaran');

                                if (!$standarId || !$paguId) {
                                    return [];
                                }

                                return \App\Models\ProgramKegiatan::where('id_standar', $standarId)
                                    ->where('fk_id_pagu_tahun_anggaran', $paguId)
                                    ->get()
                                    ->mapWithKeys(function ($program) {
                                        return [$program->id => $program->kode_lengkap];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, $record) {
                                if ($record && $record->fk_id_program_kegiatan) {
                                    $set('fk_id_program_kegiatan_kode', $record->fk_id_program_kegiatan);

                                    $program = \App\Models\ProgramKegiatan::find($record->fk_id_program_kegiatan);
                                    $set('program_kegiatan_display', $program?->judul_program ?? '');

                                    // Get pagu komite
                                    $paguKomite = (float) ($program?->pagu_komite ?? 0);
                                    $set('pagu_komite_display', number_format((int)$paguKomite, 0, '.', '.'));

                                    // Get realisasi existing
                                    $realisasiExisting = \App\Models\RealisasiNominal::where('fk_id_program_kegiatan', $record->fk_id_program_kegiatan)
                                        ->whereNull('deleted_at')
                                        ->sum('nominal');
                                    $set('realisasi_existing_display', number_format((int)$realisasiExisting, 0, '.', '.'));

                                    // Calculate sisa anggaran
                                    $sisaAnggaran = $paguKomite - $realisasiExisting;
                                    $set('sisa_anggaran_display', number_format((int)$sisaAnggaran, 0, '.', '.'));
                                }
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('fk_id_program_kegiatan', $state);

                                if ($state) {
                                    $program = \App\Models\ProgramKegiatan::find($state);
                                    $set('program_kegiatan_display', $program?->judul_program ?? '');

                                    // Get pagu komite
                                    $paguKomite = (float) ($program?->pagu_komite ?? 0);
                                    $set('pagu_komite_display', number_format((int)$paguKomite, 0, '.', '.'));

                                    // Get realisasi existing
                                    $realisasiExisting = \App\Models\RealisasiNominal::where('fk_id_program_kegiatan', $state)
                                        ->whereNull('deleted_at')
                                        ->sum('nominal');
                                    $set('realisasi_existing_display', number_format((int)$realisasiExisting, 0, '.', '.'));

                                    // Calculate sisa anggaran
                                    $sisaAnggaran = $paguKomite - $realisasiExisting;
                                    $set('sisa_anggaran_display', number_format((int)$sisaAnggaran, 0, '.', '.'));
                                } else {
                                    $set('program_kegiatan_display', '');
                                    $set('pagu_komite_display', '');
                                    $set('realisasi_existing_display', '0');
                                    $set('sisa_anggaran_display', '');
                                }
                            }),

                        TextInput::make('program_kegiatan_display')
                            ->label('Program Kegiatan (Otomatis)')
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->readOnly()
                            ->dehydrated(false)
                            ->reactive(),

                        TextInput::make('pagu_komite_display')
                            ->label('Pagu Komite')
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->prefix('Rp ')
                            ->readOnly()
                            ->dehydrated(false)
                            ->reactive(),

                        TextInput::make('realisasi_existing_display')
                            ->label('Realisasi Sudah Ada')
                            ->prefixIcon('heroicon-o-chart-bar')
                            ->prefix('Rp ')
                            ->readOnly()
                            ->dehydrated(false)
                            ->reactive(),

                        TextInput::make('sisa_anggaran_display')
                            ->label('Sisa Anggaran')
                            ->prefixIcon('heroicon-o-banknotes')
                            ->prefix('Rp ')
                            ->readOnly()
                            ->dehydrated(false)
                            ->reactive()
                            ->helperText('Sisa anggaran = Pagu Komite - Realisasi Sudah Ada'),

                        Hidden::make('fk_id_program_kegiatan')
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Detail Realisasi')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        DatePicker::make('tanggal_realisasi')
                            ->label('Tanggal Realisasi')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->required(),

                        TextInput::make('nominal')
                            ->label('Nominal Realisasi')
                            ->prefix('Rp ')
                            ->required()
                            ->default(0)
                            ->extraAttributes([
                                'x-data' => "{
                                    formatNumber(e) {
                                        let val = e.target.value.replace(/\D/g, '');
                                        e.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
                                    }
                                }",
                                '@input' => 'formatNumber($event)',
                                'inputmode' => 'numeric',
                                'type' => 'text',
                            ])
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', (string)$state) : 0),

                        Select::make('status')
                            ->label('Status')
                            ->prefixIcon('heroicon-o-flag')
                            ->options(\App\Models\RealisasiNominal::STATUSES)
                            ->default(\App\Models\RealisasiNominal::STATUS_DIAJUKAN)
                            ->required()
                            ->helperText('Diajukan: menunggu persetujuan. Berjalan: sudah dicairkan, menunggu bukti. Selesai: bukti lengkap (realisasi final).'),

                        Textarea::make('keterangan')
                            ->label('Keterangan Realisasi')
                            ->placeholder('Tambahkan keterangan realisasi (opsional)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Dokumen Pendukung')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        FileUpload::make('dokumen')
                            ->label('Upload Dokumen (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->required(false)
                            ->disk('public')
                            ->directory('realisasi-nominal-dokumen')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->multiple(false)
                            ->helperText('Format: PDF | Ukuran maksimal: 10MB')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get): string {
                                $standarNama = \App\Models\Standar::whereKey($get('fk_id_standar'))
                                    ->value('nama_standar') ?? 'standar';

                                $programNama = \App\Models\ProgramKegiatan::whereKey($get('fk_id_program_kegiatan'))
                                    ->value('judul_program') ?? 'program-kegiatan';

                                $tanggal = $get('tanggal_realisasi');
                                $tanggalSlug = $tanggal
                                    ? \Illuminate\Support\Carbon::parse($tanggal)->format('d-m-Y')
                                    : now()->format('d-m-Y');

                                $baseName = collect([
                                    'Realisasi',
                                    Str::slug($standarNama),
                                    Str::slug($programNama),
                                    $tanggalSlug,
                                ])->implode('_');

                                $extension = $file->getClientOriginalExtension();
                                $filename = "{$baseName}.{$extension}";

                                $disk = Storage::disk('public');
                                $counter = 2;

                                while ($disk->exists('realisasi-nominal-dokumen/'.$filename)) {
                                    $filename = "{$baseName}-{$counter}.{$extension}";
                                    $counter++;
                                }

                                return $filename;
                            })
                            ->deleteUploadedFileUsing(function (string $file): void {
                                Storage::disk('public')->delete($file);
                            })
                            ->columnSpanFull(),

                        View::make('filament.upload-berkas.berkas-preview')
                            ->viewData(function (Get $get, ?Model $record) {
                                $path = $get('dokumen');
                                $hasExisting = $record && is_string($path) && filled($path);

                                return [
                                    'existingUrl' => $hasExisting ? Storage::disk('public')->url($path) : null,
                                    'existingIsPdf' => $hasExisting,
                                    'showFallbackLink' => true,
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
