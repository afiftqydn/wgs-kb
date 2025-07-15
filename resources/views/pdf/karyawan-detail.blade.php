<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Karyawan - {{ $karyawan->nama_lengkap }}</title>
    <style>
        /* Menggunakan font modern dari Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --brand-color: #1D4ED8; /* Biru yang lebih formal */
            --text-primary: #1F2937; /* Hitam pekat */
            --text-secondary: #6B7280; /* Abu-abu untuk label */
            --border-color: #E5E7EB; /* Abu-abu sangat muda untuk garis pemisah */
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            color: var(--text-primary);
            background-color: #fff;
            margin: 0;
        }

        .page-container {
            padding: 30px;
        }

        /* --- HEADER --- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--brand-color);
            margin-bottom: 30px;
        }
        .header .logo {
            width: 140px;
            height: auto;
        }
        .header .company-info {
            text-align: right;
        }
        .header .company-info h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--brand-color);
        }
        .header .company-info p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* --- PROFIL UTAMA --- */
        .profile-hero {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }
        .profile-hero .photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--border-color);
            object-fit: cover;
            margin-right: 25px;
        }
        .profile-hero .info h2 {
            margin: 0 0 5px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .profile-hero .info p {
            margin: 0;
            font-size: 18px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        /* --- STRUKTUR KONTEN UTAMA --- */
        .section {
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            align-items: center;
            font-size: 16px;
            font-weight: 600;
            color: var(--brand-color);
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 15px;
        }

        /* --- ITEM DATA (PENGGANTI TABEL) --- */
        .data-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            width: 100%;
        }
        .data-item.half {
            width: 48%;
        }
        .data-item.half:nth-child(odd) {
            margin-right: 4%;
        }
        .data-item .label {
            font-weight: 500;
            color: var(--text-secondary);
            padding-right: 15px;
        }
        .data-item .value {
            font-weight: 500;
            text-align: right;
            word-break: break-word;
        }
        .section .data-item:last-child {
            border-bottom: none;
        }

        /* --- FOOTER --- */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            text-align: center;
            font-size: 10px;
            color: #9CA3AF;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="page-container">
        
        <header class="header">
            <div>
                <img src="{{ public_path('images/logo.png') }}" alt="Logo Perusahaan" class="logo">
            </div>
            <div class="company-info">
                <h1>Profil Karyawan</h1>
                <p>PT. WIN GLOBAL SOLUSITAMA</p>
            </div>
        </header>

        <section class="profile-hero">
            @if($karyawan->pas_foto && file_exists(storage_path('app/public/' . $karyawan->pas_foto)))
                <img src="{{ storage_path('app/public/' . $karyawan->pas_foto) }}" alt="Foto Profil" class="photo">
            @else
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAYAAAA8AXHiAAABpElEQVR42u3BMQEAAADCoPVPbQwfoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAbwL3wAABK9W26gAAAABJRU5ErkJggg==" alt="Foto Profil" class="photo">
            @endif
            <div class="info">
                <h2>{{ $karyawan->nama_lengkap }}</h2>
                <p>{{ $karyawan->jabatan }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                Data Pribadi
            </div>
            <div class="data-item">
                <span class="label">Email :</span>
                <span class="value">{{ $karyawan->email }}</span>
            </div>
            <div class="data-item">
                <span class="label">Tempat, Tanggal Lahir :</span>
                <span class="value">{{ $karyawan->tempat_lahir }}, {{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->isoFormat('D MMMM Y') }}</span>
            </div>
            <div class="data-grid">
                <div class="data-item half">
                    <span class="label">Jenis Kelamin :</span>
                    <span class="value">{{ $karyawan->jenis_kelamin }}</span>
                </div>
                <div class="data-item half">
                    <span class="label">Agama :</span>
                    <span class="value">{{ $karyawan->agama }}</span>
                </div>
            </div>
            <div class="data-item">
                <span class="label">Status Pernikahan :</span>
                <span class="value">{{ $karyawan->status_pernikahan }}</span>
            </div>
            <div class="data-item">
                <span class="label">Alamat KTP :</span>
                <span class="value">{{ $karyawan->alamat_ktp }}</span>
            </div>
            <div class="data-item">
                <span class="label">Alamat Domisili :</span>
                <span class="value">{{ $karyawan->alamat_domisili }}</span>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                Informasi Pekerjaan
            </div>
            <div class="data-item">
                <span class="label">Status Karyawan :</span>
                <span class="value">{{ $karyawan->status_karyawan }}</span>
            </div>
             <div class="data-item">
                <span class="label">Tanggal Bergabung :</span>
                <span class="value">{{ \Carbon\Carbon::parse($karyawan->tanggal_bergabung)->isoFormat('D MMMM Y') }}</span>
            </div>
            @if($karyawan->status_karyawan === 'Kontrak/PKWT' && $karyawan->tanggal_berakhir_kontrak)
            <div class="data-item">
                <span class="label">Kontrak Berakhir :</span>
                <span class="value">{{ \Carbon\Carbon::parse($karyawan->tanggal_berakhir_kontrak)->isoFormat('D MMMM Y') }}</span>
            </div>
            @endif
             <div class="data-item">
                <span class="label">Kantor / Wilayah :</span>
                <span class="value">{{ $karyawan->region->name ?? '-' }}</span>
            </div>
            <div class="data-item">
                <span class="label">No. Handphone :</span>
                <span class="value">{{ $karyawan->no_hp }}</span>
            </div>
        </section>

        <section class="section">
             <div class="section-header">
                Finansial & Legal
            </div>
             <div class="data-item">
                <span class="label">Nomor NPWP :</span>
                <span class="value">{{ $karyawan->npwp ?: '-' }}</span>
            </div>
             <div class="data-item">
                <span class="label">No. BPJS Ketenagakerjaan :</span>
                <span class="value">{{ $karyawan->bpjs_ketenagakerjaan ?: '-' }}</span>
            </div>
             <div class="data-item">
                <span class="label">No. BPJS Kesehatan :</span>
                <span class="value">{{ $karyawan->bpjs_kesehatan ?: '-' }}</span>
            </div>
            <div class="data-item">
                <span class="label">Nama Bank :</span>
                <span class="value">{{ $karyawan->nama_bank ?: '-' }}</span>
            </div>
            <div class="data-item">
                <span class="label">Nomor Rekening :</span>
                <span class="value">{{ $karyawan->nomor_rekening ?: '-' }}</span>
            </div>
             <div class="data-item">
                <span class="label">Atas Nama Rekening :</span>
                <span class="value">{{ $karyawan->nama_pemilik_rekening ?: '-' }}</span>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                Kontak Darurat
            </div>
            <div class="data-item">
                <span class="label">Nama Kontak :</span>
                <span class="value">{{ $karyawan->nama_kontak_darurat }}</span>
            </div>
             <div class="data-item">
                <span class="label">Hubungan :</span>
                <span class="value">{{ $karyawan->hubungan_kontak_darurat }}</span>
            </div>
             <div class="data-item">
                <span class="label">No. Handphone :</span>
                <span class="value">{{ $karyawan->no_hp_kontak_darurat }}</span>
            </div>
        </section>


        <footer class="footer">
            Dokumen ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB
            <br>
            © {{ date('Y') }} PT. WIN GLOBAL SOLUSITAMA
        </footer>
    </div>
</body>
</html>
