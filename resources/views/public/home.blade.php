<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelatix — Platform Tiketing Khusus Event Lari & Marathon</title>
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
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Navbar -->
    <header class="border-b border-white/10 backdrop-blur-xl sticky top-0 z-50 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-18 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="/" class="flex items-center gap-1.5 sm:gap-2">
                    <span class="text-xl sm:text-2xl font-black tracking-tight bg-gradient-to-r from-sky-400 via-teal-300 to-indigo-300 bg-clip-text text-transparent">
                        JELATIX
                    </span>
                    <span class="hidden xs:inline-block text-[9px] sm:text-[10px] tracking-wider uppercase px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-bold border border-sky-400/30">
                        Running Platform
                    </span>
                </a>
            </div>
            
            <nav class="flex items-center gap-2 sm:gap-5 text-xs sm:text-sm">
                <a href="/cek-tiket" class="font-semibold text-slate-300 hover:text-white transition flex items-center gap-1 sm:gap-1.5 px-2 py-1.5 rounded-lg hover:bg-white/5">
                    <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    <span class="hidden sm:inline">Cek Tiket & E-BIB</span>
                    <span class="sm:hidden">Tiket</span>
                </a>
                <a href="/organizer" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs sm:text-sm px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-xl transition">
                    <span class="hidden sm:inline">Portal EO</span>
                    <span class="sm:hidden">EO</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-12 pb-10 sm:pb-16 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-400/20 text-sky-400 text-[11px] sm:text-xs font-semibold mb-6 max-w-full text-left sm:text-center">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate sm:overflow-visible">Platform Tiketing & Registrasi Khusus Lomba Lari</span>
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white mb-4 sm:mb-6 leading-tight max-w-4xl mx-auto">
                Registrasi Lomba Lari Cepat, Terintegrasi, & Bebas Masalah.
            </h1>

            <p class="text-sm sm:text-lg text-slate-400 max-w-2xl mx-auto mb-8 sm:mb-10 leading-relaxed px-2">
                Kelola nomor BIB otomatis, kuota ukuran jersey, surat kuasa Race Pack Collection, dan pembayaran instan via Tripay dalam satu sistem terpadu.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto w-full">
                <a href="#events" class="w-full sm:w-auto bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 active:scale-95 text-slate-950 font-black px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl text-sm transition shadow-xl shadow-sky-500/20 flex items-center justify-center gap-2">
                    Jelajahi Event Lari Aktif
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </a>
                <a href="/cek-tiket" class="w-full sm:w-auto bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 active:scale-95 text-slate-200 font-semibold px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl text-sm transition flex items-center justify-center gap-2">
                    Cek Tiket & Download E-BIB
                </a>
            </div>
        </section>

        <!-- Active Events Section -->
        <section id="events" class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-12 border-t border-white/10">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <div>
                    <h2 class="text-xl sm:text-3xl font-extrabold text-white">Event Lari Mendatang</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Pilih kategori lomba favorit Anda dan amankan slot sebelum kuota habis.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @forelse($events as $event)
                    <div class="bg-slate-900/80 border border-white/10 rounded-2xl overflow-hidden hover:border-sky-500/50 transition duration-300 flex flex-col justify-between shadow-2xl">
                        <div>
                            <!-- Card Cover Image Segment -->
                            <div class="h-44 sm:h-48 relative overflow-hidden bg-slate-950 group">
                                @if($event->banner_path)
                                    <img 
                                        src="{{ asset('storage/' . $event->banner_path) }}" 
                                        alt="{{ $event->title }}" 
                                        class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                                    >
                                @else
                                    <img 
                                        src="https://images.unsplash.com/photo-1530549387789-4c1017266635?w=700&auto=format&fit=crop&q=80" 
                                        alt="{{ $event->title }}" 
                                        class="w-full h-full object-cover object-center opacity-75 group-hover:scale-105 transition duration-500"
                                    >
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent pointer-events-none"></div>

                                <div class="absolute top-3 left-3 right-3 flex justify-between items-start pointer-events-none">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-emerald-500/90 text-white backdrop-blur-md shadow-md">
                                        Registrasi Dibuka
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] sm:text-[11px] font-mono font-semibold bg-slate-950/80 text-sky-300 backdrop-blur-md border border-white/10 shadow-md">
                                        {{ $event->event_start_date->format('d M Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                                <div>
                                    <span class="text-[11px] text-sky-400 font-semibold block uppercase tracking-wider">{{ $event->organizer?->name }}</span>
                                    <h3 class="text-lg sm:text-xl font-bold text-white line-clamp-1 mt-0.5">{{ $event->title }}</h3>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-slate-300">
                                    <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate">{{ $event->race_location_name }}</span>
                                </div>

                                <p class="text-xs text-slate-400 line-clamp-2">
                                    {{ $event->short_description }}
                                </p>

                                <!-- Categories List -->
                                <div class="space-y-2 pt-2 border-t border-slate-800">
                                    <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider block">Kategori Lari Tersedia:</span>
                                    <div class="flex flex-wrap gap-1.5 sm:gap-2">
                                        @foreach($event->categories as $cat)
                                            <div class="bg-white/5 border border-white/10 rounded-lg px-2.5 py-1.5 text-xs flex items-center justify-between gap-3 w-full">
                                                <span class="font-bold text-white">{{ $cat->name }} ({{ $cat->distance_km }}K)</span>
                                                <span class="text-sky-400 font-bold font-mono">Rp {{ number_format($cat->getCurrentPrice(), 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6 pt-0">
                            <a href="{{ route('public.register.show', $event->slug) }}" class="w-full bg-gradient-to-r from-sky-400 to-sky-500 hover:from-sky-300 hover:to-sky-400 active:scale-95 text-slate-950 font-black py-3 sm:py-3.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-sky-500/25">
                                <span>Daftar Lomba Lari Sekarang &rarr;</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white/5 rounded-2xl border border-white/10">
                        <p class="text-slate-400 text-sm">Belum ada event lari aktif saat ini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Feature Pillars -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-16 border-t border-white/10">
            <div class="text-center mb-8 sm:mb-12">
                <h2 class="text-xl sm:text-3xl font-extrabold text-white">Dirancang Spesifik untuk Ekosistem Lari</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5 sm:mt-2">Bukan sekadar tiket umum, Jelatix memahami detail teknis perlombaan lari.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-8">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 sm:p-6">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold mb-3 sm:mb-4 border border-sky-400/30">
                        BIB
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-white mb-1.5 sm:mb-2">Penomoran BIB & VVIP Cantik</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Generator nomor dada instan dengan proteksi khusus VVIP, atlet elite, dan pejabat agar nomor tidak tergeser saat proses sortir massal.
                    </p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 sm:p-6">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold mb-3 sm:mb-4 border border-emerald-400/30">
                        PDF
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-white mb-1.5 sm:mb-2">E-Ticket & QR Code Instan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pengiriman tiket otomatis via email dengan barcode QR terenkripsi dan informasi detail race kit yang siap diunduh kapan saja oleh pelari.
                    </p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 sm:p-6">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold mb-3 sm:mb-4 border border-purple-400/30">
                        GATE
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-white mb-1.5 sm:mb-2">Tripay QRIS & Mailketing Queue</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pembayaran otomatis lunas via QRIS dan Virtual Account dengan sistem penahanan kuota 30 menit untuk mitigasi *war tiket*.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-6 sm:py-8 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 text-center sm:text-left">
            <div>
                &copy; {{ date('Y') }} <strong>Jelatix Ticketing System</strong>. Platform Khusus Event Lari di Indonesia.
            </div>
            <div class="flex flex-wrap gap-4 sm:gap-6 justify-center sm:justify-end">
                <a href="/cek-tiket" class="hover:text-slate-300">Cek Tiket</a>
                <a href="/organizer" class="hover:text-slate-300">Login EO</a>
                <a href="/admin" class="hover:text-slate-300">Superadmin</a>
            </div>
        </div>
    </footer>
</body>
</html>
