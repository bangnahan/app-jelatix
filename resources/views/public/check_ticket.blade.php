<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran & Unduh E-Ticket - Jelatix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #1e1b4b 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="text-slate-100 font-sans antialiased flex flex-col justify-between">
    <!-- Navbar -->
    <header class="border-b border-white/10 backdrop-blur-md sticky top-0 z-50 bg-black/20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-sky-400 to-indigo-300 bg-clip-text text-transparent">JELATIX</span>
                <span class="text-xs uppercase px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-semibold border border-sky-400/30">Running Series</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="/admin" class="text-xs sm:text-sm text-slate-300 hover:text-white transition">Admin Panel</a>
                <a href="/organizer" class="text-xs sm:text-sm text-slate-300 hover:text-white transition">Organizer Portal</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-12 flex-1 w-full">
        <!-- Hero section -->
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-3">
                Cek Status Tiket & Unduh E-BIB
            </h1>
            <p class="text-slate-400 max-w-xl mx-auto text-sm sm:text-base">
                Masukkan Kode Order, Email, atau NIK KTP yang Anda gunakan saat mendaftar untuk melihat nomor dada (BIB) dan mengunduh E-Ticket.
            </p>
        </div>

        <!-- Error Alerts -->
        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 px-4 py-3 rounded-xl text-xs mb-6 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Search Box -->
        <div class="bg-white/5 border border-white/10 backdrop-blur-xl rounded-2xl p-4 sm:p-6 shadow-2xl mb-10">
            <form action="{{ route('public.ticket.search') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        name="query" 
                        value="{{ $search ?? '' }}" 
                        required 
                        placeholder="Contoh: JLTX-2026-0012, 357801..., atau budi@gmail.com"
                        class="w-full bg-slate-900/80 border border-slate-700/80 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30 rounded-xl px-4 py-3.5 text-white placeholder-slate-500 text-sm outline-none transition"
                    >
                </div>
                <button 
                    type="submit" 
                    class="bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-semibold px-6 py-3.5 rounded-xl text-sm transition shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Cari Tiket Saya
                </button>
            </form>
        </div>

        <!-- Search Results -->
        @if(isset($searched))
            @if(count($participants) > 0)
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-slate-200 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        Ditemukan {{ count($participants) }} Tiket Terdaftar & Lunas
                    </h2>

                    @foreach($participants as $p)
                        <div class="bg-slate-900/90 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden transition hover:border-sky-500/50">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                            LUNAS / TERDAFTAR
                                        </span>
                                        @if($p->is_vip)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                VVIP RUNNER
                                            </span>
                                        @endif
                                        <span class="text-xs text-slate-400">Order: {{ $p->order?->order_code }}</span>
                                    </div>
                                    <h3 class="text-xl font-bold text-white mb-1">{{ $p->event?->title }}</h3>
                                    <p class="text-sm text-slate-400 mb-3">
                                        Kategori: <strong class="text-sky-300">{{ $p->category?->name }} ({{ $p->category?->distance_km }} KM)</strong> 
                                        &bull; Tanggal: {{ $p->event?->event_start_date?->format('d M Y') }}
                                    </p>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs bg-white/5 p-3 rounded-xl border border-white/5">
                                        <div>
                                            <span class="text-slate-400 block">Nama Pelari:</span>
                                            <span class="font-semibold text-white">{{ $p->full_name }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block">Jersey:</span>
                                            <span class="font-semibold text-white">{{ $p->jerseySize ? $p->jerseySize->size_name . ' (' . ucfirst($p->jerseySize->gender_type) . ')' : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block">Status RPC:</span>
                                            <span class="font-semibold {{ $p->is_rpc_claimed ? 'text-emerald-400' : 'text-amber-400' }}">
                                                {{ $p->is_rpc_claimed ? 'Sudah Diambil' : 'Belum Diambil' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- BIB & Action -->
                                <div class="flex flex-col sm:flex-row md:flex-col items-center md:items-end justify-between gap-4 border-t md:border-t-0 md:border-l border-white/10 pt-4 md:pt-0 md:pl-6 min-w-[200px]">
                                    <div class="text-center md:text-right">
                                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest block">Nomor BIB</span>
                                        <span class="text-3xl font-extrabold text-sky-400 font-mono tracking-wider block">
                                            {{ $p->bib_number ?: 'PROSES' }}
                                        </span>
                                        <span class="text-xs text-slate-300 font-medium uppercase">{{ $p->bib_name ?: $p->full_name }}</span>
                                    </div>

                                    <a 
                                        href="{{ route('public.ticket.download', $p->qr_token) }}" 
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold px-4 py-2.5 rounded-xl text-xs transition shadow-md"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Unduh E-Ticket (PDF)
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-slate-900/60 border border-white/10 rounded-2xl p-10 text-center">
                    <div class="inline-flex p-4 rounded-full bg-rose-500/10 text-rose-400 mb-4 border border-rose-500/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Tiket Tidak Ditemukan</h3>
                    <p class="text-sm text-slate-400 max-w-md mx-auto mb-4">
                        Tidak ditemukan pendaftaran yang lunas dengan kata kunci "<strong>{{ $search }}</strong>". Pastikan pembayaran Anda di Tripay telah berhasil dan masukkan NIK/Email/Kode Order yang sesuai.
                    </p>
                </div>
            @endif
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Jelatix Ticketing. Platform Khusus Event Lari.
    </footer>
</body>
</html>
