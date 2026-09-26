<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran: {{ $event->title }} - Jelatix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
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
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Navbar -->
    <header class="border-b border-white/10 backdrop-blur-xl sticky top-0 z-50 bg-slate-950/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="text-2xl font-black tracking-tight bg-gradient-to-r from-sky-400 to-indigo-300 bg-clip-text text-transparent">JELATIX</span>
                <span class="text-[10px] tracking-wider uppercase px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-bold border border-sky-400/30">Registration</span>
            </a>
            <a href="/" class="text-xs sm:text-sm text-slate-400 hover:text-white transition flex items-center gap-1">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-3.5 sm:px-6 py-5 sm:py-10 pb-36 sm:pb-28 flex-1 w-full">
        <!-- Event Header Card -->
        <div class="bg-slate-900/80 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xl mb-6 sm:mb-8 relative overflow-hidden">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Pendaftaran Dibuka
                </span>
                <span class="text-xs text-slate-400 truncate">&bull; {{ $event->organizer?->name }}</span>
            </div>
            <h1 class="text-xl sm:text-3xl md:text-4xl font-extrabold text-white mb-2 leading-tight">{{ $event->title }}</h1>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-xs sm:text-sm text-slate-300">
                <span class="flex items-center gap-1.5 text-sky-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $event->event_start_date->format('d F Y, H:i') }} WIB
                </span>
                <span class="flex items-center gap-1.5 text-slate-300">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    {{ $event->race_location_name }}
                </span>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-rose-500/15 border-2 border-rose-500/40 text-rose-200 px-4 sm:px-5 py-3.5 sm:py-4 rounded-xl sm:rounded-2xl text-xs sm:text-sm mb-6 sm:mb-8">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Perhatian:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('public.register.checkout', $event->slug) }}" method="POST" id="regForm">
            @csrf

            <!-- 1. Data Pemesan (Penanggung Jawab / PIC) -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 mb-6 sm:mb-8 shadow-xl">
                <div class="flex items-center justify-between mb-1 gap-2">
                    <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center shrink-0">1</span>
                        <span>Data Pemesan (PIC)</span>
                    </h2>
                    <span class="text-[10px] sm:text-[11px] text-sky-400 bg-sky-500/10 px-2.5 py-0.5 rounded-full border border-sky-500/20 font-mono shrink-0">
                        PIC Utama
                    </span>
                </div>
                <p class="text-xs text-slate-400 mb-5 sm:mb-6">
                    Invoice pembayaran Tripay dan seluruh arsip E-Ticket akan dikirimkan ke kontak pemesan ini.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Lengkap Pemesan *</label>
                        <input 
                            type="text" 
                            name="buyer_name" 
                            id="buyerNameInput" 
                            value="{{ old('buyer_name') }}" 
                            required 
                            placeholder="Contoh: Budi Santoso"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 sm:px-4 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500 transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email Pemesan *</label>
                        <input 
                            type="email" 
                            name="buyer_email" 
                            id="buyerEmailInput" 
                            value="{{ old('buyer_email') }}" 
                            required 
                            placeholder="budi@example.com"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 sm:px-4 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500 transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nomor WhatsApp Pemesan *</label>
                        <input 
                            type="tel" 
                            name="buyer_phone" 
                            id="buyerPhoneInput" 
                            value="{{ old('buyer_phone') }}" 
                            required 
                            placeholder="0812xxxxxxxx"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 sm:px-4 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500 transition"
                        >
                    </div>
                </div>
            </section>

            <!-- 2. Formulir Data Pelari & Tiket (Multi-Runner Kolektif) -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 mb-6 sm:mb-8 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6 pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center shrink-0">2</span>
                            <span>Daftar Pelari & Nomor Dada (BIB)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Beli tiket untuk diri Anda sendiri atau daftarkan hingga 10 teman sekaligus dalam 1 kali pembayaran.
                        </p>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-2.5">
                        <span id="runnerCountBadge" class="text-xs font-mono font-bold text-sky-300 bg-sky-500/10 px-3 py-1.5 rounded-xl border border-sky-500/20">
                            1 Pelari Terdaftar
                        </span>
                        <button 
                            type="button" 
                            id="addRunnerBtn" 
                            onclick="addRunner()" 
                            class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 active:scale-95 text-slate-950 font-black text-xs px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 cursor-pointer shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ TAMBAH PELARI</span>
                        </button>
                    </div>
                </div>

                <!-- Container Kartu Pelari -->
                <div id="runnersContainer" class="space-y-5 sm:space-y-6">
                    <!-- Kartu pelari akan dirender secara dinamis oleh JavaScript -->
                </div>
            </section>


            <!-- 6. Metode Pembayaran Tripay Lengkap -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 mb-6 sm:mb-8 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center shrink-0">3</span>
                            <span>Metode Pembayaran (Tripay 26 Channel)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Seluruh channel resmi Tripay tersedia. Pilih channel yang paling nyaman untuk Anda.</p>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full w-fit">
                        {{ count($paymentChannels) }} Channel Aktif
                    </span>
                </div>

                @php
                    $groupedChannels = collect($paymentChannels)->groupBy('group');
                @endphp

                <!-- Filter Kategori & Pencarian Cepat -->
                <div class="space-y-3 mb-6">
                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            type="text" 
                            id="paymentChannelSearch" 
                            placeholder="Cari bank atau e-wallet (BCA, Mandiri, BRI, QRIS, Dana, Alfamart...)" 
                            class="w-full pl-10 pr-4 py-3 sm:py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-base sm:text-xs text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 transition"
                        >
                    </div>

                    <!-- Category Tabs (Horizontal Scrollable on Mobile) -->
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none no-scrollbar flex-nowrap -mx-4 px-4 sm:mx-0 sm:px-0" id="categoryTabContainer">
                        <button 
                            type="button" 
                            data-tab="all" 
                            class="cat-tab-btn active-tab px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-sky-500 text-slate-950 shadow-md shadow-sky-500/20 shrink-0 whitespace-nowrap"
                        >
                            Semua ({{ count($paymentChannels) }})
                        </button>
                        @foreach($groupedChannels as $groupName => $channels)
                            <button 
                                type="button" 
                                data-tab="{{ Str::slug($groupName) }}" 
                                class="cat-tab-btn px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700 border border-slate-700/60 transition cursor-pointer shrink-0 whitespace-nowrap"
                            >
                                {{ $groupName }} ({{ count($channels) }})
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Channel Cards Container -->
                <div class="space-y-6" id="channelsContainer">
                    @foreach($groupedChannels as $groupName => $channels)
                        <div class="channel-group-block" data-group-slug="{{ Str::slug($groupName) }}">
                            <div class="text-xs font-extrabold uppercase tracking-wider text-sky-400 mb-2.5 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                                {{ $groupName }}
                                <span class="text-[10px] text-slate-500 font-normal">({{ count($channels) }} pilihan)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                @foreach($channels as $ch)
                                    @php
                                        $initials = strtoupper(substr($ch['code'], 0, 3));
                                        $accentColor = $ch['color'] ?? '#0284c7';
                                    @endphp
                                    <label 
                                        class="payment-channel-card border-2 border-slate-800/90 hover:border-slate-600 rounded-xl p-3 sm:p-3.5 flex items-center justify-between cursor-pointer transition bg-slate-800/30 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-500/10 has-[:checked]:shadow-lg has-[:checked]:shadow-sky-500/10"
                                        data-name="{{ strtolower($ch['name']) }}"
                                        data-code="{{ strtolower($ch['code']) }}"
                                        data-channel-name="{{ $ch['name'] }}"
                                    >
                                        <div class="flex items-center gap-3 min-w-0">
                                            <input 
                                                type="radio" 
                                                name="payment_method" 
                                                value="{{ $ch['code'] }}" 
                                                required 
                                                {{ $loop->parent->first && $loop->first ? 'checked' : '' }}
                                                class="channel-radio text-sky-500 focus:ring-sky-500 w-4 h-4 shrink-0"
                                                data-channel-name="{{ $ch['name'] }}"
                                            >
                                            
                                            <!-- Channel Logo / Badge -->
                                            <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-700/80 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                                @if(!empty($ch['icon_url']))
                                                    <img 
                                                        src="{{ $ch['icon_url'] }}" 
                                                        alt="{{ $ch['name'] }}" 
                                                        class="w-full h-full object-contain"
                                                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                                                    >
                                                    <span class="hidden text-[10px] font-black font-mono text-sky-300" style="color: {{ $accentColor }}">{{ $initials }}</span>
                                                @else
                                                    <span class="text-[10px] font-black font-mono" style="color: {{ $accentColor }}">{{ $initials }}</span>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <span class="font-bold text-white text-xs sm:text-sm block truncate" title="{{ $ch['name'] }}">{{ $ch['name'] }}</span>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-900 text-sky-300 border border-slate-700">
                                                        {{ $ch['code'] }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 hidden xs:inline">Verifikasi Otomatis</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right pl-2">
                                            <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                                                Instan
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="noChannelMatch" class="hidden text-center py-8 text-slate-400 text-xs">
                    Tidak ada metode pembayaran yang cocok dengan kata kunci pencarian Anda.
                </div>
            </section>

            <!-- 7. Legal & Medical Waiver Agreement -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 mb-6 sm:mb-8 shadow-xl">
                <h2 class="text-sm sm:text-base font-bold text-white mb-3">Pernyataan Pelepasan Tanggung Jawab Hukum & Medis (Waiver)</h2>
                
                <div class="bg-slate-950 p-3.5 sm:p-4 rounded-xl border border-slate-800 text-xs text-slate-400 max-h-36 overflow-y-auto mb-4 leading-relaxed">
                    {{ $event->waiver_content ?: 'Dengan ini saya menyatakan bahwa saya mengikuti lomba lari ini atas kemauan sendiri dan dalam kondisi kesehatan yang prima. Saya membebaskan penyelenggara dari segala tuntutan hukum akibat cedera yang timbul selama perlombaan.' }}
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="waiver_accepted" value="1" required class="mt-1 rounded bg-slate-800 border-slate-700 text-sky-500 focus:ring-0">
                    <span class="text-xs text-slate-300 leading-normal">
                        Saya telah membaca, memahami, dan menyetujui seluruh ketentuan lomba, size chart jersey, serta pernyataan pelepasan tanggung jawab hukum di atas.
                    </span>
                </label>
            </section>

            <!-- Order Summary In-Page Box -->
            <div class="bg-slate-900 border-2 border-sky-500/50 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-6 mb-6">
                <div class="w-full sm:w-auto">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-xs text-slate-400 uppercase tracking-widest block">Total Pembayaran</span>
                        <span id="ticketCountSummary" class="text-xs font-mono font-bold text-sky-400 bg-sky-500/10 px-2.5 py-0.5 rounded-full border border-sky-500/30">
                            1 Tiket Pelari
                        </span>
                    </div>
                    <div id="grandTotalDisplay" class="text-3xl sm:text-4xl font-black text-sky-400 font-mono">
                        Rp 0
                    </div>
                    <div id="categoryBreakdownDisplay" class="text-xs text-slate-300 mt-1 flex flex-wrap gap-2">
                        <!-- Breakdown per kategori -->
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-[11px] text-slate-400">Metode Bayar:</span>
                        <span id="selectedMethodBadge" class="text-[11px] font-bold text-sky-300 bg-sky-500/10 px-2.5 py-0.5 rounded-full border border-sky-500/30 font-mono">
                            QRIS Dinamis (QRIS)
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-500 block mt-1">Sudah termasuk biaya layanan platform Rp 5.000 (flat per transaksi)</span>
                </div>

                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full sm:w-auto bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 active:scale-95 text-slate-950 font-black px-8 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base transition shadow-xl shadow-sky-500/25 flex items-center justify-center gap-2 cursor-pointer shrink-0"
                >
                    <span id="submitBtnText">LANJUT KE PEMBAYARAN TRIPAY (1 TIKET) &rarr;</span>
                </button>
            </div>
        </form>
    </main>

    <!-- Floating Sticky Bottom Bar for Mobile & Desktop -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-slate-950/95 backdrop-blur-xl border-t border-slate-800/90 py-3 px-4 sm:px-6 shadow-[0_-8px_30px_rgba(0,0,0,0.7)]">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-slate-400">
                    <span>Total Tagihan:</span>
                    <span id="stickyTicketCountBadge" class="font-mono font-bold text-sky-400 bg-sky-500/10 px-1.5 py-0.2 rounded border border-sky-500/20 text-[10px]">1 Tiket</span>
                </div>
                <div id="stickyGrandTotalDisplay" class="text-lg sm:text-2xl font-black text-sky-400 font-mono leading-tight tracking-tight truncate">
                    Rp 0
                </div>
            </div>
            <button 
                type="submit" 
                form="regForm"
                id="stickySubmitBtn"
                class="bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 active:scale-95 text-slate-950 font-black px-5 sm:px-8 py-3 rounded-xl text-xs sm:text-sm transition shadow-lg shadow-sky-500/25 flex items-center gap-1.5 cursor-pointer shrink-0"
            >
                <span id="stickySubmitBtnText">BAYAR SEKARANG &rarr;</span>
            </button>
        </div>
    </div>

    <!-- Size Chart Modal -->
    <div id="sizeChartModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-white/20 rounded-2xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white">Size Chart Jersey (Running Tee)</h3>
                <button type="button" onclick="document.getElementById('sizeChartModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <table class="w-full text-xs text-left border-collapse mb-4">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-300">
                        <th class="py-2">Ukuran</th>
                        <th class="py-2">Lebar Dada (cm)</th>
                        <th class="py-2">Panjang Badan (cm)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @foreach($event->jerseySizes as $j)
                        <tr>
                            <td class="py-2.5 font-bold font-mono text-sky-400">{{ $j->size_name }} ({{ ucfirst($j->gender_type) }})</td>
                            <td class="py-2.5">{{ $j->chest_width_cm ?: '50' }} cm</td>
                            <td class="py-2.5">{{ $j->body_length_cm ?: '70' }} cm</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-[11px] text-amber-300/80 bg-amber-500/10 p-3 rounded-lg mb-4">
                Toleransi ukuran jahitan +/- 1.5 cm. Pastikan mengukur sebelum memilih karena kaos tidak dapat ditukar saat RPC.
            </div>
            <button type="button" onclick="document.getElementById('sizeChartModal').classList.add('hidden')" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 rounded-xl text-xs">
                Tutup Panduan
            </button>
        </div>
    </div>

    <!-- Scripts: Multi-Runner Builder, Price Calculation, BIB Live Preview, and Tripay Channel Filtering -->
    <script>
        const categoriesData = @json($event->categories);
        const jerseySizesData = @json($event->jerseySizes);
        const customFieldsData = @json($event->customFields);
        const MAX_RUNNERS = 10;

        let runners = [];

        const runnersContainer = document.getElementById('runnersContainer');
        const runnerCountBadge = document.getElementById('runnerCountBadge');
        const ticketCountSummary = document.getElementById('ticketCountSummary');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');
        const categoryBreakdownDisplay = document.getElementById('categoryBreakdownDisplay');
        const submitBtnText = document.getElementById('submitBtnText');
        const selectedMethodBadge = document.getElementById('selectedMethodBadge');
        const buyerNameInput = document.getElementById('buyerNameInput');
        const buyerEmailInput = document.getElementById('buyerEmailInput');
        const buyerPhoneInput = document.getElementById('buyerPhoneInput');

        // Fungsi Render Kartu Pelari
        function createRunnerCardHtml(index) {
            const isFirst = (index === 0);
            const title = isFirst ? 'Pelari #1 (Pelari Utama / Pemesan)' : `Pelari #${index + 1} (Teman)`;
            
            // Opsi Kategori
            let categoryOptions = categoriesData.map(cat => {
                const formattedPrice = new Intl.NumberFormat('id-ID').format(cat.normal_price);
                return `<option value="${cat.id}" data-price="${cat.normal_price}" data-name="${cat.name}">${cat.name} (${cat.distance_km} KM) - Rp ${formattedPrice} [Sisa ${cat.quota - cat.slots_taken}]</option>`;
            }).join('');

            // Opsi Jersey
            let jerseyOptions = jerseySizesData.map(j => {
                return `<option value="${j.id}">${j.size_name} (${j.gender_type.toUpperCase()}) - Stok: ${j.stock - j.allocated_stock}</option>`;
            }).join('');

            // Opsi Custom Fields
            let customFieldsHtml = '';
            if (customFieldsData && customFieldsData.length > 0) {
                customFieldsHtml = `
                    <div class="mt-4 pt-4 border-t border-slate-800 space-y-3">
                        <span class="text-xs font-bold text-sky-400 block uppercase tracking-wider">Pertanyaan Khusus Panitia:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            ${customFieldsData.map(f => {
                                const req = f.is_required ? 'required' : '';
                                if (f.field_type === 'select') {
                                    const opts = (f.options || []).map(o => `<option value="${o}">${o}</option>`).join('');
                                    return `
                                        <div>
                                            <label class="block text-xs font-bold text-slate-300 mb-1.5">${f.label} ${f.is_required ? '<span class="text-rose-400">*</span>' : ''}</label>
                                            <select name="participants[${index}][custom_fields][${f.field_key}]" ${req} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-xs text-white outline-none focus:border-sky-500">
                                                <option value="">-- Pilih --</option>
                                                ${opts}
                                            </select>
                                        </div>
                                    `;
                                } else {
                                    return `
                                        <div>
                                            <label class="block text-xs font-bold text-slate-300 mb-1.5">${f.label} ${f.is_required ? '<span class="text-rose-400">*</span>' : ''}</label>
                                            <input type="text" name="participants[${index}][custom_fields][${f.field_key}]" placeholder="${f.placeholder || ''}" ${req} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500">
                                        </div>
                                    `;
                                }
                            }).join('')}
                        </div>
                    </div>
                `;
            }

            return `
                <div class="runner-card bg-slate-950/80 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl transition" id="runnerCard_${index}" data-index="${index}">
                    <!-- Header Kartu -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800/80 pb-4 mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shadow-sky-500/20 shrink-0">
                                ${index + 1}
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-white">${title}</h3>
                                <span class="text-[11px] text-slate-400">Pilih kategori, ukuran kaos, dan identitas lomba</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            ${isFirst ? `
                                <label class="flex items-center gap-2 text-xs text-sky-400 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 px-3 py-2 sm:py-1.5 rounded-xl cursor-pointer transition">
                                    <input type="checkbox" id="syncBuyerCheckbox" checked onchange="handleSyncBuyer()" class="rounded bg-slate-800 border-slate-700 text-sky-500 focus:ring-0">
                                    <span>Sama dengan Data Pemesan</span>
                                </label>
                            ` : `
                                <button type="button" onclick="copyEmergencyFromFirst(${index})" class="text-xs sm:text-[11px] font-semibold text-sky-400 hover:text-sky-300 bg-slate-800/80 hover:bg-slate-700 border border-slate-700 px-3 py-2 sm:py-1 rounded-xl transition cursor-pointer">
                                    📋 Samakan Kontak Darurat
                                </button>
                                <button type="button" onclick="removeRunner(${index})" class="text-xs sm:text-[11px] font-bold text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 px-3 py-2 sm:py-1 rounded-xl transition cursor-pointer">
                                    ✕ Hapus
                                </button>
                            `}
                        </div>
                    </div>

                    <!-- Pilihan Kategori & Jersey -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 bg-slate-900/60 p-3.5 sm:p-4 rounded-xl border border-slate-800">
                        <div>
                            <label class="block text-xs font-bold text-sky-400 mb-1.5 flex items-center justify-between">
                                <span>Pilih Kategori Lomba *</span>
                                <span class="text-[10px] text-slate-400">Jarak & Kuota</span>
                            </label>
                            <select name="participants[${index}][category_id]" required onchange="calculateTotal()" class="runner-category-select w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-xs text-white font-semibold outline-none focus:border-sky-500">
                                ${categoryOptions}
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-amber-400">Pilih Ukuran Jersey Lari *</label>
                                <button type="button" onclick="document.getElementById('sizeChartModal').classList.remove('hidden')" class="text-[11px] text-sky-400 hover:underline">
                                    Size Chart (cm)
                                </button>
                            </div>
                            <select name="participants[${index}][jersey_size_id]" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-xs text-white font-semibold outline-none focus:border-amber-400">
                                ${jerseyOptions}
                            </select>
                        </div>
                    </div>

                    <!-- Identitas & Nama BIB -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Nama Lengkap Sesuai KTP *</label>
                            <input 
                                type="text" 
                                name="participants[${index}][full_name]" 
                                id="fullName_${index}" 
                                required 
                                oninput="handleRunnerNameInput(${index})" 
                                placeholder="Contoh: Budi Pratama"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Nama di BIB Dada (Max 14 Karakter)</label>
                            <input 
                                type="text" 
                                name="participants[${index}][bib_name]" 
                                id="bibName_${index}" 
                                maxlength="14" 
                                placeholder="BUDI P (opsional)"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500 uppercase"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Jenis Identitas *</label>
                            <select name="participants[${index}][id_type]" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white outline-none focus:border-sky-500">
                                <option value="KTP">KTP (WNI)</option>
                                <option value="SIM">SIM</option>
                                <option value="Passport">Passport (WNA/Asing)</option>
                                <option value="KIA">KIA (Kartu Identitas Anak)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Nomor Identitas (NIK/Paspor) *</label>
                            <input 
                                type="text" 
                                name="participants[${index}][id_number]" 
                                required 
                                placeholder="16 digit NIK..."
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Jenis Kelamin *</label>
                            <select name="participants[${index}][gender]" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white outline-none focus:border-sky-500">
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Tanggal Lahir *</label>
                            <input 
                                type="date" 
                                name="participants[${index}][birth_date]" 
                                required 
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white outline-none focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Golongan Darah *</label>
                            <select name="participants[${index}][blood_type]" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white outline-none focus:border-sky-500">
                                <option value="O+">O+</option>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="AB+">AB+</option>
                                <option value="Unknown">Tidak Tahu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Target Waktu Finish (Pace)</label>
                            <input 
                                type="text" 
                                name="participants[${index}][estimated_finish_time]" 
                                placeholder="Contoh: 00:30:00 (30 menit)"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Nomor WhatsApp Pelari *</label>
                            <input 
                                type="tel" 
                                name="participants[${index}][phone]" 
                                id="phone_${index}" 
                                required 
                                placeholder="0812xxxxxxxx"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Email Pelari *</label>
                            <input 
                                type="email" 
                                name="participants[${index}][email]" 
                                id="email_${index}" 
                                required 
                                placeholder="pelari@example.com"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2.5 text-base sm:text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>
                    </div>

                    <!-- Kontak Darurat & Medis -->
                    <div class="border-t border-slate-800/80 pt-4">
                        <span class="text-xs font-bold text-slate-400 block mb-2 uppercase tracking-wider">Kontak Darurat Pelari Ini:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                            <div>
                                <label class="block text-xs sm:text-[11px] font-bold text-slate-300 mb-1.5">Nama Kontak Darurat *</label>
                                <input 
                                    type="text" 
                                    name="participants[${index}][emergency_contact_name]" 
                                    id="emName_${index}" 
                                    required 
                                    placeholder="Keluarga / Kerabat"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2 text-base sm:text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500"
                                >
                            </div>

                            <div>
                                <label class="block text-xs sm:text-[11px] font-bold text-slate-300 mb-1.5">No. Telp Darurat *</label>
                                <input 
                                    type="tel" 
                                    name="participants[${index}][emergency_contact_phone]" 
                                    id="emPhone_${index}" 
                                    required 
                                    placeholder="08xxxxxxxx"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2 text-base sm:text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500"
                                >
                            </div>

                            <div>
                                <label class="block text-xs sm:text-[11px] font-bold text-slate-300 mb-1.5">Hubungan *</label>
                                <input 
                                    type="text" 
                                    name="participants[${index}][emergency_contact_relation]" 
                                    id="emRel_${index}" 
                                    required 
                                    placeholder="Orang Tua / Teman / Pasangan"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2 text-base sm:text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-[11px] font-bold text-slate-300 mb-1.5">Riwayat Penyakit Khusus / Alergi Obat (Jika Ada)</label>
                            <input 
                                type="text" 
                                name="participants[${index}][medical_conditions]" 
                                placeholder="Contoh: Asma, alergi penisilin. Kosongkan jika sehat."
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-3 sm:py-2 text-base sm:text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500"
                            >
                        </div>
                    </div>

                    ${customFieldsHtml}
                </div>
            `;
        }

        // Tambah Pelari
        function addRunner() {
            if (runners.length >= MAX_RUNNERS) {
                alert(`Maksimal pendaftaran dalam 1 transaksi adalah ${MAX_RUNNERS} pelari.`);
                return;
            }

            const nextIndex = runners.length;
            runners.push(nextIndex);
            
            const cardWrapper = document.createElement('div');
            cardWrapper.innerHTML = createRunnerCardHtml(nextIndex);
            runnersContainer.appendChild(cardWrapper.firstElementChild);

            if (nextIndex === 0) {
                handleSyncBuyer();
            }

            updateRunnerState();
        }

        // Hapus Pelari
        function removeRunner(index) {
            const card = document.getElementById(`runnerCard_${index}`);
            if (card) {
                card.remove();
            }
            
            // Re-index runners
            reindexRunners();
            updateRunnerState();
        }

        function reindexRunners() {
            const cards = runnersContainer.querySelectorAll('.runner-card');
            runners = [];
            cards.forEach((card, idx) => {
                runners.push(idx);
                card.id = `runnerCard_${idx}`;
                card.setAttribute('data-index', idx);
                
                // Update title
                const titleEl = card.querySelector('h3');
                if (titleEl) {
                    titleEl.innerText = (idx === 0) ? 'Pelari #1 (Pelari Utama / Pemesan)' : `Pelari #${idx + 1} (Teman)`;
                }
            });
        }

        function updateRunnerState() {
            const count = runners.length;
            runnerCountBadge.innerText = `${count} Pelari Terdaftar`;
            ticketCountSummary.innerText = `${count} Tiket Pelari`;
            submitBtnText.innerHTML = `LANJUT KE PEMBAYARAN TRIPAY (${count} TIKET) &rarr;`;

            const stickyTicketCountBadge = document.getElementById('stickyTicketCountBadge');
            if (stickyTicketCountBadge) {
                stickyTicketCountBadge.innerText = `${count} TIKET`;
            }
            const stickySubmitBtnText = document.getElementById('stickySubmitBtnText');
            if (stickySubmitBtnText) {
                stickySubmitBtnText.innerHTML = `BAYAR (${count}) &rarr;`;
            }

            const addBtn = document.getElementById('addRunnerBtn');
            if (count >= MAX_RUNNERS) {
                addBtn.disabled = true;
                addBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                addBtn.disabled = false;
                addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            calculateTotal();
        }

        // Salin Kontak Darurat dari Pelari 1 ke Pelari Lain
        function copyEmergencyFromFirst(targetIndex) {
            const firstEmName = document.getElementById('emName_0');
            const firstEmPhone = document.getElementById('emPhone_0');
            const firstEmRel = document.getElementById('emRel_0');

            const targetName = document.getElementById(`emName_${targetIndex}`);
            const targetPhone = document.getElementById(`emPhone_${targetIndex}`);
            const targetRel = document.getElementById(`emRel_${targetIndex}`);

            if (firstEmName && targetName) targetName.value = firstEmName.value;
            if (firstEmPhone && targetPhone) targetPhone.value = firstEmPhone.value;
            if (firstEmRel && targetRel) targetRel.value = firstEmRel.value;
        }

        // Sinkronisasi data pemesan ke Pelari 1
        function handleSyncBuyer() {
            const syncCheckbox = document.getElementById('syncBuyerCheckbox');
            if (!syncCheckbox || !syncCheckbox.checked) return;

            const fnInput = document.getElementById('fullName_0');
            const phoneInput = document.getElementById('phone_0');
            const emailInput = document.getElementById('email_0');

            if (fnInput && buyerNameInput) fnInput.value = buyerNameInput.value;
            if (phoneInput && buyerPhoneInput) phoneInput.value = buyerPhoneInput.value;
            if (emailInput && buyerEmailInput) emailInput.value = buyerEmailInput.value;
        }

        if (buyerNameInput) buyerNameInput.addEventListener('input', handleSyncBuyer);
        if (buyerEmailInput) buyerEmailInput.addEventListener('input', handleSyncBuyer);
        if (buyerPhoneInput) buyerPhoneInput.addEventListener('input', handleSyncBuyer);

        function handleRunnerNameInput(index) {
            const fn = document.getElementById(`fullName_${index}`);
            const bn = document.getElementById(`bibName_${index}`);
            if (bn && !bn.value && fn) {
                // Jangan paksa ubah jika user sedang mengetik
            }
        }

        // Hitung Grand Total & Ringkasan Kategori
        function calculateTotal() {
            const categorySelects = document.querySelectorAll('.runner-category-select');
            let sumPrice = 0;
            const categoryBreakdown = {};

            categorySelects.forEach(sel => {
                const opt = sel.selectedOptions[0];
                if (opt) {
                    const price = parseFloat(opt.getAttribute('data-price')) || 0;
                    const name = opt.getAttribute('data-name') || 'Lari';
                    sumPrice += price;
                    categoryBreakdown[name] = (categoryBreakdown[name] || 0) + 1;
                }
            });

            const platformFee = 5000;
            const grandTotal = sumPrice + platformFee;
            const formattedTotal = 'Rp ' + grandTotal.toLocaleString('id-ID');
            grandTotalDisplay.innerText = formattedTotal;

            const stickyGrandTotalDisplay = document.getElementById('stickyGrandTotalDisplay');
            if (stickyGrandTotalDisplay) {
                stickyGrandTotalDisplay.innerText = formattedTotal;
            }

            // Render breakdown tags
            const breakdownHtml = Object.entries(categoryBreakdown).map(([name, qty]) => {
                return `<span class="bg-slate-800 text-sky-300 px-2 py-0.5 rounded border border-slate-700 font-mono">${qty}x ${name}</span>`;
            }).join(' ');

        }

        // Payment Method selection badge update
        function updateSelectedMethodBadge() {
            const checkedChannel = document.querySelector('input[name="payment_method"]:checked');
            if (checkedChannel) {
                const card = checkedChannel.closest('.payment-channel-card');
                const name = card ? card.getAttribute('data-channel-name') : checkedChannel.value;
                selectedMethodBadge.innerText = `${name} (${checkedChannel.value})`;
            }
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', updateSelectedMethodBadge);
        });

        // Filter Channel Tripay (Search & Tabs)
        const searchInput = document.getElementById('paymentChannelSearch');
        const tabBtns = document.querySelectorAll('.cat-tab-btn');
        const groupBlocks = document.querySelectorAll('.channel-group-block');
        const noMatchEl = document.getElementById('noChannelMatch');
        let currentActiveTab = 'all';

        function filterPaymentChannels() {
            const query = (searchInput.value || '').trim().toLowerCase();
            let totalVisible = 0;

            groupBlocks.forEach(group => {
                const groupSlug = group.getAttribute('data-group-slug');
                const isTabMatch = (currentActiveTab === 'all' || currentActiveTab === groupSlug);

                let visibleInGroup = 0;
                const cardsInGroup = group.querySelectorAll('.payment-channel-card');

                cardsInGroup.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    const code = card.getAttribute('data-code') || '';
                    const isSearchMatch = !query || name.includes(query) || code.includes(query);

                    if (isTabMatch && isSearchMatch) {
                        card.classList.remove('hidden');
                        visibleInGroup++;
                        totalVisible++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (visibleInGroup > 0) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            });

            if (totalVisible === 0) {
                noMatchEl.classList.remove('hidden');
            } else {
                noMatchEl.classList.add('hidden');
            }
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => {
                    b.classList.remove('bg-sky-500', 'text-slate-950', 'shadow-md', 'shadow-sky-500/20', 'font-bold');
                    b.classList.add('text-slate-300', 'bg-slate-800/80', 'font-semibold');
                });
                btn.classList.add('bg-sky-500', 'text-slate-950', 'shadow-md', 'shadow-sky-500/20', 'font-bold');
                btn.classList.remove('text-slate-300', 'bg-slate-800/80', 'font-semibold');

                currentActiveTab = btn.getAttribute('data-tab');
                filterPaymentChannels();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', filterPaymentChannels);
        }

        // Inisialisasi: Tambahkan Pelari #1 saat halaman pertama kali dibuka
        document.addEventListener('DOMContentLoaded', () => {
            addRunner();
            updateSelectedMethodBadge();

            const stickyBtn = document.getElementById('stickySubmitBtn');
            if (stickyBtn) {
                stickyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = document.getElementById('regForm');
                    if (form) {
                        if (typeof form.reportValidity === 'function') {
                            if (form.reportValidity()) {
                                form.submit();
                            }
                        } else {
                            form.submit();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
