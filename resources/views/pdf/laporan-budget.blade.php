<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Budget</title>
    <style>
        @page {
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FF9800;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
        }

        .header p {
            margin: 3px 0;
            font-size: 9pt;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-item {
            display: table-cell;
            width: 33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }

        .summary-label {
            font-size: 8pt;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #FF9800;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            border-top: 2px solid #FF9800;
            padding-top: 20px;
        }

        .footer-item {
            display: table-cell;
            width: 33%;
            padding: 10px 0;
        }

        .footer-label {
            font-size: 8pt;
            color: #666;
        }

        .footer-value {
            font-size: 11pt;
            font-weight: bold;
            color: #333;
        }

        .status-good {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .status-warning {
            background: rgba(255, 152, 0, 0.2);
            color: #FF9800;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .status-danger {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Laporan Budget Tahunan</h1>
        <p>Tahun {{ $data['tahun'] }}</p>
        <p>Dihasilkan: {{ $data['generated_at'] }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">TOTAL PAGU KOMITE</div>
            <div class="summary-value">Rp {{ number_format($data['total_pagu'], 0, '.', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL REALISASI</div>
            <div class="summary-value">Rp {{ number_format($data['total_realisasi'], 0, '.', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">EXECUTION RATE</div>
            <div class="summary-value">{{ $data['total_pagu'] > 0 ? round(($data['total_realisasi'] / $data['total_pagu']) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 14%;">STANDAR</th>
                <th style="width: 20%;">PROGRAM KEGIATAN</th>
                <th style="width: 12%;" class="text-right">PAGU (Rp)</th>
                <th style="width: 12%;" class="text-right">REALISASI (Rp)</th>
                <th style="width: 13%;" class="text-right">PENGAJUAN BERJALAN (Rp)</th>
                <th style="width: 12%;" class="text-right">PENGEMBALIAN (Rp)</th>
                <th style="width: 13%;" class="text-right">SISA (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['programs'] as $index => $program)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $program['standar'] }}</td>
                    <td>{{ $program['judul'] }}</td>
                    <td class="text-right">{{ number_format($program['pagu'], 0, '.', '.') }}</td>
                    <td class="text-right">{{ number_format($program['realisasi'], 0, '.', '.') }}</td>
                    <td class="text-right">{{ number_format($program['pengajuan_berjalan'], 0, '.', '.') }}</td>
                    <td class="text-right">{{ number_format($program['pengembalian'], 0, '.', '.') }}</td>
                    <td class="text-right{{ $program['sisa'] < 0 ? ' status-danger' : '' }}">{{ number_format($program['sisa'], 0, '.', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <table style="border: none;">
            <tr>
                <td class="footer-item">
                    <div class="footer-label">TOTAL PAGU</div>
                    <div class="footer-value">Rp {{ number_format($data['total_pagu'], 0, '.', '.') }}</div>
                </td>
                <td class="footer-item">
                    <div class="footer-label">TOTAL REALISASI</div>
                    <div class="footer-value">Rp {{ number_format($data['total_realisasi'], 0, '.', '.') }}</div>
                </td>
                <td class="footer-item">
                    <div class="footer-label">PENGAJUAN BERJALAN</div>
                    <div class="footer-value">Rp {{ number_format($data['total_pengajuan_berjalan'], 0, '.', '.') }}</div>
                </td>
            </tr>
            <tr>
                <td class="footer-item">
                    <div class="footer-label">PENGEMBALIAN</div>
                    <div class="footer-value">Rp {{ number_format($data['total_pengembalian'], 0, '.', '.') }}</div>
                </td>
                <td class="footer-item">
                    <div class="footer-label">SISA ANGGARAN</div>
                    <div class="footer-value">Rp {{ number_format($data['total_sisa'], 0, '.', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
