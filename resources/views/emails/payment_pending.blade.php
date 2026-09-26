<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pembayaran - {{ $order->order_code }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px 10px;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 0;
            color: #93c5fd;
            font-size: 14px;
            font-weight: 500;
        }
        .content {
            padding: 24px;
        }
        .alert-urgent {
            background-color: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: center;
        }
        .alert-urgent-title {
            color: #b91c1c;
            font-weight: 800;
            font-size: 16px;
            margin-bottom: 6px;
            display: block;
        }
        .alert-urgent-time {
            color: #dc2626;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin: 6px 0;
        }
        .alert-urgent-desc {
            color: #7f1d1d;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }
        .payment-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .payment-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .total-amount {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .paycode-box {
            background: #ffffff;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            margin-top: 12px;
        }
        .paycode-value {
            font-size: 24px;
            font-weight: 800;
            color: #1d4ed8;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 2px;
            word-break: break-all;
        }
        .btn-action {
            display: block;
            background: #2563eb;
            color: #ffffff !important;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            padding: 16px 24px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }
        .btn-secondary {
            display: block;
            background: #f1f5f9;
            color: #334155 !important;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 16px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 14px;
        }
        .table-details td {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-details td.label {
            color: #64748b;
            width: 40%;
        }
        .table-details td.val {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }
        .guide-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 0 8px 8px 0;
            padding: 14px;
            margin: 20px 0;
            font-size: 13px;
            color: #1e3a8a;
        }
        .guide-box ol {
            margin: 6px 0 0 0;
            padding-left: 18px;
        }
        .guide-box li {
            margin-bottom: 4px;
        }
        .participants-box {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <h1>Pendaftaran Berhasil!</h1>
            <p>{{ $event->title }}</p>
        </div>

        <div class="content">
            <p style="margin-top: 0; font-size: 15px;">
                Halo <strong>{{ $order->customer_name }}</strong>,
            </p>
            <p style="font-size: 14px; color: #475569; margin-bottom: 20px;">
                Terima kasih telah mendaftar di <strong>{{ $event->title }}</strong>. Data pendaftaran Anda telah kami terima dan slot kuota Anda telah diamankan sementara.
            </p>

            <!-- Urgency Alert: 30 Menit -->
            <div class="alert-urgent">
                <span class="alert-urgent-title">⏱️ HARAP SELESAIKAN PEMBAYARAN DALAM WAKTU 30 MENIT</span>
                <div class="alert-urgent-time">
                    Batas Waktu: {{ $order->expired_at ? $order->expired_at->format('d M Y, H:i') : now()->addMinutes(30)->format('d M Y, H:i') }} WIB
                </div>
                <p class="alert-urgent-desc">
                    Jika pembayaran tidak diterima dalam <strong>30 menit</strong> sejak pendaftaran, sistem Jelatix akan <strong>otomatis membatalkan pesanan</strong> dan melepaskan slot kuota tiket serta ukuran jersey Anda kembali ke publik.
                </p>
            </div>

            <!-- Payment Details Card -->
            <div class="payment-card">
                <div class="payment-label">Total Pembayaran</div>
                <div class="total-amount">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </div>

                <table class="table-details">
                    <tr>
                        <td class="label">Nomor Invoice</td>
                        <td class="val">{{ $order->order_code }}</td>
                    </tr>
                    <tr>
                        <td class="label">Metode Pembayaran</td>
                        <td class="val">{{ $order->tripay_payment_method ?? 'Tripay Payment Gateway' }}</td>
                    </tr>
                    @if($order->tripay_reference)
                    <tr>
                        <td class="label">Referensi Tripay</td>
                        <td class="val">{{ $order->tripay_reference }}</td>
                    </tr>
                    @endif
                </table>

                @if(!empty($order->tripay_pay_code))
                <div class="paycode-box">
                    <div style="font-size: 12px; color: #64748b; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">
                        Kode Pembayaran / Nomor Virtual Account:
                    </div>
                    <div class="paycode-value">{{ $order->tripay_pay_code }}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Salin nomor di atas dan gunakan untuk transfer / pembayaran
                    </div>
                </div>
                @endif

                @php
                    $checkoutUrl = !empty($order->tripay_checkout_url) && filter_var($order->tripay_checkout_url, FILTER_VALIDATE_URL) && !str_contains($order->tripay_checkout_url, url('/orders/'))
                        ? $order->tripay_checkout_url
                        : route('public.order.show', $order->order_code);
                @endphp

                <!-- CTA Button -->
                <a href="{{ $checkoutUrl }}" class="btn-action">
                    💳 BAYAR SEKARANG DI TRIPAY &rarr;
                </a>
                
                <a href="{{ route('public.order.show', $order->order_code) }}" class="btn-secondary">
                    📄 Buka Halaman Invoice Jelatix
                </a>
            </div>

            <!-- Panduan Cara Bayar Singkat -->
            <div class="guide-box">
                <strong>💡 Panduan Langkah Pembayaran:</strong>
                <ol>
                    <li>Klik tombol <strong>"BAYAR SEKARANG DI TRIPAY"</strong> di atas untuk membuka halaman pembayaran resmi Tripay.</li>
                    <li>Ikuti petunjuk lengkap sesuai metode pembayaran yang Anda pilih (Virtual Account, QRIS, e-Wallet, atau Gerai Ritel).</li>
                    <li>Pastikan nominal transfer sesuai persis hingga digit terakhir: <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>.</li>
                    <li>Setelah transfer berhasil, status pembayaran Anda akan terverifikasi secara instan (real-time).</li>
                    <li>E-Ticket resmi berformat PDF & Nomor BIB akan langsung dikirimkan ke email ini dan dapat langsung Anda unduh.</li>
                </ol>
            </div>

            <!-- Ringkasan Peserta -->
            <div class="participants-box">
                <h3 style="margin: 0 0 12px 0; font-size: 15px; color: #0f172a;">Data Peserta Lari:</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; text-align: left; color: #475569; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 8px;">Peserta</th>
                            <th style="padding: 8px;">Kategori</th>
                            <th style="padding: 8px;">Jersey</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->participants as $participant)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 8px;">
                                <strong>{{ $participant->full_name }}</strong><br>
                                <span style="font-size: 11px; color: #64748b;">BIB: {{ $participant->bib_name ?: '-' }}</span>
                            </td>
                            <td style="padding: 10px 8px;">
                                {{ $participant->category?->name ?? '-' }}
                            </td>
                            <td style="padding: 10px 8px;">
                                {{ $participant->jerseySize?->size_name ?? '-' }} ({{ ucfirst($participant->jerseySize?->gender_type ?? 'Unisex') }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                <p style="margin: 0 0 6px 0;"><strong>Jadwal & Lokasi Event:</strong></p>
                <p style="margin: 0 0 4px 0;">📅 Tanggal: {{ $event->event_start_date ? $event->event_start_date->format('d F Y, H:i') . ' WIB' : 'Akan diumumkan' }}</p>
                <p style="margin: 0;">📍 Lokasi: {{ $event->race_location_name ?? 'Menunggu konfirmasi' }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px 0;">Email ini dikirim otomatis oleh sistem pendaftaran <strong>Jelatix Running Series</strong>.</p>
            <p style="margin: 0;">Butuh bantuan? Silakan hubungi panitia melalui email atau media sosial resmi acara.</p>
            <p style="margin: 8px 0 0 0; color: #94a3b8;">&copy; {{ date('Y') }} Jelatix Ticketing System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
