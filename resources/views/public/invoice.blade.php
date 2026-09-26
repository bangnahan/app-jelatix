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
            <div class="bg-slate-900 border-2 border-emerald-500 rounded-3xl p-8 shadow-2xl text-center mb-8">
                <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/40">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-2xl font-black text-white mb-1">PEMBAYARAN LUNAS!</h1>
                <p class="text-xs text-slate-400 mb-6">Pendaftaran Anda untuk <strong>{{ $order->event?->title }}</strong> telah resmi terkonfirmasi.</p>

                @foreach($order->participants as $p)
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-5 mb-6 text-center">
                        <span class="text-xs text-emerald-400 font-bold uppercase tracking-widest block">Nomor Dada (BIB) Anda:</span>
                        <span class="text-4xl font-extrabold text-white font-mono tracking-wider my-1 block">
                            {{ $p->bib_number ?: 'SEDANG DI-GENERATE' }}
                        </span>
                        <span class="text-xs font-semibold text-emerald-300 uppercase">{{ $p->bib_name ?: $p->full_name }}</span>
                    </div>

                    <a 
                        href="{{ route('public.ticket.download', $p->qr_token) }}" 
                        class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black py-4 px-6 rounded-xl text-sm transition shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        UNDUH E-TICKET RESMI (PDF)
                    </a>
                @endforeach

                <div class="mt-6 text-xs text-slate-500">
                    E-Ticket juga telah dikirimkan ke email <strong>{{ $order->customer_email }}</strong>.
                </div>
            </div>
        @else
            <!-- PENDING PAYMENT INVOICE -->
            <div class="bg-slate-900 border border-white/10 rounded-3xl p-6 sm:p-8 shadow-2xl mb-8">
                <!-- Status & Timer Banner -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
                    <div>
                        <span class="text-[11px] text-slate-400 uppercase tracking-widest block">Batas Waktu Bayar</span>
                        <span id="countdownTimer" class="text-lg font-black text-amber-400 font-mono">30:00</span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        Menunggu Pembayaran
                    </span>
                </div>

                <!-- Total Amount -->
                <div class="text-center mb-6">
                    <span class="text-xs text-slate-400 uppercase tracking-wider block">Total yang Harus Dibayar</span>
                    <div class="text-3xl sm:text-4xl font-black text-sky-400 font-mono my-1">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-slate-400">Metode: <strong class="text-white">{{ $order->tripay_payment_method }}</strong></span>
                </div>

                <!-- Payment Details Card (QRIS vs VA) -->
                @if($order->tripay_payment_method === 'QRIS' && $order->tripay_qr_url)
                    <div class="bg-white rounded-2xl p-6 text-slate-950 text-center mb-6">
                        <img src="{{ $order->tripay_qr_url }}" alt="QRIS Code" class="w-64 h-64 mx-auto rounded-lg mb-2">
                        <p class="text-xs text-slate-600 font-semibold">
                            Scan kode QRIS di atas menggunakan BCA Mobile, GoPay, OVO, Dana, ShopeePay, atau Livin Mandiri.
                        </p>
                    </div>
                @elseif($order->tripay_pay_code)
                    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 text-center mb-6">
                        <span class="text-xs text-slate-400 uppercase tracking-wider block">Nomor Virtual Account</span>
                        <div class="text-2xl sm:text-3xl font-black text-white font-mono my-2 flex items-center justify-center gap-3">
                            <span id="vaCode">{{ $order->tripay_pay_code }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $order->tripay_pay_code }}'); alert('Nomor VA berhasil disalin!')" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-xs text-sky-300 cursor-pointer">
                                Salin
                            </button>
                        </div>
                        <p class="text-xs text-slate-400">
                            Transfer sesuai nominal persis ke nomor Virtual Account di atas.
                        </p>
                    </div>
                @endif

                <!-- Order Detail Summary -->
                <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 text-xs space-y-2 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Event Lari:</span>
                        <span class="font-bold text-white">{{ $order->event?->title }}</span>
                    </div>
                    @foreach($order->participants as $p)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama Pelari:</span>
                            <span class="font-semibold text-white">{{ $p->full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kategori:</span>
                            <span class="font-semibold text-sky-400">{{ $p->category?->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jersey:</span>
                            <span class="font-semibold text-white">{{ $p->jerseySize?->size_name }} ({{ ucfirst($p->jerseySize?->gender_type ?? 'Unisex') }})</span>
                        </div>
                    @endforeach
                </div>

                <!-- Action Button & Polling -->
                <div class="space-y-3">
                    <button 
                        type="button" 
                        onclick="checkPaymentStatus()" 
                        class="w-full bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold py-3.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-sky-500/20"
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
        let countdown = 1800; // 30 mins in seconds
        const timerEl = document.getElementById('countdownTimer');

        setInterval(() => {
            if (countdown > 0) {
                countdown--;
                const m = Math.floor(countdown / 60);
                const s = countdown % 60;
                timerEl.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }
        }, 1000);

        async function checkPaymentStatus() {
            try {
                const res = await fetch('/orders/{{ $order->order_code }}/status');
                const data = await res.json();
                if (data.is_paid) {
                    window.location.reload();
                } else {
                    alert('Status pembayaran masih: ' + data.status.toUpperCase() + '. Harap selesaikan pembayaran terlebih dahulu.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Auto polling every 6 seconds
        setInterval(async () => {
            try {
                const res = await fetch('/orders/{{ $order->order_code }}/status');
                const data = await res.json();
                if (data.is_paid) {
                    window.location.reload();
                }
            } catch (e) {}
        }, 6000);
    </script>
    @endif
</body>
</html>
