<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Program Kegiatan</title>
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
        }

        .subtitle {
            font-size: 9px;
            color: #666;
            margin-bottom: 16px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            background: #fef3c7;
            color: #92400e;
            font-weight: bold;
            font-size: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10.5px;
        }

        .info-table .label {
            color: #666;
            width: 130px;
        }

        .info-table .value {
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 18px 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 2px solid #1f2937;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary-table th {
            background: #1f2937;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 9.5px;
        }

        .summary-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        table.uraian {
            width: 100%;
            border-collapse: collapse;
        }

        table.uraian th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 9.5px;
            text-align: left;
        }

        table.uraian td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 10px;
        }

        table.uraian tfoot td {
            font-weight: bold;
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Detail Program Kegiatan</h1>
    <div class="subtitle">Dicetak pada {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y H:i') }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Kode Program</td>
            <td class="value"><span class="badge">{{ $record->kode_lengkap ?? '-' }}</span></td>
            <td class="label">Standar</td>
            <td class="value">{{ trim(($record->standars->kode ?? '') . '. ' . ($record->standars->nama_standar ?? '-')) }}</td>
        </tr>
        <tr>
            <td class="label">Judul Program</td>
            <td class="value" colspan="3">{{ $record->judul_program ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Anggaran</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Kebutuhan</th>
                <th>Pagu DIPA</th>
                <th>Pagu Komite</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Rp {{ number_format($record->total ?? 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($record->pagu_dipa ?? 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($record->pagu_komite ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Daftar Uraian Kegiatan</div>
    <table class="uraian">
        <thead>
            <tr>
                <th style="width: 24px;">No</th>
                <th>Nama Uraian</th>
                <th class="text-center" style="width: 80px;">Banyaknya</th>
                <th class="text-right" style="width: 120px;">Harga Satuan</th>
                <th class="text-right" style="width: 120px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($record->uraianProgram as $index => $uraian)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $uraian->nama_uraian ?? '-' }}</td>
                    <td class="text-center">{{ $uraian->banyaknya ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($uraian->satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($uraian->jumlah ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada uraian kegiatan</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($record->uraianProgram->sum('jumlah'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
