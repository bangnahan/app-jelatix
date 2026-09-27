<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran: {{ $order->order_code }} - Jelatix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #090e1a 0%, #0f172a 50%, #1e1b4b 100%);
            color: #f8fafc;
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Navbar -->
    <header class="border-b border-white/10 backdrop-blur-xl sticky top-0 z-50 bg-slate-950/70">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="text-2xl font-black tracking-tight bg-gradient-to-r from-sky-400 to-indigo-300 bg-clip-text text-transparent">JELATIX</span>
                <span class="text-[10px] tracking-wider uppercase px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-bold border border-sky-400/30">Invoice</span>
            </a>
            <div class="text-xs text-slate-400 font-mono">Order: {{ $order->order_code }}</div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-10 flex-1 w-full">
        @if(session('success'))
            <div class="bg-emerald-500/20 border-2 border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm mb-6 text-center font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if($order->isPaid())
            <!-- PAID SUCCESS CARD -->
            <div class="bg-slate-900 border-2 border-emerald-500 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-2xl text-center mb-8">
                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/40">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white mb-1">PEMBAYARAN LUNAS!</h1>
                <p class="text-xs text-slate-400 mb-6">Pendaftaran Anda untuk <strong>{{ $order->event?->title }}</strong> telah resmi terkonfirmasi.</p>

                @foreach($order->participants as $p)
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 sm:p-5 mb-5 text-center">
                        <span class="text-[11px] sm:text-xs text-emerald-400 font-bold uppercase tracking-widest block">Nomor Dada (BIB) Anda:</span>
                        <span class="text-3xl sm:text-4xl font-extrabold text-white font-mono tracking-wider my-1 block break-all">
                            {{ $p->bib_number ?: 'SEDANG DI-GENERATE' }}
                        </span>
                        <span class="text-xs font-semibold text-emerald-300 uppercase block truncate">{{ $p->bib_name ?: $p->full_name }}</span>
                    </div>

                    <a 
                        href="{{ route('public.ticket.download', $p->qr_token) }}" 
                        class="w-full bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 font-black py-3.5 sm:py-4 px-4 sm:px-6 rounded-xl text-xs sm:text-sm transition shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer mb-3"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        UNDUH E-TICKET RESMI (PDF)
                    </a>
                @endforeach

                <div class="mt-5 text-xs text-slate-400">
                    E-Ticket juga telah dikirimkan ke email <strong>{{ $order->customer_email }}</strong>.
                </div>

                <form action="{{ route('public.order.resend_ticket', $order->order_code) }}" method="POST" class="mt-3">
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-400 hover:text-sky-300 bg-sky-950/40 hover:bg-sky-900/50 border border-sky-800/40 px-3 py-1.5 rounded-lg transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Kirim Ulang E-Ticket ke Email</span>
                    </button>
                </form>
            </div>
        @else
            <!-- PENDING PAYMENT INVOICE -->
            @php
                $remainingSeconds = max(0, $order->expired_at ? now()->diffInSeconds($order->expired_at, false) : 1800);
                $isExpired = ($remainingSeconds <= 0);
            @endphp

            <div class="bg-slate-900 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl mb-8">
                <!-- Status & Timer Banner -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-5 sm:mb-6">
                    <div>
                        <span class="text-[10px] sm:text-[11px] text-slate-400 uppercase tracking-widest block">Batas Waktu Bayar</span>
                        <span id="countdownTimer" class="text-base sm:text-lg font-black {{ $isExpired ? 'text-rose-400' : 'text-amber-400' }} font-mono">
                            {{ $isExpired ? '00:00 (KADALUARSA)' : sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60) }}
                        </span>
                    </div>
                    @if($isExpired)
                        <span class="px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30">
                            Waktu Bayar Habis
                        </span>
                    @else
                        <span class="px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Menunggu Pembayaran
                        </span>
                    @endif
                </div>

                <!-- Total Amount with 1-Click Copy -->
                <div class="text-center mb-5 sm:mb-6 bg-slate-950/60 p-4 sm:p-5 rounded-2xl border border-slate-800">
                    <span class="text-xs text-slate-400 uppercase tracking-wider block mb-1">Total yang Harus Dibayar</span>
                    <div class="flex flex-wrap items-center justify-center gap-2 my-1">
                        <div class="text-2xl sm:text-4xl font-black text-sky-400 font-mono">
                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                        </div>
                        <button 
                            type="button" 
                            onclick="copyAmount('{{ (int)$order->grand_total }}')" 
                            class="px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-300 border border-sky-500/30 text-xs font-semibold transition active:scale-95 cursor-pointer flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span id="copyAmountBtnText">Salin Nominal</span>
                        </button>
                    </div>
                    <span class="text-xs text-slate-400">Metode Pembayaran: <strong class="text-white font-mono">{{ $order->tripay_payment_method }}</strong></span>
                </div>

                @if($order->tripay_checkout_url && !str_contains($order->tripay_checkout_url, url("/orders/{$order->order_code}")))
                    <div class="mb-5 sm:mb-6">
                        <a 
                            href="{{ $order->tripay_checkout_url }}" 
                            target="_blank" 
                            class="w-full bg-gradient-to-r from-sky-400 via-sky-500 to-indigo-500 hover:from-sky-300 hover:to-indigo-400 active:scale-95 text-slate-950 font-black py-4 px-5 rounded-2xl text-xs sm:text-sm transition shadow-xl shadow-sky-500/25 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span>Buka Halaman Pembayaran & Panduan Tripay &rarr;</span>
                        </a>
                    </div>
                @endif

                <!-- Payment Details Card (QRIS vs VA vs Minimarket vs E-Wallet/Paylater) -->
                @if(str_starts_with($order->tripay_payment_method, 'QRIS') && $order->tripay_qr_url)
                    <div class="bg-white rounded-2xl p-4 sm:p-6 text-slate-950 text-center mb-5 sm:mb-6 shadow-xl">
                        <img src="{{ $order->tripay_qr_url }}" alt="QRIS Code" class="max-w-[220px] sm:max-w-[260px] w-full aspect-square mx-auto rounded-lg mb-3 object-contain border border-slate-200 shadow-inner">
                        <p class="text-xs text-slate-700 font-semibold mb-3 leading-relaxed">
                            Buka m-Banking atau E-Wallet pilihan Anda (BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay) lalu arahkan kamera ke kode QR di atas.
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ $order->tripay_qr_url }}" target="_blank" download="QRIS-{{ $order->order_code }}.png" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span>Unduh Gambar QRIS</span>
                            </a>
                        </div>
                    </div>
                @elseif($order->tripay_pay_code)
                    @php
                        $isRetail = in_array($order->tripay_payment_method, ['ALFAMART', 'INDOMARET', 'ALFAMIDI']);
                        $isEwallet = in_array($order->tripay_payment_method, ['OVO', 'DANA', 'SHOPEEPAY']);
                        $isPaylater = in_array($order->tripay_payment_method, ['KREDIVO', 'AKULAKU']);
                    @endphp
                    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 sm:p-6 text-center mb-5 sm:mb-6">
                        <span class="text-xs text-slate-400 uppercase tracking-wider block mb-1">
                            @if($isRetail)
                                Kode Pembayaran Kasir ({{ $order->tripay_payment_method }})
                            @elseif($isEwallet)
                                Nomor Akun / Tagihan ({{ $order->tripay_payment_method }})
                            @elseif($isPaylater)
                                Kode Referensi Pembayaran ({{ $order->tripay_payment_method }})
                            @else
                                Nomor Virtual Account ({{ $order->tripay_payment_method }})
                            @endif
                        </span>
                        <div class="my-3 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-3 bg-slate-900/80 p-3.5 rounded-xl border border-slate-700">
                            <span id="vaCode" class="text-xl sm:text-2xl font-black text-white font-mono tracking-wider break-all select-all">{{ $order->tripay_pay_code }}</span>
                            <button 
                                type="button" 
                                id="copyVaBtn"
                                onclick="copyVaCode()" 
                                class="px-3.5 py-1.5 rounded-lg bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-500/40 text-xs font-bold transition active:scale-95 cursor-pointer shrink-0 flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span id="copyVaBtnText">Salin Nomor VA</span>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            @if($isRetail)
                                Sebutkan pembayaran "Tripay" kepada kasir minimarket dan tunjukkan kode pembayaran di atas.
                            @elseif($isEwallet)
                                Silakan buka aplikasi {{ $order->tripay_payment_method }} untuk menyelesaikan konfirmasi pembayaran.
                            @elseif($isPaylater)
                                Selesaikan pembayaran cicilan melalui aplikasi {{ $order->tripay_payment_method }} Anda.
                            @else
                                Transfer sesuai nominal persis ke nomor Virtual Account di atas sebelum batas waktu berakhir.
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Panduan Pembayaran Interaktif (Accordion) -->
                <div class="mb-6 bg-slate-950/80 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-3.5 sm:p-4 border-b border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Panduan Cara Pembayaran</span>
                        </span>
                        <span class="text-[10px] text-slate-400">Klik untuk melihat langkah</span>
                    </div>

                    <div class="divide-y divide-slate-800/80 text-xs">
                        @if(str_starts_with($order->tripay_payment_method, 'QRIS'))
                            <details class="group p-3 sm:p-4 cursor-pointer" open>
                                <summary class="font-bold text-sky-400 flex items-center justify-between list-none">
                                    <span>Langkah Pembayaran via QRIS (Semua Bank & E-Wallet)</span>
                                    <span class="transition group-open:rotate-180 text-slate-400">&darr;</span>
                                </summary>
                                <ol class="list-decimal list-inside text-slate-300 space-y-1.5 mt-3 leading-relaxed pl-1 text-[11px] sm:text-xs">
                                    <li>Buka aplikasi m-Banking (BCA Mobile, Livin Mandiri, BRImo, BNI) atau E-Wallet (GoPay, OVO, DANA, ShopeePay).</li>
                                    <li>Pilih menu <strong>Scan / Bayar / QRIS</strong>.</li>
                                    <li>Arahkan kamera ke kode QR di layar atau upload file gambar QR yang telah diunduh.</li>
                                    <li>Pastikan nama merchant tertera <strong>Tripay</strong> dan total bayar sesuai <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>.</li>
                                    <li>Masukkan PIN transaksi Anda untuk menyelesaikan pembayaran. Sistem akan mendeteksi pelunasan secara otomatis.</li>
                                </ol>
                            </details>
                        @elseif(in_array($order->tripay_payment_method, ['ALFAMART', 'INDOMARET', 'ALFAMIDI']))
                            <details class="group p-3 sm:p-4 cursor-pointer" open>
                                <summary class="font-bold text-sky-400 flex items-center justify-between list-none">
                                    <span>Langkah Pembayaran di Kasir Gerai Ritel</span>
                                    <span class="transition group-open:rotate-180 text-slate-400">&darr;</span>
                                </summary>
                                <ol class="list-decimal list-inside text-slate-300 space-y-1.5 mt-3 leading-relaxed pl-1 text-[11px] sm:text-xs">
                                    <li>Kunjungi gerai <strong>{{ $order->tripay_payment_method }}</strong> terdekat.</li>
                                    <li>Sampaikan kepada kasir bahwa Anda ingin melakukan pembayaran merchant <strong>Tripay</strong>.</li>
                                    <li>Tunjukkan Kode Pembayaran: <strong class="text-white font-mono">{{ $order->tripay_pay_code }}</strong> kepada kasir.</li>
                                    <li>Kasir akan menyebutkan nominal <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>.</li>
                                    <li>Bayar sesuai nominal dan simpan struk fisik bukti pembayaran.</li>
                                </ol>
                            </details>
                        @else
                            <!-- Virtual Account Instructions -->
                            <details class="group p-3 sm:p-4 cursor-pointer" open>
                                <summary class="font-bold text-sky-400 flex items-center justify-between list-none">
                                    <span>Langkah 1: Bayar via Mobile Banking (m-Banking)</span>
                                    <span class="transition group-open:rotate-180 text-slate-400">&darr;</span>
                                </summary>
                                <ol class="list-decimal list-inside text-slate-300 space-y-1.5 mt-3 leading-relaxed pl-1 text-[11px] sm:text-xs">
                                    <li>Login ke aplikasi Mobile Banking Anda.</li>
                                    <li>Pilih menu <strong>Transfer</strong> &gt; <strong>Virtual Account</strong> (atau Pembayaran VA).</li>
                                    <li>Masukkan Nomor VA: <strong class="text-white font-mono">{{ $order->tripay_pay_code }}</strong>.</li>
                                    <li>Pastikan nominal tagihan tertera persis <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>.</li>
                                    <li>Konfirmasi pembayaran dan masukkan PIN m-Banking Anda.</li>
                                </ol>
                            </details>

                            <details class="group p-3 sm:p-4 cursor-pointer">
                                <summary class="font-bold text-slate-300 hover:text-white flex items-center justify-between list-none">
                                    <span>Langkah 2: Bayar via Mesin ATM</span>
                                    <span class="transition group-open:rotate-180 text-slate-400">&darr;</span>
                                </summary>
                                <ol class="list-decimal list-inside text-slate-300 space-y-1.5 mt-3 leading-relaxed pl-1 text-[11px] sm:text-xs">
                                    <li>Masukkan kartu ATM dan PIN Anda di mesin ATM.</li>
                                    <li>Pilih menu <strong>Transaksi Lainnya</strong> &gt; <strong>Transfer</strong> &gt; <strong>Ke Rekening Virtual Account</strong>.</li>
                                    <li>Ketikkan nomor VA: <strong class="text-white font-mono">{{ $order->tripay_pay_code }}</strong> lalu tekan Benar.</li>
                                    <li>Periksa kembali data pendaftar dan jumlah pembayaran, lalu selesaikan transaksi.</li>
                                    <li>Ambil struk bukti pembayaran dari mesin ATM.</li>
                                </ol>
                            </details>
                        @endif
                    </div>
                </div>

                <!-- Order Detail Summary -->
                <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 text-xs space-y-2 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Event Lari:</span>
                        <span class="font-bold text-white">{{ $order->event?->title }}</span>
                    </div>
                    <div class="border-t border-slate-800 pt-3 mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Daftar Pelari Terdaftar:</span>
                            <span class="text-[10px] font-mono text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full border border-sky-500/20">
                                {{ $order->participants->count() }} Orang
                            </span>
                        </div>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @foreach($order->participants as $idx => $p)
                                <div class="bg-slate-900/90 p-2.5 rounded-xl border border-slate-800 flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <div class="font-bold text-white text-xs flex items-center gap-1.5 truncate">
                                            <span class="w-4 h-4 rounded-full bg-sky-500/20 text-sky-400 text-[10px] flex items-center justify-center font-mono shrink-0">{{ $idx + 1 }}</span>
                                            <span class="truncate">{{ $p->full_name }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            BIB: <strong class="text-sky-300 font-mono uppercase">{{ $p->bib_name ?: $p->full_name }}</strong>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-bold text-sky-400 block font-mono">{{ $p->category?->name }}</span>
                                        <span class="text-[10px] text-slate-400">Jersey: {{ $p->jerseySize?->size_name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Button & Polling -->
                <div class="space-y-3">
                    <button 
                        type="button" 
                        onclick="checkPaymentStatus()" 
                        class="w-full bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-sky-500/20 active:scale-95"
                    >
                        <svg id="refreshIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Saya Sudah Bayar (Cek Status Sekarang)
                    </button>

                    <!-- Sandbox Testing Simulator Button -->
                    <form action="{{ route('public.order.simulate', $order->order_code) }}" method="POST">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-amber-300 font-semibold py-2.5 px-4 rounded-xl text-xs transition cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <span>🧪 Simulasi Pelunasan Langsung (Tripay Sandbox Mode)</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </main>

    <!-- Auto Polling Script -->
    @if(!$order->isPaid())
    <script>
        let countdown = {{ $remainingSeconds }};
        const timerEl = document.getElementById('countdownTimer');

        if (countdown > 0) {
            const timerInterval = setInterval(() => {
                if (countdown > 0) {
                    countdown--;
                    const m = Math.floor(countdown / 60);
                    const s = countdown % 60;
                    if (timerEl) {
                        timerEl.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    }
                } else {
                    clearInterval(timerInterval);
                    if (timerEl) {
                        timerEl.innerText = '00:00 (KADALUARSA)';
                        timerEl.className = 'text-base sm:text-lg font-black text-rose-400 font-mono';
                    }
                }
            }, 1000);
        }

        async function checkPaymentStatus() {
            const btn = event?.currentTarget;
            if (btn) btn.disabled = true;
            try {
                const res = await fetch('/orders/{{ $order->order_code }}/status');
                const data = await res.json();
                if (data.is_paid) {
                    window.location.reload();
                } else {
                    alert('Status pembayaran saat ini: ' + data.status.toUpperCase() + '.\n\nSistem belum mendeteksi pembayaran masuk. Jika Anda baru saja transfer, mohon tunggu 1-2 menit agar Tripay memverifikasi transaksi.');
                }
            } catch (e) {
                console.error(e);
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        // Auto polling every 5 seconds
        setInterval(async () => {
            try {
                const res = await fetch('/orders/{{ $order->order_code }}/status');
                const data = await res.json();
                if (data.is_paid) {
                    window.location.reload();
                }
            } catch (e) {}
        }, 5000);

        function copyVaCode() {
            const el = document.getElementById('vaCode');
            const code = el ? el.innerText.trim() : '{{ $order->tripay_pay_code }}';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => {
                    const btnText = document.getElementById('copyVaBtnText');
                    if (btnText) {
                        btnText.innerText = 'Tersalin!';
                        setTimeout(() => { btnText.innerText = 'Salin Nomor VA'; }, 2000);
                    }
                }).catch(() => {
                    prompt('Salin nomor ini:', code);
                });
            } else {
                prompt('Salin nomor ini:', code);
            }
        }

        function copyAmount(amt) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(amt).then(() => {
                    const btnText = document.getElementById('copyAmountBtnText');
                    if (btnText) {
                        btnText.innerText = 'Tersalin!';
                        setTimeout(() => { btnText.innerText = 'Salin Nominal'; }, 2000);
                    }
                }).catch(() => {
                    prompt('Salin nominal ini:', amt);
                });
            } else {
                prompt('Salin nominal ini:', amt);
            }
        }
    </script>
    @endif
</body>
</html>
