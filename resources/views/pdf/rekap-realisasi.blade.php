<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rencana Kerja dan Anggaran Madrasah (RKAM)</title>
    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 15px;
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
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
        }

        thead th {
            background: #e5e7eb;
            padding: 5px 6px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }

        tbody td {
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .group-row td {
            background: #f4c7d3;
            font-weight: bold;
        }

        .subtotal-row td {
            font-weight: bold;
            background: #f3f4f6;
        }

        .grand-total-row td {
            font-weight: bold;
            background: #d1d5db;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>Rencana Kerja dan Anggaran Madrasah (RKAM)</h1>
    <h2>MTsN 1 Kota Malang</h2>
    <div class="subtitle">
        {{ $periodeLabel ?? ('Tahun Anggaran ' . ($tahun->tahun ?? '-')) }} &middot; Dicetak pada {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 24px;">NO.</th>
                <th rowspan="2">URAIAN ANGGARAN BELANJA</th>
                <th colspan="5">SUMBER DANA</th>
            </tr>
            <tr>
                <th>KOMITE</th>
                <th>REALISASI</th>
                <th>PENGAJUAN BERJALAN</th>
                <th>PENGEMBALIAN SISA ANGGARAN</th>
                <th>SISA ANGGARAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fmt = function ($n) {
                    if (!$n) return '-';
                    $formatted = number_format(abs($n), 0, ',', '.');
                    return $n < 0 ? '- ' . $formatted : $formatted;
                };
            @endphp

            @forelse ($groups as $groupIndex => $group)
                <tr class="group-row">
                    <td class="text-center">{{ $groupIndex + 1 }}</td>
                    <td colspan="6">{{ $group['standar']->nama_standar ?? '-' }}</td>
                </tr>

                @forelse ($group['rows'] as $rowIndex => $row)
                    <tr>
                        <td class="text-center">{{ $rowIndex + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        <td class="text-right">{{ $fmt($row['komite']) }}</td>
                        <td class="text-right">{{ $fmt($row['realisasi']) }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">{{ $fmt($row['sisa']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td></td>
                        <td colspan="6" class="text-center">Tidak ada program kegiatan</td>
                    </tr>
                @endforelse

                <tr class="subtotal-row">
                    <td colspan="2">JUMLAH NO.{{ $groupIndex + 1 }} (Biaya {{ $group['standar']->nama_standar ?? '-' }})</td>
                    <td class="text-right">{{ $fmt($group['subtotal_komite']) }}</td>
                    <td class="text-right">{{ $fmt($group['subtotal_realisasi']) }}</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ $fmt($group['subtotal_sisa']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse

            @if ($groups->isNotEmpty())
                <tr class="grand-total-row">
                    <td colspan="2">JUMLAH KESELURUHAN</td>
                    <td class="text-right">{{ $fmt($grandTotalKomite) }}</td>
                    <td class="text-right">{{ $fmt($grandTotalRealisasi) }}</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ $fmt($grandTotalSisa) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
