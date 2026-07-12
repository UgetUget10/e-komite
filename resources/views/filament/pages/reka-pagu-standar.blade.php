<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-main-black mb-1">Rencana Kegiatan dan Anggaran Madrasah (RKAM)</h2>
            <p class="text-sm text-main-secondary font-medium">{{ $data['namaSekolah'] }}</p>
            <p class="text-sm text-main-secondary font-medium">Tahun {{ $data['tahun'] }}</p>
        </div>

        <!-- Scroll hint -->
        <p class="reka-scroll-hint">↔ Geser tabel untuk melihat kolom realisasi &amp; sisa anggaran</p>

        <!-- Table Wrapper with horizontal scroll -->
        <div class="reka-table-wrapper">
            <div class="reka-table-container">
                <table class="reka-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="reka-th reka-th-no reka-sticky reka-sticky-1">NO.</th>
                            <th rowspan="2" class="reka-th reka-th-left reka-sticky reka-sticky-2">URAIAN ANGGARAN BELANJA</th>
                            <th colspan="3" class="reka-th reka-th-colspan">KEPERLUAN DANA SATU TAHUN</th>
                            <th colspan="4" class="reka-th reka-th-colspan reka-th-colspan-realisasi">REALISASI &amp; SISA ANGGARAN</th>
                        </tr>
                        <tr class="reka-th-sub">
                            <th class="reka-th">DIPA (Rp)</th>
                            <th class="reka-th">BOS (Rp)</th>
                            <th class="reka-th">KOMITE (Rp)</th>
                            <th class="reka-th">REALISASI (Rp)</th>
                            <th class="reka-th">PENGAJUAN BERJALAN (Rp)</th>
                            <th class="reka-th">PENGEMBALIAN (Rp)</th>
                            <th class="reka-th">SISA (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="reka-row">
                                <td class="reka-td reka-td-center reka-sticky reka-sticky-1">{{ $row['no'] }}</td>
                                <td class="reka-td reka-td-left reka-sticky reka-sticky-2">{{ $row['nama_standar'] }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['dipa'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['bos'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['komite'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['realisasi'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['pengajuan_berjalan'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right">{{ number_format($row['pengembalian'], 0, '.', '.') }}</td>
                                <td class="reka-td reka-td-right {{ $row['sisa'] < 0 ? 'reka-td-negative' : '' }}">{{ number_format($row['sisa'], 0, '.', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="reka-td reka-td-center reka-empty">
                                    Belum ada data standar untuk tahun {{ $data['tahun'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="reka-row-footer">
                            <td class="reka-td-foot reka-td-center reka-sticky reka-sticky-1 reka-td-foot-sticky">&nbsp;</td>
                            <td class="reka-td-foot reka-td-center reka-sticky reka-sticky-2 reka-td-foot-sticky">JUMLAH</td>
                            <td class="reka-td-foot reka-td-right">{{ number_format($data['totals']['dipa'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right">{{ number_format($data['totals']['bos'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right dkd-foot-highlight">{{ number_format($data['totals']['komite'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right">{{ number_format($data['totals']['realisasi'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right">{{ number_format($data['totals']['pengajuan_berjalan'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right">{{ number_format($data['totals']['pengembalian'], 0, '.', '.') }}</td>
                            <td class="reka-td-foot reka-td-right {{ $data['totals']['sisa'] < 0 ? 'reka-td-negative' : '' }}">{{ number_format($data['totals']['sisa'], 0, '.', '.') }}</td>
                        </tr>
                        <tr class="reka-row-footer">
                            <td colspan="9" class="reka-td-foot reka-td-center">JUMLAH KESELURUHAN (DIPA/BOS dan KOMITE)</td>
                        </tr>
                        <tr class="reka-row-footer">
                            <td colspan="9" class="reka-td-foot reka-td-center reka-grand-total">
                                {{ number_format($data['totals']['grand_total'], 0, '.', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-center mt-8">
            <a href="{{ route('filament.kelola.pages.dashboard') }}"
                class="px-6 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200"
                style="background: linear-gradient(135deg, var(--color-main-amber), rgb(255, 152, 0)); color: var(--color-main-black);">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <style>
        .reka-scroll-hint {
            @apply text-xs font-medium text-center mb-2;
            color: var(--color-main-secondary);
        }

        @media (min-width: 1280px) {
            .reka-scroll-hint {
                display: none;
            }
        }

        .reka-table-wrapper {
            @apply relative rounded-2xl overflow-hidden shadow-lg;
            border: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
        }

        .reka-table-container {
            @apply overflow-x-auto;
            -webkit-overflow-scrolling: touch;
        }

        .reka-table {
            @apply border-collapse;
            width: 100%;
            min-width: 1100px;
            background: linear-gradient(135deg, var(--color-main-white) 0%, rgba(254, 241, 211, 1) 100%);
            table-layout: auto;
        }

        .reka-th {
            @apply px-3 py-3 font-semibold text-main-black text-center whitespace-nowrap;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, rgba(40, 43, 47, 0.03), rgba(255, 152, 0, 0.05));
            position: relative;
        }

        .reka-th-colspan {
            background: linear-gradient(135deg, rgba(40, 43, 47, 0.05), rgba(255, 152, 0, 0.08));
        }

        .reka-th-colspan-realisasi {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.04));
        }

        .reka-th-sub {
            background: linear-gradient(135deg, rgba(40, 43, 47, 0.02), rgba(255, 152, 0, 0.03));
        }

        .reka-th-no {
            min-width: 50px;
            width: 50px;
        }

        .reka-th-left {
            text-align: left;
            min-width: 190px;
            width: 190px;
        }

        .reka-td {
            @apply px-3 py-2.5 text-sm border;
            border-color: rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .reka-td-center {
            text-align: center;
            min-width: 50px;
        }

        .reka-td-left {
            text-align: left;
            white-space: normal;
            min-width: 190px;
            word-break: break-word;
        }

        .reka-td-right {
            text-align: right;
            min-width: 120px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .reka-row {
            @apply transition-all duration-200;
        }

        .reka-row:hover {
            background: linear-gradient(90deg, rgba(255, 152, 0, 0.02), transparent);
        }

        .reka-td-foot {
            @apply font-semibold border px-3 py-3;
            font-size: 0.8rem;
            border-color: rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, rgba(40, 43, 47, 0.05), rgba(255, 152, 0, 0.08));
        }

        .reka-row-footer {
            @apply font-bold;
        }

        .dkd-foot-highlight {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 152, 0, 0.08)) !important;
            color: var(--color-main-black);
            font-weight: 600;
        }

        .reka-grand-total {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-main-black);
            letter-spacing: 0.02em;
        }

        .reka-td-negative {
            color: #dc2626;
            font-weight: 700;
        }

        .reka-empty {
            color: var(--color-main-secondary);
            padding: 2rem !important;
            font-style: italic;
            white-space: normal;
        }

        /* Kolom NO. dan URAIAN tetap terlihat (sticky) saat tabel di-scroll horizontal */
        .reka-sticky {
            position: sticky;
            z-index: 2;
        }

        .reka-sticky-1 {
            left: 0;
        }

        .reka-sticky-2 {
            left: 50px;
        }

        thead .reka-sticky {
            z-index: 3;
        }

        td.reka-sticky {
            background: white;
        }

        .reka-row:hover td.reka-sticky {
            background: #fdf6ea;
        }

        .reka-td-foot-sticky {
            background: linear-gradient(135deg, rgba(40, 43, 47, 0.05), rgba(255, 152, 0, 0.08)) !important;
        }

        .reka-sticky-2::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 6px;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
            pointer-events: none;
        }

        /* Scrollbar styling */
        .reka-table-container::-webkit-scrollbar {
            height: 8px;
        }

        .reka-table-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .reka-table-container::-webkit-scrollbar-thumb {
            background: rgba(255, 152, 0, 0.5);
            border-radius: 4px;
        }

        .reka-table-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 152, 0, 0.7);
        }
    </style>
</x-filament-panels::page>
