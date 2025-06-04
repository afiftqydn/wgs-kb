<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keputusan Permohonan {{ $loanApplication->application_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
        }

        .content {
            margin-top: 30px;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .content th,
        .content td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .content th {
            background-color: #f2f2f2;
        }

        .decision {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
        }

        .decision.approved {
            background-color: #e6ffe6;
            border-color: #009900;
        }

        .decision.rejected {
            background-color: #ffe6e6;
            border-color: #990000;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PT WGS (Win Global Solusitama)</h1>
        <p>Kantor Cabang/Unit:
            {{ $loanApplication->processingRegion->name ?? ($loanApplication->inputRegion->name ?? 'N/A') }}</p>
        <p>Tanggal Cetak: {{ date('d M Y') }}</p>
        <hr>
        <h2>SURAT KEPUTUSAN PERMOHONAN PEMBIAYAAN</h2>
        Nomor Permohonan: <strong>{{ $loanApplication->application_number }}</strong>
    </div>

    <div class="content">
        <p>Dengan hormat,</p>
        <p>Berdasarkan permohonan pembiayaan yang diajukan oleh:</p>
        <table>
            <tr>
                <th style="width: 30%;">Nama Nasabah</th>
                <td>{{ $loanApplication->customer->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td>{{ $loanApplication->customer->nik ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $loanApplication->customer->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Jenis Produk</th>
                <td>{{ $loanApplication->productType->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Jumlah Diajukan</th>
                <td>Rp {{ number_format($loanApplication->amount_requested, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Tanggal Pengajuan</th>
                <td>{{ $loanApplication->created_at->format('d M Y') }}</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">Setelah melalui proses analisis dan evaluasi, bersama ini kami sampaikan bahwa
            permohonan pembiayaan Bapak/Ibu tersebut telah:</p>

        @if ($loanApplication->status == 'APPROVED')
            <div class="decision approved">
                <strong>DISETUJUI</strong>
                <p>Catatan: {{ $workflowNotes['APPROVED'] ?? 'Sesuai ketentuan yang berlaku.' }}</p>
                {{-- Tambahkan detail persetujuan jika ada, misal jumlah disetujui, tenor, dll. --}}
            </div>
        @elseif($loanApplication->status == 'REJECTED')
            <div class="decision rejected">
                <strong>DITOLAK</strong>
                <p>Alasan: {{ $workflowNotes['REJECTED'] ?? 'Tidak memenuhi persyaratan.' }}</p>
            </div>
        @else
            <div class="decision">
                <strong>STATUS BELUM FINAL: {{ $loanApplication->status }}</strong>
                <p>Permohonan masih dalam proses atau status tidak mendukung pencetakan surat keputusan.</p>
            </div>
        @endif

        {{-- Bagian Tanda Tangan --}}
        <table style="margin-top: 50px; border: none;">
            <tr style="border: none;">
                <td style="width: 70%; border: none;"></td>
                <td style="text-align: center; border: none;">
                    Hormat kami,<br><br><br><br><br>
                    ( {{ $decisionMakerName ?? 'Pejabat Berwenang' }} )<br>
                    {{ $decisionMakerRole ?? 'PT WGS' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dokumen ini dicetak oleh sistem pada tanggal {{ date('d M Y H:i:s') }} dan sah tanpa tanda tangan basah jika
        disertai stempel resmi.
    </div>
</body>

</html>
