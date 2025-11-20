<?php

namespace App\Filament\Resources\ProgramKegiatans\Schemas;

use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use App\Models\PaguTahunAnggaran;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class ProgramKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('tahun_anggaran')
                    ->label('Tahun Anggaran Aktif')
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
                    ->options(function () {
                        $user = auth()->user();
                        return $user->standars()->pluck('nama_standar', 'id');
                    })
                    ->required(),
                TextInput::make('judul_program')
                    ->required(),

                // TextInput::make('pagu_dipa')
                //     ->label('Pagu DIPA')
                //     // ->required()
                //     ->default(0)
                //     ->prefix('Rp ')
                //     ->integer()
                //     ->minValue(0)
                //     ->live()
                //     ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                //     ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', $state) : 0)
                //     ->extraInputAttributes(['class' => 'text-right font-medium']),

                // ========== PAGU DIPA (Aman di Create & Edit) ==========
                // TextInput::make('pagu_dipa')
                //     ->label('Pagu DIPA')
                //     ->required()
                //     ->default(0)
                //     ->prefix('Rp ')
                //     ->integer()
                //     ->minValue(0)
                //     ->live(debounce: 500)
                //     // Format tampilan: selalu tampilkan ribuan, bahkan saat edit
                //     ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                //     // Saat simpan: bersihkan titik dan Rp jadi integer murni
                //     ->dehydrateStateUsing(fn ($state): int => $state ? (int) preg_replace('/\D/', '', $state) : 0)
                //     ->extraInputAttributes(['class' => 'text-right font-medium']),

                TextInput::make('pagu_dipa')
                    ->label('Pagu DIPA')
                    ->prefix('Rp ')
                    ->required()
                    ->default(0)
                    ->live(debounce: 500)

                    // HAPUS SEMUA numeric(), integer(), mask(), dll — biarkan polos!

                    // TAMPILKAN dengan format ribuan (500000 → Rp 500.000)
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')

                    // YANG PALING PENTING: SELALU KEMBALIKAN ANGKA MURNI TANPA TITIK!
                    ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))

                    // Biar user bisa ketik ribuan otomatis (opsional tapi bagus)
                    ->extraInputAttributes([
                        'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                    ])
                    ->extraAttributes(['class' => 'text-right font-medium']),

                // ========== PAGU KOMITE (OTOMATIS BERKURANG LIVE 100%) ==========
                Hidden::make('pagu_komite')
                    ->label('Pagu Komite (Otomatis)')
                    // ->prefix('Rp ')
                    // ->readOnly()
                    ->dehydrated()
                    ->live()        // update saat ada perubahan
                    ->reactive()    // WAJIB — biar dengar perubahan repeater & pagu_dipa
                    ->formatStateUsing(fn ($get) => number_format(self::hitungPaguKomite($get), 0, ',', '.'))
                    ->dehydrateStateUsing(fn ($get) => self::hitungPaguKomite($get))
                    ->afterStateHydrated(fn ($component, $get) => $component->state(self::hitungPaguKomite($get)))
                    ->extraAttributes([
                        'class' => 'text-2xl font-bold text-primary-600 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-right border border-primary-300'
                    ])
                    ->columnSpanFull(),

                // TextInput::make('pagu_dipa')
                //     ->label('Pagu DIPA')
                //     ->numeric()
                //     ->required()
                //     ->live() // WAJIB!
                //     ->prefix('Rp ')
                //     ->mask(RawJs::make('function($input) { return new Intl.NumberFormat("id-ID").format($input); }'))
                //     ->default(0),

                // TextInput::make('pagu_komite')
                //     ->label('Pagu Komite (Otomatis)')
                //     ->readOnly()
                //     ->dehydrated(false) // Tidak disimpan ke DB (opsional)
                //     ->prefix('Rp ')
                //     ->live() // Update saat ada perubahan
                //     ->reactive() // Deteksi perubahan dari field lain
                //     ->formatStateUsing(function ($get) {
                //         // Ambil total dari semua uraian
                //         $totalUraian = collect($get('uraianProgram') ?? [])->sum('jumlah') ?? 0;

                //         // Ambil pagu_dipa (jika kosong = 0)
                //         $paguDipa = (float) ($get('pagu_dipa') ?? 0);

                //         // Hitung pagu_komite
                //         $paguKomite = $totalUraian - $paguDipa;

                //         // Pastikan tidak negatif
                //         return number_format(max($paguKomite, 0), 0, ',', '.');
                //     })
                //     ->extraAttributes([
                //         'class' => 'text-xl font-bold text-primary-600 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-right'
                //     ])
                //     ->columnSpanFull(),



                // 👉 Repeater untuk uraian program
                Repeater::make('uraianProgram')
                    ->label('Uraian Kegiatan')
                    ->relationship('uraianProgram')
                    ->schema([
                        Grid::make(4)->schema([
                            // TextInput::make('nama_uraian')
                            //     ->label('Nama Uraian')
                            //     ->required(),

                            // TextInput::make('banyaknya')
                            //     ->numeric()
                            //     ->reactive()
                            //     ->live(debounce: 2000) // Wait 2000ms before re-rendering the schema.
                            //     ->required()
                            //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            //         $satuan = (float) $get('satuan');
                            //         $set('jumlah', $state * $satuan);
                            //     }),

                            // TextInput::make('satuan')
                            //     ->label('Harga Satuan')
                            //     ->prefix('Rp')
                            //     ->numeric()
                            //     ->reactive()
                            //     ->live(debounce: 2000) // Wait 500ms before re-rendering the schema.
                            //     ->required()
                            //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            //         $banyaknya = (float) $get('banyaknya');
                            //         $set('jumlah', $state * $banyaknya);
                            //     }),

                            // TextInput::make('jumlah')
                            //     ->label('Jumlah Otomatis')
                            //     ->numeric()
                            //     ->prefix('Rp')
                            //     ->readOnly(), // biar user nggak ubah manual


                            TextInput::make('nama_uraian')
                                ->label('Nama Uraian'),
                                // ->required(),

                            TextInput::make('banyaknya')
                                ->numeric()
                                // ->reactive()
                                ->live(debounce: 1000)
                                // ->required()
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
                                // ->reactive()
                                ->live(debounce: 1000)
                                // ->required()
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

                        // ... repeater di sini
                        // TOTAL (SETELAH REPEATER)


                    ])
                    ->defaultItems(1)
                    ->minItems(0) // 👈 tidak bisa dihapus semua
                    // ->minItems(1) // 👈 tidak bisa dihapus semua
                    ->createItemButtonLabel('Tambah Uraian Kegiatan')
                    ->columns(1)
                    ->columnSpanFull(),

                    // Placeholder::make('total_semua')
                    //         ->label('Total Semua Uraian')
                    //         ->content(fn ($get) => 'Rp ' . number_format(
                    //             collect($get('uraianProgram') ?? [])->sum('jumlah'),
                    //             0, ',', '.'
                    //         ))
                    //         ->columnSpanFull(),// 👈 tambahkan ini
                    // Ganti Placeholder::make(...) dengan ini:
                    // Placeholder::make('total_semua')
                    //     ->content(fn ($get) => 'Rp ' . number_format(
                    //     collect($get('uraianProgram') ?? [])->sum('jumlah'),
                    //     0, ',', '.'
                    // ))
                    // ->columnSpanFull(),

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

                    // ========== PAGU KOMITE (Otomatis + Live 100%) ==========
                // TextInput::make('pagu_komite')
                //     ->label('Pagu Komite (Otomatis)')
                //     ->prefix('Rp ')
                //     ->numeric()
                //     ->readOnly()
                //     ->dehydrated() // tetap simpan ke DB
                //     ->live()       // WAJIB
                //     ->reactive()   // <--- INI YANG PALING PENTING! Biar dengar perubahan repeater
                //     ->formatStateUsing(fn ($get) => self::hitungPaguKomite($get))
                //     ->dehydrateStateUsing(fn ($get) => self::hitungPaguKomite($get))
                //     ->afterStateHydrated(fn ($component, $get) => $component->state(self::hitungPaguKomite($get)))
                //     ->extraAttributes([
                //         'class' => 'text-2xl font-bold text-primary-600 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-right border border-primary-200 dark:border-primary-800'
                //     ])
                //     ->columnSpanFull(),

            ]);
    }

    private static function hitungPaguKomite($get): int
    {
        // Total semua uraian
        $totalUraian = collect($get('uraianProgram') ?? [])->sum('jumlah') ?? 0;

        // Ambil pagu_dipa dan PASTIKAN bersih dari titik & karakter lain
        $paguDipaString = $get('pagu_dipa') ?? '0';
        $paguDipa = (int) preg_replace('/\D/', '', $paguDipaString);

        $hasil = $totalUraian - $paguDipa;
        return max($hasil, 0);
    }
}
