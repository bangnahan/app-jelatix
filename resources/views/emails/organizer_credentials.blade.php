<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Login Portal Event Organizer - Jelatix</title>
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
            background: linear-gradient(135deg, #090e1a 0%, #0369a1 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 6px 0;
            font-size: 24px;
            font-weight: 800;
        }
        .header p {
            margin: 0;
            color: #bae6fd;
            font-size: 14px;
        }
        .content {
            padding: 28px 24px;
        }
        .credentials-card {
            background: #f8fafc;
            border: 2px solid #0284c7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .cred-item {
            margin-bottom: 12px;
        }
        .cred-item:last-child {
            margin-bottom: 0;
        }
        .cred-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .cred-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            background: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            display: inline-block;
        }
        .btn-login {
            display: block;
            background: #0284c7;
            color: #ffffff !important;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 24px;
            border-radius: 8px;
            margin: 24px 0 16px 0;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }
        .steps-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 16px;
            margin-top: 20px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #1e3a8a;
        }
        .steps-box ol {
            margin: 6px 0 0 0;
            padding-left: 18px;
        }
        .steps-box li {
            margin-bottom: 6px;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>Selamat Datang di Jelatix!</h1>
            <p>Portal Event Organizer & Ticketing Lomba Lari</p>
        </div>

        <div class="content">
            <p style="margin-top: 0; font-size: 15px;">
                Halo <strong>{{ $userName }}</strong>,
            </p>
            <p style="font-size: 14px; color: #475569;">
                Akun Anda sebagai penyelenggara untuk <strong>{{ $organizer->name }}</strong> telah berhasil didaftarkan di platform Jelatix. Berikut adalah kredensial login resmi Anda:
            </p>

            <div class="credentials-card">
                <div class="cred-item">
                    <div class="cred-label">URL Portal Penyelenggara (EO)</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0284c7; margin-top: 4px;">
                        <a href="{{ $loginUrl }}" style="color: #0284c7; text-decoration: underline;">{{ $loginUrl }}</a>
                    </div>
                </div>

                <div class="cred-item" style="margin-top: 14px;">
                    <div class="cred-label">Email Login</div>
                    <div class="cred-value">{{ $userEmail }}</div>
                </div>

                <div class="cred-item" style="margin-top: 14px;">
                    <div class="cred-label">Password</div>
                    <div class="cred-value" style="color: #dc2626; font-size: 18px;">{{ $password }}</div>
                </div>
            </div>

            <a href="{{ $loginUrl }}" class="btn-login">
                🚀 MASUK KE PORTAL EO SEKARANG &rarr;
            </a>

            <div class="steps-box">
                <strong>📌 Langkah Selanjutnya di Portal EO:</strong>
                <ol>
                    <li>Login ke portal menggunakan email dan password di atas.</li>
                    <li>Buat event lari baru, atur kategori jarak (5K / 10K / 21K / 42K), kuota tiket, dan ukuran kaos/jersey.</li>
                    <li>Pantau penjualan tiket, grafik registrasi pelari, dan laporan pendapatan secara real-time.</li>
                    <li>Manfaatkan portal scanner kru venue untuk pengambilan Race Pack (RPC) saat acara.</li>
                    <li>Ajukan pencairan dana penjualan tiket kapan saja langsung ke rekening bank terdaftar Anda.</li>
                </ol>
            </div>

            <p style="margin-top: 24px; font-size: 13px; color: #64748b;">
                Demi keamanan, Anda dapat mengganti password ini kapan saja setelah login melalui menu profil akun di pojok kanan atas dashboard.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 4px 0;">Email ini dikirim otomatis oleh sistem administrasi <strong>Jelatix Running Series</strong>.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Jelatix Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
