<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Paket Pengajuan Bank - {{ $loanApplication->application_number }}</title>
    <style>
        @page {
            margin: 25mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header,
        .footer {
            /* ... (style header/footer seperti sebelumnya) ... */
        }

        h1,
        h2 {
            /* ... (style h1/h2 seperti sebelumnya) ... */
        }

        table.info-table {
            /* ... (style tabel seperti sebelumnya) ... */
        }

        .document-attachment {
            page-break-before: always;
            /* Setiap lampiran akan mulai di halaman baru */
            padding-top: 20px;
        }

        .attachment-header {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .attachment-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
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
    </style>
</head>

<body>
    {{-- Bagian Header, Footer, dan Detail Pengajuan (SAMA SEPERTI SEBELUMNYA) --}}
    <div class="header">Paket Pengajuan Pembiayaan WGS</div>
    <div class="footer">Dokumen ini dibuat oleh Sistem Aplikasi Pembiayaan WGS | <span class="page-number"></span></div>
    <h1>Paket Pengajuan Pembiayaan untuk Bank</h1>
    <h2>A. Detail Permohonan</h2>
    {{-- ... tabel detail permohonan ... --}}
    <h2>B. Detail Nasabah Pemohon</h2>
    {{-- ... tabel detail nasabah ... --}}

    {{-- ========================================================= --}}
    {{-- BAGIAN BARU UNTUK MENAMPILKAN LAMPIRAN DOKUMEN --}}
    {{-- ========================================================= --}}

    {{-- 1. Lampiran Dokumen Gambar --}}
    @if ($imageDocuments->isNotEmpty())
        @foreach ($imageDocuments as $doc)
            <div class="document-attachment">
                <div class="attachment-header">Lampiran: {{ $doc->document_type }}</div>
                @php
                    // Menggunakan Base64 untuk menyematkan gambar agar lebih andal
                    $imagePath = storage_path('app/public/' . $doc->file_path);
                    if (file_exists($imagePath)) {
                        $imageData = base64_encode(file_get_contents($imagePath));
                        $src = 'data:' . $doc->mime_type . ';base64,' . $imageData;
                        echo '<img src="' . $src . '" class="attachment-image">';
                    } else {
                        echo '<p>Error: File gambar tidak ditemukan.</p>';
                    }
                @endphp
            </div>
        @endforeach
    @endif

    {{-- 2. Daftar Dokumen Berformat PDF (Jika Ada) --}}
    @if ($pdfDocuments->isNotEmpty())
        <div class="document-attachment">
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

</body>

</html>
