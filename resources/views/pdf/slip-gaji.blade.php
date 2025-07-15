<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $gaji->karyawan->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
        }
        /* Penyesuaian Header untuk Logo */
        .header-table {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        .logo-cell {
            width: 25%;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 150px; /* Atur lebar maksimal logo */
            height: auto;
        }
        .title-cell {
            width: 75%;
            text-align: right;
            vertical-align: middle;
        }
        .title-cell h1 {
            margin: 0;
            font-size: 22px;
            color: #2c3e50;
            font-weight: 600;
        }
        .title-cell p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }
        /* Akhir Penyesuaian Header */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table {
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 8px;
            font-size: 12px;
        }
        .info-table td:nth-child(1), .info-table td:nth-child(3) {
            width: 15%;
        }
        .info-table td:nth-child(2), .info-table td:nth-child(4) {
            width: 35%;
        }
        .main-table th, .main-table td {
            border: 1px solid #cccccc;
            padding: 8px;
            text-align: left;
        }
        .main-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .note-section {
            margin-top: 20px;
            font-size: 11px;
        }
        .note {
            font-style: italic;
            color: #555;
        }
        .footer {
            margin-top: 50px;
        }
        .footer td {
            padding-top: 10px;
            text-align: center;
            width: 50%;
        }
        .signature-space {
            height: 60px;
        }
        .total-summary-row td {
            background-color: #ecf0f1;
            font-weight: bold;
            font-size: 13px;
        }
        .total-breakdown-row td {
            background-color: #f8f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo Perusahaan" class="logo-img">
                </td>
                <td class="title-cell">
                    <h1>SLIP GAJI</h1>
                    <p>PT. WIN GLOBAL SOLUSITAMA</p>
                </td>
            </tr>
        </table>
        <table class="info-table">
            <tr>
                <td><strong>Nama</strong></td>
                <td>: {{ $gaji->karyawan->nama_lengkap }}</td>
                <td><strong>Jabatan</strong></td>
                <td>: {{ $gaji->karyawan->jabatan }}</td>
            </tr>
            <tr>
                <td><strong>Periode</strong></td>
                <td>: {{ $gaji->periode_bulan }} {{ $gaji->periode_tahun }}</td>
                <td><strong>Tanggal Bayar</strong></td>
                <td>: {{ $gaji->tanggal_bayar->format('d F Y') }}</td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th colspan="2">PENDAPATAN</th>
                    <th colspan="2">POTONGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right">{{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                    <td>BPJS</td>
                    <td class="text-right">{{ number_format($gaji->bpjs, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Transport</td>
                    <td class="text-right">{{ number_format($gaji->transport, 0, ',', '.') }}</td>
                    <td>Absen</td>
                    <td class="text-right">{{ number_format($gaji->absen, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Kehadiran</td>
                    <td class="text-right">{{ number_format($gaji->tun_kehadiran, 0, ',', '.') }}</td>
                    <td>Kas Bon</td>
                    <td class="text-right">{{ number_format($gaji->kas_bon, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Komunikasi</td>
                    <td class="text-right">{{ number_format($gaji->tun_komunikasi, 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Lembur</td>
                    <td class="text-right">{{ number_format($gaji->lembur, 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="total-breakdown-row">
                    <td class="font-bold">TOTAL PENDAPATAN</td>
                    <td class="text-right font-bold">{{ number_format($gaji->total_pendapatan, 0, ',', '.') }}</td>
                    <td class="font-bold">TOTAL POTONGAN</td>
                    <td class="text-right font-bold">{{ number_format($gaji->total_potongan, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-summary-row">
                    <td colspan="2" class="font-bold">JUMLAH DITERIMA (Take Home Pay)</td>
                    <td colspan="2" class="text-right font-bold">{{ number_format($gaji->total_diterima, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note-section">
            <strong>Catatan:</strong>
            <span class="note">{{ $gaji->note ?? 'Tidak ada catatan.' }}</span>
        </div>

        <table class="footer">
            <tr>
                <td>HRD</td>
                <td>Diterima Oleh,</td>
            </tr>
            <tr>
                <td class="signature-space"></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td>(___________________)</td>
                <td>({{ $gaji->karyawan->nama_lengkap }})</td>
            </tr>
        </table>
    </div>
</body>
</html>