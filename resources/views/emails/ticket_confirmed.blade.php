<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .header { background: #1e3a8a; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .bib-box { background: #f0fdf4; border: 2px solid #22c55e; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0; }
        .bib-box .number { font-size: 32px; font-weight: bold; color: #15803d; letter-spacing: 2px; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Pembayaran Dikonfirmasi!</h2>
            <p style="margin: 4px 0 0 0;">{{ $event->title }}</p>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $participant->full_name }}</strong>,</p>
            <p>Selamat! Pendaftaran Anda untuk <strong>{{ $event->title }}</strong> telah berhasil diverifikasi dan lunas.</p>
            
            <div class="bib-box">
                <div style="font-size: 13px; color: #166534; text-transform: uppercase;">Nomor Dada Lari Anda</div>
                <div class="number">{{ $participant->bib_number }}</div>
                <div style="font-size: 14px; font-weight: bold; color: #15803d;">{{ $participant->bib_name ?: $participant->full_name }}</div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b;">Kategori Lari</td>
                    <td style="padding: 6px 0; font-weight: bold;">{{ $participant->category?->name }} ({{ $participant->category?->distance_km }} KM)</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;">Ukuran Jersey</td>
                    <td style="padding: 6px 0; font-weight: bold;">{{ $participant->jerseySize?->size_name }} ({{ ucfirst($participant->jerseySize?->gender_type ?? 'Unisex') }})</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;">Tanggal Lomba</td>
                    <td style="padding: 6px 0; font-weight: bold;">{{ $event->event_start_date->format('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;">Lokasi Start</td>
                    <td style="padding: 6px 0; font-weight: bold;">{{ $event->race_location_name }}</td>
                </tr>
            </table>

            @php
                $targetUrl = !empty($downloadUrl) ? $downloadUrl : route('public.ticket.download', $participant->qr_token);
                if (str_contains($targetUrl, 'localhost') || str_contains($targetUrl, '127.0.0.1')) {
                    $targetUrl = str_replace(['http://localhost:8000', 'http://127.0.0.1:8000', 'http://localhost'], 'https://app.jelatix.com', $targetUrl);
                }
            @endphp
            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ $targetUrl }}" target="_blank" style="background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                    📥 Unduh E-Ticket Resmi (PDF)
                </a>
            </div>

            <p style="margin-top: 20px; font-size: 13px; color: #64748b;">
                E-Ticket resmi Anda juga telah dilampirkan pada email ini dalam format PDF lengkap dengan barcode QR Code untuk penukaran <strong>Race Pack Collection (RPC)</strong>.
            </p>
            <p style="font-size: 13px; color: #64748b;">
                Anda juga dapat mencari dan mengunduh tiket sewaktu-waktu di website resmi Jelatix melalui menu <strong>Cek Tiket</strong> menggunakan NIK atau Email Anda.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Jelatix Ticketing System. All rights reserved.
        </div>
    </div>
</body>
</html>
