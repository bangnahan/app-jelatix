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
    <header class="border-b border-white/10 backdrop-blur-xl sticky top-0 z-50 bg-slate-950/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-tight bg-gradient-to-r from-sky-400 via-teal-300 to-indigo-300 bg-clip-text text-transparent">
                        JELATIX
                    </span>
                    <span class="text-[10px] tracking-wider uppercase px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-bold border border-sky-400/30">
                        Running Platform
                    </span>
                </a>
            </div>
            
            <nav class="flex items-center gap-3 sm:gap-6">
                <a href="/cek-tiket" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    Cek Tiket & E-BIB
                </a>
                <a href="/crew" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Scanner RPC
                </a>
                <a href="/organizer" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs sm:text-sm px-4 py-2 rounded-xl transition">
                    Portal EO
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-400/20 text-sky-400 text-xs font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Platform Tiketing & Registrasi Khusus Komunitas & Lomba Lari
            </div>

            <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white mb-6 leading-tight max-w-4xl mx-auto">
                Registrasi Lomba Lari Cepat, Terintegrasi, & Bebas Masalah.
            </h1>

            <p class="text-base sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Kelola nomor BIB otomatis, kuota ukuran jersey, surat kuasa Race Pack Collection, dan pembayaran instan via Tripay dalam satu sistem terpadu.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#events" class="w-full sm:w-auto bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold px-8 py-4 rounded-xl text-sm transition shadow-xl shadow-sky-500/20 flex items-center justify-center gap-2">
                    Jelajahi Event Lari Aktif
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </a>
                <a href="/cek-tiket" class="w-full sm:w-auto bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-200 font-semibold px-8 py-4 rounded-xl text-sm transition flex items-center justify-center gap-2">
                    Cek Tiket & Download E-BIB
                </a>
            </div>
        </section>

        <!-- Active Events Section -->
        <section id="events" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 border-t border-white/10">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Event Lari Mendatang</h2>
                    <p class="text-sm text-slate-400">Pilih kategori lomba favorit Anda dan amankan slot sebelum kuota habis.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($events as $event)
                    <div class="bg-slate-900/80 border border-white/10 rounded-2xl overflow-hidden hover:border-sky-500/50 transition duration-300 flex flex-col justify-between shadow-2xl">
                        <div>
                            <!-- Banner Placeholder with gradient -->
                            <div class="h-48 bg-gradient-to-tr from-sky-900 via-indigo-950 to-slate-900 relative p-6 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Registrasi Dibuka
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono bg-black/40 text-slate-300 backdrop-blur-md">
                                        {{ $event->event_start_date->format('d M Y') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-sky-400 font-semibold block uppercase tracking-wider">{{ $event->organizer?->name }}</span>
                                    <h3 class="text-xl font-bold text-white line-clamp-1">{{ $event->title }}</h3>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
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
                                    <div class="flex flex-wrap gap-2">
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

                        <div class="p-6 pt-0">
                            <a href="/cek-tiket" class="w-full bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-sky-500/20">
                                <span>Cek Pendaftaran / Status BIB</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
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
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16 border-t border-white/10">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Dirancang Spesifik untuk Ekosistem Lari</h2>
                <p class="text-sm text-slate-400 mt-2">Bukan sekadar tiket umum, Jelatix memahami detail teknis perlombaan lari.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                    <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold mb-4 border border-sky-400/30">
                        BIB
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Penomoran BIB & VVIP Cantik</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Generator nomor dada instan dengan proteksi khusus VVIP, atlet elite, dan pejabat agar nomor tidak tergeser saat proses sortir massal.
                    </p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold mb-4 border border-emerald-400/30">
                        RPC
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Scanner Race Pack & Surat Kuasa</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Aplikasi scanner ponsel untuk kru lapangan. Menampilkan ukuran jersey dalam ukuran besar dan mendukung penyerahan kolektif via surat kuasa.
                    </p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold mb-4 border border-purple-400/30">
                        GATE
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Tripay QRIS & Mailketing Queue</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pembayaran otomatis lunas via QRIS dan Virtual Account dengan sistem penahanan kuota 30 menit untuk mitigasi *war tiket*.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }} <strong>Jelatix Ticketing System</strong>. Platform Khusus Event Lari di Indonesia.
            </div>
            <div class="flex gap-6">
                <a href="/cek-tiket" class="hover:text-slate-300">Cek Tiket</a>
                <a href="/crew" class="hover:text-slate-300">Scanner Kru</a>
                <a href="/organizer" class="hover:text-slate-300">Login EO</a>
                <a href="/admin" class="hover:text-slate-300">Superadmin</a>
            </div>
        </div>
    </footer>
</body>
</html>
