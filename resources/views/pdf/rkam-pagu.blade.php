<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RKAM - Rencana Kerja dan Anggaran Madrasah</title>
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
            padding: 5px 6px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
        }

        tbody td {
            padding: 4px 6px;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .no-col {
            width: 24px;
            text-align: center;
            vertical-align: middle;
        }

        .local-no-col {
            width: 20px;
            text-align: center;
        }

        .group-row td {
            font-weight: bold;
        }

        .subtotal-row td {
            font-weight: bold;
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h1>Rencana Kerja dan Anggaran Madrasah (RKAM)</h1>
    <h2>MTsN 1 Kota Malang</h2>
    <div class="subtitle">
        {{ $periodeLabel ?? '' }} &middot; Dicetak pada {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 24px;">NO.</th>
                <th rowspan="2" style="width: 20px;"></th>
                <th rowspan="2">URAIAN ANGGARAN BELANJA</th>
                <th colspan="2">SUMBER DANA</th>
            </tr>
            <tr>
                <th>DIPA (Rp)</th>
                <th>KOMITE (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fmt = fn ($n) => number_format($n ?? 0, 2, ',', '.');
            @endphp

            @forelse ($groups as $groupIndex => $group)
                @php $rowspan = $group['rows']->count() + 1; @endphp

                <tr class="group-row">
                    <td class="no-col" rowspan="{{ $rowspan }}">{{ $group['standar']->kode ?? ($groupIndex + 1) }}</td>
                    <td></td>
                    <td>{{ $group['standar']->nama_standar ?? '-' }}</td>
                    <td></td>
                    <td></td>
                </tr>

                @forelse ($group['rows'] as $rowIndex => $row)
                    <tr>
                        <td class="local-no-col">{{ $rowIndex + 1 }}</td>
                        <td>{{ $row['judul_program'] }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_dipa']) }}</td>
                        <td class="text-right">{{ $fmt($row['pagu_komite']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada program kegiatan</td>
                    </tr>
                @endforelse

                <tr class="subtotal-row">
                    <td></td>
                    <td colspan="2" class="text-center">JUMLAH</td>
                    <td class="text-right">{{ $fmt($group['subtotal_dipa']) }}</td>
                    <td class="text-right">{{ $fmt($group['subtotal_komite']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
