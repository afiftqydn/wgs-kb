<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dokumen Lengkap Permohonan - {{ $loanApplication->application_number }}</title>
    <style>
        @page {
            margin: 30mm 25mm 25mm 25mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header,
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            color: #aaa;
            font-size: 9px;
        }

        .header {
            top: -22mm;
            text-align: right;
        }

        .footer {
            bottom: -20mm;
            text-align: center;
        }

        .footer .page-number:before {
            content: "Halaman " counter(page);
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            color: #1d4a23;
        }

        h2 {
            font-size: 14px;
            color: #1d4a23;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        table.info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        table.info-table td {
            padding: 6px;
            vertical-align: top;
        }

        table.info-table td:first-child {
            font-weight: bold;
            width: 30%;
        }

        .decision-box {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid;
            border-radius: 5px;
        }

        .decision-box.approved {
            border-color: #28a745;
            background-color: #eaf7ed;
        }

        .decision-box.rejected {
            border-color: #dc3545;
            background-color: #fdecea;
        }

        .decision-box strong {
            font-size: 16px;
            text-transform: uppercase;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
        }

        .signature-box .name {
            margin-top: 60px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .signature-box .role {
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .attachment-header {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .attachment-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
            margin-bottom: 15px;
        }

        ul.document-list {
            list-style-type: none;
            padding-left: 0;
        }

        ul.document-list li {
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="header"></div>
    <div class="footer">
        Dibuat oleh Sistem WGS Kalbar | <span class="page-number"></span>
    </div>

    <main>
        {{-- ================= HALAMAN 1: SURAT KEPUTUSAN ================= --}}
        <h1>Surat Keputusan Permohonan Pembiayaan</h1>
        <table class="info-table" style="border: none;">
            <tr>
                <td>Nomor Permohonan</td>
                <td>: <strong>{{ $loanApplication->application_number }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Keputusan</td>
                <td>: {{ date('d M Y') }}</td>
            </tr>
            <tr>
                <td>Nasabah</td>
                <td>: {{ $loanApplication->customer->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <p>Dengan hormat,</p>
        <p>Berdasarkan permohonan pembiayaan yang diajukan, setelah melalui proses analisis dan evaluasi, bersama ini
            kami sampaikan bahwa permohonan tersebut telah:</p>

        <div class="decision-box {{ strtolower($loanApplication->status) }}">
            <strong>{{ $loanApplication->status }}</strong>
        </div>

        <div class="signature-section clearfix">
            <div class="signature-box">
                Hormat kami,<br>
                <div class="name">( {{ $decisionMakerName ?? 'Pejabat Berwenang' }} )</div>
                <div class="role">{{ $decisionMakerRole ?? 'PT WGS' }}</div>
            </div>
        </div>

        {{-- ================= HALAMAN 2: RANGKUMAN ================= --}}
        <div class="page-break">
            <h1>Rangkuman Data Pengajuan</h1>

            <h2>A. Detail Permohonan</h2>
            <table class="info-table">
                <tr>
                    <td>Jenis Produk</td>
                    <td>: {{ $loanApplication->productType->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Jumlah Diajukan</td>
                    <td>: Rp {{ number_format($loanApplication->amount_requested, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tujuan Pembiayaan</td>
                    <td>: {{ $loanApplication->purpose ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Wilayah Input</td>
                    <td>: {{ $loanApplication->inputRegion->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Unit Pemroses</td>
                    <td>: {{ $loanApplication->processingRegion->name ?? 'N/A' }}</td>
                </tr>
            </table>

            <h2>B. Detail Nasabah Pemohon</h2>
            <table class="info-table">
                <tr>
                    <td>NIK</td>
                    <td>: {{ $loanApplication->customer->nik ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <td>: {{ $loanApplication->customer->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>: {{ $loanApplication->customer->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: {{ $loanApplication->customer->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Wilayah Domisili</td>
                    <td>: {{ $loanApplication->customer->region->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        {{-- ================= HALAMAN 3+: LAMPIRAN ================= --}}
        @if ($imageDocuments->isNotEmpty())
            @foreach ($imageDocuments as $doc)
                <div class="page-break">
                    <div class="attachment-header">Lampiran: {{ $doc->document_type }}</div>

                    @if ($doc->base64image)
                        <img src="{{ $doc->base64image }}" class="attachment-image">
                    @else
                        <p style="color: red; border: 1px solid red; padding: 10px;">
                            <strong>Error:</strong> File gambar '{{ $doc->file_name }}' tidak dapat ditemukan di
                            server.
                        </p>
                    @endif
                </div>
            @endforeach
        @endif

        @if ($pdfDocuments->isNotEmpty())
            <div class="page-break">
                <div class="attachment-header">Daftar Dokumen Tambahan (Format PDF)</div>
                <p>Dokumen berikut terlampir secara terpisah dalam format PDF:</p>
                <ul class="document-list">
                    @foreach ($pdfDocuments as $doc)
                        <li>
                            <strong>{{ $doc->document_type }}</strong><br>
                            <small>Nama File: {{ $doc->file_name }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </main>
</body>

</html>
