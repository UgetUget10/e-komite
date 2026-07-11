<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reka Pagu Standar</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            margin: 1px 0;
            font-size: 8.5pt;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8.5pt;
        }

        th {
            background-color: white;
            border: 1px solid #999;
            padding: 4px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }

        td {
            border: 1px solid #999;
            padding: 3px 5px;
            font-size: 8pt;
            background-color: white;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .footer-row {
            background-color: white;
            font-weight: bold;
        }

        .highlight {
            background-color: white;
            font-weight: bold;
        }

        .grand-total {
            background-color: white;
            font-weight: bold;
            font-size: 9pt;
        }

        .signature {
            margin-top: 15px;
        }

        .signature-table {
            width: 100%;
            border: none;
            font-size: 8pt;
        }

        .signature-table td {
            border: none;
            padding: 2px 20px;
            text-align: center;
            vertical-align: top;
        }

        .sig-header {
            font-weight: bold;
            font-size: 8pt;
            padding-bottom: 2px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 7.5pt;
            padding: 3px 0;
        }

        .sig-space {
            height: 55px;
        }

        .sig-name {
            font-weight: bold;
            font-size: 8pt;
            padding-top: 2px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Rencana Kegiatan dan Anggaran Madrasah (RKAM)</h2>
        <p>{{ $data['namaSekolah'] }}</p>
        <p>Tahun {{ $data['tahun'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO.</th>
                <th style="width: 40%;">URAIAN ANGGARAN BELANJA</th>
                <th style="width: 18%;">DIPA (Rp)</th>
                <th style="width: 18%;">BOS (Rp)</th>
                <th style="width: 19%;">KOMITE (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td class="text-left">{{ $row['nama_standar'] }}</td>
                    <td class="text-right">{{ number_format($row['dipa'], 0, '.', '.') }}</td>
                    <td class="text-right">{{ number_format($row['bos'], 0, '.', '.') }}</td>
                    <td class="text-right">{{ number_format($row['komite'], 0, '.', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">
                        Belum ada data standar untuk tahun {{ $data['tahun'] }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total Row -->
    <table style="margin-top: 0;">
        <tr class="footer-row">
            <td colspan="2" class="text-center">JUMLAH</td>
            <td class="text-right">{{ number_format($data['totals']['dipa'], 0, '.', '.') }}</td>
            <td class="text-right">{{ number_format($data['totals']['bos'], 0, '.', '.') }}</td>
            <td class="highlight text-right">{{ number_format($data['totals']['komite'], 0, '.', '.') }}</td>
        </tr>
    </table>

    <!-- Grand Total -->
    <table style="margin-top: 10px;">
        <tr class="footer-row">
            <td colspan="5" class="text-center">JUMLAH KESELURUHAN (DIPA/BOS dan KOMITE)</td>
        </tr>
        <tr class="grand-total">
            <td colspan="5" class="text-center">{{ number_format($data['totals']['grand_total'], 0, '.', '.') }}
            </td>
        </tr>
    </table>

    <!-- Signature Section -->
    <div class="signature">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <div class="sig-header">Menyetujui</div>
                </td>
                <td style="width: 50%;">
                    <div class="sig-header">Malang, {{ $data['signature_date'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="sig-label">Ketua Komite MTsN 1 Kota Malang</div>
                </td>
                <td>
                    <div class="sig-label">Kepala MTsN 1 Kota Malang</div>
                </td>
            </tr>
            <tr>
                <td class="sig-space"></td>
                <td class="sig-space"></td>
            </tr>
            <tr>
                <td>
                    <div class="sig-name">{{ $data['ketua_komite_name'] }}</div>
                </td>
                <td>
                    <div class="sig-name">{{ $data['kepala_sekolah_name'] }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
