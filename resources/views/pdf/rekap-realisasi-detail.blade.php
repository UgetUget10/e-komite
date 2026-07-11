<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rencana Kerja dan Anggaran Madrasah (RKAM) - Detail Bulanan</title>
    <style>
        @page {
            margin: 20px 16px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 14px;
            margin: 0 0 2px 0;
            text-align: center;
        }

        h2 {
            font-size: 11px;
            margin: 0 0 4px 0;
            text-align: center;
            font-weight: normal;
        }

        .subtitle {
            text-align: center;
            font-size: 8px;
            color: #555;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
        }

        thead th {
            background: #e5e7eb;
            padding: 4px 3px;
            text-align: center;
            font-size: 7.5px;
            font-weight: bold;
        }

        tbody td {
            padding: 3px;
            font-size: 7.5px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .uraian-col {
            width: 160px;
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
            font-size: 8px;
        }
    </style>
</head>

<body>
    <h1>Rencana Kerja dan Anggaran Madrasah (RKAM)</h1>
    <h2>MTsN 1 Kota Malang</h2>
    <div class="subtitle">
        {{ $periodeLabel ?? 'Tahun Anggaran ' . ($tahun->tahun ?? '-') }} &middot; Dicetak pada
        {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO.</th>
                <th rowspan="2" class="uraian-col">URAIAN ANGGARAN BELANJA</th>
                <th rowspan="2">PAGU</th>
                <th colspan="12">REALISASI</th>
                <th rowspan="2">SISA ANGGARAN</th>
            </tr>
            <tr>
                @foreach ($bulanLabels as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $fmt = function ($n) {
                    if (!$n) {
                        return '-';
                    }
                    $formatted = number_format(abs($n), 0, ',', '.');
                    return $n < 0 ? '- ' . $formatted : $formatted;
                };
            @endphp

            @forelse ($groups as $groupIndex => $group)
                <tr class="group-row">
                    <td class="text-center">{{ $groupIndex + 1 }}</td>
                    <td colspan="14">{{ $group['standar']->nama_standar ?? '-' }}</td>
                </tr>

                @forelse ($group['rows'] as $rowIndex => $row)
                    <tr>
                        <td class="text-center">{{ $rowIndex + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        <td class="text-right">{{ $fmt($row['komite']) }}</td>
                        @foreach ($row['bulan'] as $nominalBulan)
                            <td class="text-right">{{ $fmt($nominalBulan) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($row['sisa']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td></td>
                        <td colspan="14" class="text-center">Tidak ada program kegiatan</td>
                    </tr>
                @endforelse

                <tr class="subtotal-row">
                    <td colspan="2">JUMLAH NO.{{ $groupIndex + 1 }} (Biaya
                        {{ $group['standar']->nama_standar ?? '-' }})</td>
                    <td class="text-right">{{ $fmt($group['subtotal_komite']) }}</td>
                    @foreach ($group['subtotal_bulan'] as $subtotalBulan)
                        <td class="text-right">{{ $fmt($subtotalBulan) }}</td>
                    @endforeach
                    <td class="text-right">{{ $fmt($group['subtotal_sisa']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse

            @if ($groups->isNotEmpty())
                <tr class="grand-total-row">
                    <td colspan="2">JUMLAH KESELURUHAN</td>
                    <td class="text-right">{{ $fmt($grandTotalKomite) }}</td>
                    @foreach ($grandTotalBulan as $totalBulan)
                        <td class="text-right">{{ $fmt($totalBulan) }}</td>
                    @endforeach
                    <td class="text-right">{{ $fmt($grandTotalSisa) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
