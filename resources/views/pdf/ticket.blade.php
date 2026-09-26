<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Ticket {{ $participant->full_name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #1a202c;
            background-color: #f7fafc;
        }
        .ticket-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #1e3a8a;
            color: #ffffff;
            padding: 20px 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .bib-banner {
            background: #0ea5e9;
            color: #ffffff;
            padding: 12px 24px;
            text-align: center;
        }
        .bib-number {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0;
        }
        .bib-name {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            margin: 2px 0 0 0;
        }
        .body-section {
            padding: 24px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 4px;
            font-size: 13px;
            vertical-align: top;
        }
        .label {
            color: #64748b;
            font-weight: 500;
            width: 35%;
        }
        .value {
            color: #0f172a;
            font-weight: 600;
        }
        .qr-section {
            text-align: center;
            border-top: 2px dashed #cbd5e1;
            padding: 20px;
            background: #f8fafc;
        }
        .qr-code {
            display: inline-block;
            margin-bottom: 8px;
        }
        .qr-instruction {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        .footer {
            background: #f1f5f9;
            padding: 12px 24px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="ticket-card">
        <div class="header">
            <h1>{{ $event->title }}</h1>
            <p>{{ $event->race_location_name }} | {{ $event->event_start_date->format('d F Y, H:i') }} WIB</p>
        </div>

        <div class="bib-banner">
            <div class="bib-number">{{ $participant->bib_number ?: 'NOMOR BIB AKAN DITERBITKAN' }}</div>
            <div class="bib-name">{{ $participant->bib_name ?: $participant->full_name }}</div>
        </div>

        <div class="body-section">
            <table class="info-table">
                <tr>
                    <td class="label">Kategori Lari</td>
                    <td class="value">{{ $category->name }} ({{ $category->distance_km }} KM)</td>
                </tr>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="value">{{ $participant->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Identitas (NIK/Passport)</td>
                    <td class="value">{{ $participant->id_number }}</td>
                </tr>
                <tr>
                    <td class="label">Ukuran Jersey</td>
                    <td class="value">{{ $jerseySize ? $jerseySize->size_name . ' (' . ucfirst($jerseySize->gender_type) . ')' : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Golongan Darah</td>
                    <td class="value">{{ $participant->blood_type }}</td>
                </tr>
                <tr>
                    <td class="label">Kontak Darurat</td>
                    <td class="value">{{ $participant->emergency_contact_name }} ({{ $participant->emergency_contact_phone }})</td>
                </tr>
                @if($participant->wave_group)
                <tr>
                    <td class="label">Gelombang Start</td>
                    <td class="value">{{ $participant->wave_group }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Kode Order</td>
                    <td class="value">{{ $order ? $order->order_code : 'INV-VVIP' }}</td>
                </tr>
            </table>
        </div>

        <div class="qr-section">
            <div class="qr-code">
                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="150" height="150" alt="QR Code Race Pack">
            </div>
            <p class="qr-instruction">Tunjukkan QR Code ini kepada panitia saat <strong>Race Pack Collection (RPC)</strong></p>
            <p style="font-size: 11px; color: #94a3b8; margin: 4px 0 0 0;">Token: {{ $participant->qr_token }}</p>
        </div>

        <div class="footer">
            Harap membawa KTP/Kartu Identitas asli saat pengambilan Race Pack. Ukuran jersey bersifat final dan tidak dapat ditukar.
        </div>
    </div>
</body>
</html>
