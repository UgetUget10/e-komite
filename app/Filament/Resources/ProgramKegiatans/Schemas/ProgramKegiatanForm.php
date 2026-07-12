<?php

namespace App\Filament\Resources\ProgramKegiatans\Schemas;

use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use App\Models\PaguTahunAnggaran;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class ProgramKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Program')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('tahun_anggaran')
                            ->label('Tahun Anggaran Aktif')
                            ->prefixIcon('heroicon-o-calendar')
                            ->disabled()
                            ->dehydrated(false) // JANGAN simpan ke DB
                            ->formatStateUsing(function ($record) {
                                // Saat EDIT: ambil dari relasi
                                if ($record?->paguTahunAnggaran) {
                                    return $record->paguTahunAnggaran->tahun;
                                }

                                // Saat CREATE: ambil tahun aktif
                                return PaguTahunAnggaran::where('status_aktif', 1)->first()?->tahun ?? '-';
                            }),

                        Select::make('id_standar')
                            ->label('Standar')
                            ->prefixIcon('heroicon-o-building-library')
                            ->options(function () {
                                $user = auth()->user();

                                // Ambil standar sesuai role
                                if ($user->hasRole('Super Admin')) {
                                    $standars = \App\Models\Standar::all();
                                } else {
                                    $standars = $user->standars()->get();
                                }

                                return $standars->mapWithKeys(function ($standar) {
                                    $label = $standar->nama_standar;
                                    if ($standar->kode) {
                                        $label .= ' (' . $standar->kode . ')';
                                    }
                                    return [$standar->id => $label];
                                });
                            })
                            ->required(),

                        TextInput::make('kode')
                            ->label('Kode Program Kegiatan')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->placeholder('Misal: 01, 02, 03, dst')
                            ->required(),

                        TextInput::make('kode_lengkap')
                            ->label('Kode Lengkap')
                            ->prefixIcon('heroicon-o-qr-code')
                            ->readOnly()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                if ($record && $record->kode_lengkap) {
                                    return $record->kode_lengkap;
                                }
                                return '';
                            }),

                        TextInput::make('judul_program')
                            ->label('Judul Program')
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Sumber Dana')
                    ->icon('heroicon-o-banknotes')
                    ->description('Isi Pagu DIPA, BOS, dan Swadaya — Pagu Komite akan terhitung otomatis dari sisa Total Uraian')
                    ->schema([
                        TextInput::make('pagu_dipa')
                            ->label('Pagu DIPA')
                            ->prefix('Rp ')
                            ->required()
                            ->default(0)
                            ->live(debounce: 500)
                            // TAMPILKAN dengan format ribuan (500000 → Rp 500.000)
                            ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                            // SELALU KEMBALIKAN ANGKA MURNI TANPA TITIK saat disimpan
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))
                            ->extraInputAttributes([
                                'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                            ])
                            ->extraAttributes(['class' => 'text-right font-medium']),

                        TextInput::make('pagu_bos')
                            ->label('Pagu BOS')
                            ->prefix('Rp ')
                            ->required()
                            ->default(0)
                            ->live(debounce: 500)
                            ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))
                            ->extraInputAttributes([
                                'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                            ])
                            ->extraAttributes(['class' => 'text-right font-medium']),

                        TextInput::make('pagu_swadaya')
                            ->label('Pagu Swadaya')
                            ->prefix('Rp ')
                            ->required()
                            ->default(0)
                            ->live(debounce: 500)
                            ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))
                            ->extraInputAttributes([
                                'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                            ])
                            ->extraAttributes(['class' => 'text-right font-medium']),

                        // ========== PAGU KOMITE (OTOMATIS, LIVE) ==========
                        Hidden::make('pagu_komite')
                            ->label('Pagu Komite (Otomatis)')
                            ->dehydrated()
                            ->live()        // update saat ada perubahan
                            ->reactive()    // WAJIB — biar dengar perubahan repeater & pagu lain
                            ->formatStateUsing(fn ($get) => number_format(self::hitungPaguKomite($get), 0, ',', '.'))
                            ->dehydrateStateUsing(fn ($get) => self::hitungPaguKomite($get))
                            ->afterStateHydrated(fn ($component, $get) => $component->state(self::hitungPaguKomite($get)))
                            ->extraAttributes([
                                'class' => 'text-2xl font-bold text-primary-600 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-right border border-primary-300'
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Pengembalian Anggaran')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->description('Diisi jika ada sisa dana yang dikembalikan ke komite (biasanya di akhir tahun anggaran). Hanya bisa diisi oleh Super Admin, Pelaksana Komite, atau Kepala Madrasah.')
                    ->schema([
                        TextInput::make('pengembalian_sisa_anggaran')
                            ->label('Pengembalian Sisa Anggaran')
                            ->prefix('Rp ')
                            ->default(0)
                            ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))
                            ->extraInputAttributes([
                                'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                            ])
                            ->extraAttributes(['class' => 'text-right font-medium']),
                    ])
                    ->visible(fn () => auth()->user()?->hasRole(['Super Admin', 'Pelaksana Komite', 'Kepala Madrasah']) ?? false)
                    ->columnSpanFull(),

                Section::make('Uraian Kegiatan')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('uraianProgram')
                            ->hiddenLabel()
                            ->relationship('uraianProgram')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('nama_uraian')
                                        ->label('Nama Uraian'),

                                    TextInput::make('banyaknya')
                                        ->numeric()
                                        ->live(debounce: 1000)
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $satuan = (float) $get('satuan');
                                            $set('jumlah', $state * $satuan);
                                        }),

                                    TextInput::make('satuan')
                                        ->label('Harga Satuan')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('function($input) { return new Intl.NumberFormat("id-ID").format($input); }'))
                                        ->stripCharacters(['.', 'Rp', ','])
                                        ->live(debounce: 1000)
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $banyaknya = (float) $get('banyaknya');
                                            $set('jumlah', $banyaknya * $state);
                                        }),

                                    TextInput::make('jumlah')
                                        ->label('Jumlah Otomatis')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live() // WAJIB
                                        ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                                        ->readOnly(),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->minItems(0) // 👈 tidak bisa dihapus semua
                            ->createItemButtonLabel('Tambah Uraian Kegiatan')
                            ->columns(1)
                            ->columnSpanFull(),

                        Placeholder::make('total_semua')
                            ->label('Total Semua Uraian')
                            ->content(fn ($get) => 'Rp ' . number_format(
                                collect($get('uraianProgram') ?? [])->sum('jumlah') ?? 0,
                                0, ',', '.'
                            ))
                            ->extraAttributes([
                                'class' => 'text-2xl font-bold text-success-600 text-right p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'
                            ])
                            ->columnSpanFull()
                            ->reactive(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function hitungPaguKomite($get): int
    {
        // Total semua uraian
        $totalUraian = collect($get('uraianProgram') ?? [])->sum('jumlah') ?? 0;

        // Ambil pagu_dipa, pagu_bos, dan pagu_swadaya, PASTIKAN bersih dari titik & karakter lain
        $paguDipaString = $get('pagu_dipa') ?? '0';
        $paguDipa = (int) preg_replace('/\D/', '', $paguDipaString);

        $paguBosString = $get('pagu_bos') ?? '0';
        $paguBos = (int) preg_replace('/\D/', '', $paguBosString);

        $paguSwadayaString = $get('pagu_swadaya') ?? '0';
        $paguSwadaya = (int) preg_replace('/\D/', '', $paguSwadayaString);

        // Komite = Total Semua Uraian - DIPA - BOS - Swadaya
        $hasil = $totalUraian - $paguDipa - $paguBos - $paguSwadaya;
        return max($hasil, 0);
    }
}
