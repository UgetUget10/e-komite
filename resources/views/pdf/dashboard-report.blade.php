<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Laporan - Tahun {{ $tahun }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        @page {
            size: A4;
            margin: 20mm;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            background-color: #e5e7eb;
            padding: 8px 10px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #3b82f6;
        }

        .meta-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }

        table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 10px;
        }

        table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .stat-box {
            display: inline-block;
            width: 22%;
            margin-right: 2%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            vertical-align: top;
        }

        .stat-box-last {
            margin-right: 0;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-value {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 4px;
        }

        .alert-status {
            color: #dc2626;
            font-weight: bold;
        }

        .alert-ok {
            color: #16a34a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DASHBOARD KOMITE</h1>
        <p>Tahun Anggaran {{ $tahun }}</p>
    </div>

    <div class="meta-info">
        <p><strong>Pengguna:</strong> {{ $userName }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ $generatedAt }}</p>
    </div>

    <!-- SECTION 1: RINGKASAN ANGGARAN -->
    @if (!empty($paguOverview))
        <div class="section-title">1. Ringkasan Anggaran Tahun {{ $tahun }}</div>

        <div style="margin-bottom: 15px;">
            <div class="stat-box">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp {{ number_format($paguOverview['totalPagu'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Keperluan Dana</div>
                <div class="stat-value">Rp {{ number_format($paguOverview['totalKeperluanDana'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="stat-box stat-box-last">
                <div class="stat-label">Total Realisasi</div>
                <div class="stat-value">Rp {{ number_format($paguOverview['totalRealisasi'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    @endif

    <!-- SECTION 2: AKTIVITAS REALISASI TERBARU -->
    @if (!empty($recentActivity))
        <div class="section-title">2. Aktivitas Realisasi Terbaru</div>

        <table>
            <tr>
                <th>Tanggal</th>
                <th>Program</th>
                <th>Standar</th>
                <th class="text-right">Nominal (Rp)</th>
                <th class="text-center">Dokumen</th>
                <th>Status</th>
            </tr>
            @foreach ($recentActivity as $item)
                <tr>
                    <td>{{ $item['tanggal'] }}</td>
                    <td>{{ $item['program'] }}</td>
                    <td>{{ $item['standar'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item['hasDokumen'] ? '✓' : '✗' }}</td>
                    <td>{{ $item['statusUpdate'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div style="margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #d1d5db; padding-top: 10px;">
        <p>Laporan ini secara otomatis dihasilkan dari Sistem Manajemen Anggaran KOMITE</p>
    </div>
</body>
</html>
