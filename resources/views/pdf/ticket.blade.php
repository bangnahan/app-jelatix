<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>E-Ticket Resmi - {{ $participant->bib_number ?: 'PESERTA' }} - {{ $participant->full_name }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #0f172a;
            background-color: #ffffff;
            font-size: 11px;
            line-height: 1.4;
        }
        .ticket-wrapper {
            border: 2px solid #0284c7;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }
        
        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            background: #090e1a;
            color: #ffffff;
        }
        .header-table td {
            padding: 16px 20px;
            vertical-align: middle;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #38bdf8;
            margin: 0;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 9px;
            color: #94a3b8;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .event-title-header {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            text-align: right;
        }
        .event-meta-header {
            font-size: 10px;
            color: #38bdf8;
            margin: 3px 0 0 0;
            text-align: right;
        }

        /* BIB HERO BANNER */
        .bib-banner {
            background: #0284c7;
            color: #ffffff;
            text-align: center;
            padding: 14px 20px;
            border-top: 1px solid #0369a1;
            border-bottom: 2px dashed #0369a1;
        }
        .category-pill {
            display: inline-block;
            background: #082f49;
            color: #38bdf8;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .bib-number {
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 3px;
            margin: 0;
            line-height: 1;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #ffffff;
        }
        .bib-name {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 6px 0 0 0;
            color: #e0f2fe;
        }

        /* TWO COLUMN CONTENT */
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }
        .content-table td {
            vertical-align: top;
            padding: 16px 20px;
        }
        .left-col {
            width: 62%;
            border-right: 1px solid #e2e8f0;
        }
        .right-col {
            width: 38%;
            background: #f8fafc;
            text-align: center;
        }

        .section-heading {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0369a1;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table td {
            padding: 4px 2px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .data-label {
            color: #64748b;
            font-weight: 500;
            width: 42%;
        }
        .data-value {
            color: #0f172a;
            font-weight: 700;
        }

        /* RPC HIGHLIGHT BOX */
        .rpc-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 10px;
        }
        .rpc-title {
            font-size: 10px;
            font-weight: 800;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }
        .rpc-text {
            font-size: 10px;
            color: #15803d;
            margin: 0;
            line-height: 1.35;
        }

        /* QR CODE */
        .qr-box {
            padding: 6px 0;
        }
        .qr-instruction {
            font-size: 10px;
            font-weight: 700;
            color: #0f172a;
            margin: 6px 0 2px 0;
        }
        .qr-token {
            font-size: 9px;
            color: #64748b;
            font-family: monospace;
            margin: 0 0 8px 0;
            word-break: break-all;
        }
        .verified-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* NOTICE FOOTER */
        .footer-notice {
            background: #f1f5f9;
            padding: 8px 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #475569;
            text-align: center;
            line-height: 1.3;
        }

        /* SURAT KUASA SECTION */
        .auth-section {
            margin-top: 16px;
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            padding: 12px 16px;
            background: #ffffff;
        }
        .auth-heading {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
            margin: 0 0 4px 0;
        }
        .auth-desc {
            font-size: 8.5px;
            color: #64748b;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }
        .auth-table {
            width: 100%;
            border-collapse: collapse;
        }
        .auth-table td {
            vertical-align: top;
            font-size: 9px;
            padding: 2px 4px;
        }
        .sig-box {
            height: 40px;
            border-bottom: 1px solid #64748b;
            margin-top: 24px;
        }
        .sig-name {
            font-size: 8.5px;
            color: #475569;
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="ticket-wrapper">
        <!-- 1. HEADER -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <div class="brand-title">JELATIX PASS</div>
                    <div class="brand-sub">Official Race E-Ticket & Verification</div>
                </td>
                <td style="width: 50%;">
                    <div class="event-title-header">{{ $event->title }}</div>
                    <div class="event-meta-header">
                        {{ $event->event_start_date->format('d F Y, H:i') }} WIB &bull; {{ $event->race_location_name }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- 2. BIB HERO BANNER -->
        <div class="bib-banner">
            <div class="category-pill">{{ $category->name }} ({{ $category->distance_km }} KM)</div>
            <div class="bib-number">{{ $participant->bib_number ?: 'BIB AKAN DITERBITKAN' }}</div>
            <div class="bib-name">{{ $participant->bib_name ?: $participant->full_name }}</div>
        </div>

        <!-- 3. MAIN DATA & QR CODE SECTION -->
        <table class="content-table">
            <tr>
                <!-- LEFT COLUMN: RUNNER & RACE DETAILS -->
                <td class="left-col">
                    <div class="section-heading">Data Peserta Lomba</div>
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Nama Lengkap:</td>
                            <td class="data-value">{{ $participant->full_name }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Nomor Identitas (NIK/ID):</td>
                            <td class="data-value">{{ $participant->id_number }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Jenis Kelamin / Gol. Darah:</td>
                            <td class="data-value">{{ ucfirst($participant->gender) }} / {{ $participant->blood_type ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Ukuran Jersey Lari:</td>
                            <td class="data-value">{{ $jerseySize ? $jerseySize->size_name . ' (' . ucfirst($jerseySize->gender_type) . ')' : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Kontak Darurat:</td>
                            <td class="data-value">{{ $participant->emergency_contact_name }} ({{ $participant->emergency_contact_phone }})</td>
                        </tr>
                        <tr>
                            <td class="data-label">Kode Invoice / Order:</td>
                            <td class="data-value">{{ $order ? $order->order_code : 'INV-OFFICIAL' }}</td>
                        </tr>
                        @if($participant->wave_group)
                        <tr>
                            <td class="data-label">Gelombang Start (Wave):</td>
                            <td class="data-value">{{ $participant->wave_group }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- RACE PACK COLLECTION (RPC) DETAILS -->
                    <div class="rpc-box">
                        <div class="rpc-title">Pengambilan Paket Lomba (RPC)</div>
                        <p class="rpc-text">
                            @if($event->rpc_start_date)
                                <strong>Jadwal:</strong> {{ $event->rpc_start_date->format('d M Y, H:i') }}
                                @if($event->rpc_end_date)
                                    s/d {{ $event->rpc_end_date->format('d M Y, H:i') }} WIB
                                @endif
                                <br>
                            @endif
                            @if($event->rpc_location)
                                <strong>Lokasi:</strong> {{ $event->rpc_location }}
                                <br>
                            @endif
                            <strong>Syarat:</strong> Tunjukkan E-Ticket ini (digital/cetak) beserta KTP/Kartu Identitas asli.
                        </p>
                    </div>
                </td>

                <!-- RIGHT COLUMN: QR CODE SCANNER VERIFICATION -->
                <td class="right-col">
                    <div class="section-heading" style="text-align: center;">Verifikasi QR Panitia</div>
                    <div class="qr-box">
                        <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="145" height="145" alt="QR Code Race Pack">
                        <div class="qr-instruction">Scan saat Pengambilan RPC</div>
                        <div class="qr-token">Token: {{ $participant->qr_token }}</div>
                        <div class="verified-badge">&check; Pembayaran Lunas</div>
                    </div>
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 10px; line-height: 1.3;">
                        Petugas RPC akan memindai QR Code di atas untuk menyerahkan nomor BIB, Jersey, dan Race Pack Anda.
                    </div>
                </td>
            </tr>
        </table>

        <!-- 4. FOOTER NOTICE -->
        <div class="footer-notice">
            Pendaftaran dan nomor BIB bersifat personal serta tidak dapat dipindahtangankan tanpa persetujuan panitia penyelenggara. Ukuran jersey bersifat final sesuai pilihan saat registrasi dan tidak dapat ditukar saat pengambilan race pack.
        </div>
    </div>

    <!-- 5. SURAT KUASA PENGAMBILAN (LETTER OF AUTHORIZATION) -->
    <div class="auth-section">
        <div class="auth-heading">Surat Kuasa Pengambilan Race Pack (Diisi Hanya Jika Pengambilan Diwakilkan)</div>
        <p class="auth-desc">
            Saya yang bertanda tangan di bawah ini sebagai pemilik tiket resmi, memberikan kuasa penuh kepada nama di bawah ini untuk mengambil paket perlombaan (Race Pack) atas nama saya dengan melampirkan fotokopi KTP pemberi kuasa.
        </p>
        
        <table class="auth-table">
            <tr>
                <td style="width: 55%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 130px; color: #64748b;">Nama Penerima Kuasa:</td>
                            <td style="border-bottom: 1px dotted #94a3b8;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">NIK Penerima Kuasa:</td>
                            <td style="border-bottom: 1px dotted #94a3b8;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Nomor HP / WhatsApp:</td>
                            <td style="border-bottom: 1px dotted #94a3b8;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Hubungan dengan Peserta:</td>
                            <td style="border-bottom: 1px dotted #94a3b8;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 22%; text-align: center;">
                    <div style="color: #64748b;">Pemberi Kuasa (Peserta)</div>
                    <div class="sig-box"></div>
                    <div class="sig-name">({{ $participant->full_name }})</div>
                </td>
                <td style="width: 23%; text-align: center;">
                    <div style="color: #64748b;">Penerima Kuasa</div>
                    <div class="sig-box"></div>
                    <div class="sig-name">( ............................................ )</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
