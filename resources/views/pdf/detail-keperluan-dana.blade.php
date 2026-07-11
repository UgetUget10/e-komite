<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 2px 0;
            text-align: center;
        }

        h2 {
            font-size: 12px;
            margin: 0 0 4px 0;
            text-align: center;
            font-weight: normal;
        }

        .subtitle {
            text-align: center;
            font-size: 9px;
            color: #555;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
        }

        thead th {
            background: #f3f4f6;
            padding: 6px 8px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
        }

        tbody td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        tfoot td {
            padding: 8px;
            font-weight: bold;
            background: #f9fafb;
        }

        .highlight {
            background: #fdf2f8;
        }

        .highlight-success {
            background: #ecfdf5;
        }

        .table-compact th,
        .table-compact td {
            padding: 4px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <h1>Rencana Kerja dan Anggaran Madrasah (RKAM)</h1>
    <h2>{{ $judul }}</h2>
    <div class="subtitle">
        {{ $periodeLabel }} &middot; Dicetak pada {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    @php
        $fmt = fn ($n) => $n ? number_format($n, 2, ',', '.') : '-';
    @endphp

    @if ($activeTab === 'bulanan')
        <table class="table-compact">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">NO.</th>
                    <th rowspan="2" class="text-left">URAIAN ANGGARAN BELANJA</th>
                    <th colspan="12">REALISASI PER BULAN</th>
                    <th rowspan="2">JUMLAH (Rp)</th>
                </tr>
                <tr>
                    @foreach ($bulanLabels as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($bulananRows as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        @foreach ($row['bulan'] as $nominalBulan)
                            <td class="text-right">{{ $fmt($nominalBulan) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($row['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center">Belum ada program kegiatan</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($bulananRows) > 0)
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">JUMLAH</td>
                        @foreach ($totalPerBulan as $totalBulan)
                            <td class="text-right">{{ $fmt($totalBulan) }}</td>
                        @endforeach
                        <td class="text-right highlight-success">{{ $fmt($totalRealisasiBulanan) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @elseif ($activeTab === 'pagu')
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 24px;">NO.</th>
                    <th rowspan="2" class="text-left">URAIAN ANGGARAN BELANJA</th>
                    <th colspan="3">KEPERLUAN DANA SATU TAHUN</th>
                </tr>
                <tr>
                    <th>DIPA (Rp)</th>
                    <th>BOS (Rp)</th>
                    <th>KOMITE (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_dipa']) }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_bos']) }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_komite']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada program kegiatan</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($rows) > 0)
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">JUMLAH</td>
                        <td class="text-right">{{ $fmt($totalDipa) }}</td>
                        <td class="text-right">{{ $fmt($totalBos) }}</td>
                        <td class="text-right highlight">{{ $fmt($totalKomite) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 24px;">NO.</th>
                    <th class="text-left">URAIAN ANGGARAN BELANJA</th>
                    <th>PAGU KOMITE (Rp)</th>
                    <th>REALISASI (Rp)</th>
                    <th>SISA ANGGARAN (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($realisasiRows as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_komite']) }}</td>
                        <td class="text-right">{{ $fmt($row['realisasi']) }}</td>
                        <td class="text-right">{{ $fmt($row['sisa']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada program kegiatan</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($realisasiRows) > 0)
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">JUMLAH</td>
                        <td class="text-right">{{ $fmt($totalKomiteRealisasi) }}</td>
                        <td class="text-right highlight-success">{{ $fmt($totalRealisasi) }}</td>
                        <td class="text-right">{{ $fmt($totalSisa) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</body>
</html>
