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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10 flex-1 w-full">
        <!-- Event Header Card -->
        <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6 sm:p-8 shadow-2xl mb-8 relative overflow-hidden">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Pendaftaran Dibuka
                </span>
                <span class="text-xs text-slate-400">&bull; {{ $event->organizer?->name }}</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white mb-2">{{ $event->title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-xs sm:text-sm text-slate-300">
                <span class="flex items-center gap-1.5 text-sky-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $event->event_start_date->format('d F Y, H:i') }} WIB
                </span>
                <span class="flex items-center gap-1.5 text-slate-300">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    {{ $event->race_location_name }}
                </span>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-rose-500/15 border-2 border-rose-500/40 text-rose-200 px-5 py-4 rounded-xl text-sm mb-8">
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

            <!-- 1. Pilih Kategori Lari -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">1</span>
                    Pilih Kategori Lomba Lari
                </h2>
                <p class="text-xs text-slate-400 mb-6">Pilih jarak yang ingin Anda ikuti.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($event->categories as $cat)
                        <label class="relative flex flex-col justify-between border-2 border-slate-700/80 hover:border-sky-500/60 rounded-xl p-5 cursor-pointer transition bg-slate-800/40 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-500/10">
                            <input 
                                type="radio" 
                                name="category_id" 
                                value="{{ $cat->id }}" 
                                required 
                                {{ old('category_id') == $cat->id || $loop->first ? 'checked' : '' }} 
                                class="sr-only category-radio" 
                                data-price="{{ $cat->getCurrentPrice() }}"
                                data-name="{{ $cat->name }}"
                            >
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-extrabold text-white text-base">{{ $cat->name }}</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-mono">
                                        {{ $cat->distance_km }} KM
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 mb-3">
                                    Cut Off Time: {{ $cat->cut_off_time_minutes ? $cat->cut_off_time_minutes . ' Menit' : 'Tidak Ada' }}
                                </div>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
                                <span class="text-xs text-slate-400">Sisa Kuota: {{ $cat->available_slots }} slot</span>
                                <span class="text-lg font-black text-sky-400 font-mono">
                                    Rp {{ number_format($cat->getCurrentPrice(), 0, ',', '.') }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            <!-- 2. Pilih Ukuran Jersey -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">2</span>
                        Pilih Ukuran Jersey Lomba (Race Tee)
                    </h2>
                    <button type="button" onclick="document.getElementById('sizeChartModal').classList.remove('hidden')" class="text-xs text-sky-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lihat Size Chart (cm)
                    </button>
                </div>
                <p class="text-xs text-slate-400 mb-6">Ukuran bersifat final dan tidak dapat ditukar saat pengambilan race pack.</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($event->jerseySizes as $j)
                        <label class="border-2 border-slate-700/80 hover:border-amber-500/60 rounded-xl p-3 text-center cursor-pointer transition bg-slate-800/40 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-400/10">
                            <input 
                                type="radio" 
                                name="jersey_size_id" 
                                value="{{ $j->id }}" 
                                required 
                                {{ old('jersey_size_id') == $j->id || $loop->first ? 'checked' : '' }} 
                                class="sr-only"
                            >
                            <span class="text-xl font-black text-white block mb-0.5 font-mono">{{ $j->size_name }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ ucfirst($j->gender_type) }}</span>
                            <span class="text-[10px] text-slate-500 block mt-1">Sisa {{ $j->available_stock }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <!-- 3. Biodata Pelari & Mockup BIB -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">3</span>
                    Data Diri Pelari
                </h2>
                <p class="text-xs text-slate-400 mb-6">Data ini dicetak di nomor dada dan digunakan untuk verifikasi di hari lomba.</p>

                <!-- Live BIB Preview -->
                <div class="mb-6 p-4 rounded-xl bg-slate-950 border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-400">
                        <span class="font-bold text-white block text-sm">Preview Nomor Dada (BIB) Anda:</span>
                        Nama akan dicetak otomatis dalam huruf kapital.
                    </div>
                    <div class="bg-gradient-to-r from-sky-500 to-indigo-600 rounded-lg p-3 w-56 text-center text-white shadow-lg">
                        <div class="text-[10px] font-mono tracking-widest text-sky-100 uppercase">JELATIX RUNNER</div>
                        <div class="text-2xl font-black tracking-widest font-mono my-0.5">5K-XXXX</div>
                        <div id="bibNamePreview" class="text-xs font-bold uppercase truncate tracking-wider">NAMA ANDA</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Lengkap Sesuai KTP *</label>
                        <input 
                            type="text" 
                            name="full_name" 
                            id="fullNameInput" 
                            value="{{ old('full_name') }}" 
                            required 
                            placeholder="Contoh: Budi Pratama"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama di BIB (Max 14 Karakter)</label>
                        <input 
                            type="text" 
                            name="bib_name" 
                            id="bibNameInput" 
                            maxlength="14" 
                            value="{{ old('bib_name') }}" 
                            placeholder="Contoh: BUDI P (opsional)"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500 uppercase"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Jenis Identitas *</label>
                        <select name="id_type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-sky-500">
                            <option value="KTP" {{ old('id_type') == 'KTP' ? 'selected' : '' }}>KTP (WNI)</option>
                            <option value="SIM" {{ old('id_type') == 'SIM' ? 'selected' : '' }}>SIM</option>
                            <option value="Passport" {{ old('id_type') == 'Passport' ? 'selected' : '' }}>Passport (WNA/Asing)</option>
                            <option value="KIA" {{ old('id_type') == 'KIA' ? 'selected' : '' }}>KIA (Kartu Identitas Anak)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nomor Identitas (NIK/Paspor) *</label>
                        <input 
                            type="text" 
                            name="id_number" 
                            value="{{ old('id_number') }}" 
                            required 
                            placeholder="16 digit NIK KTP..."
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Jenis Kelamin *</label>
                        <select name="gender" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-sky-500">
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Tanggal Lahir *</label>
                        <input 
                            type="date" 
                            name="birth_date" 
                            value="{{ old('birth_date') }}" 
                            required 
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Golongan Darah *</label>
                        <select name="blood_type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-sky-500">
                            <option value="O+" {{ old('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="A+" {{ old('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="B+" {{ old('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="AB+" {{ old('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="Unknown" {{ old('blood_type') == 'Unknown' ? 'selected' : '' }}>Tidak Tahu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Estimasi Waktu Tempuh / Target Finish</label>
                        <input 
                            type="text" 
                            name="estimated_finish_time" 
                            value="{{ old('estimated_finish_time') }}" 
                            placeholder="Contoh: 00:30:00 (30 menit)"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nomor WhatsApp Aktif *</label>
                        <input 
                            type="tel" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            required 
                            placeholder="0812xxxxxxxx"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email Pengiriman Tiket *</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            placeholder="emailanda@gmail.com"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>
                </div>
            </section>

            <!-- 4. Kontak Darurat & Medis -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">4</span>
                    Kontak Darurat & Info Medis
                </h2>
                <p class="text-xs text-slate-400 mb-6">Wajib diisi demi keselamatan Anda saat berlari di rute lomba.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Kontak Darurat *</label>
                        <input 
                            type="text" 
                            name="emergency_contact_name" 
                            value="{{ old('emergency_contact_name') }}" 
                            required 
                            placeholder="Nama keluarga..."
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">No. Telp Darurat *</label>
                        <input 
                            type="tel" 
                            name="emergency_contact_phone" 
                            value="{{ old('emergency_contact_phone') }}" 
                            required 
                            placeholder="08xxxxxxxx"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Hubungan *</label>
                        <input 
                            type="text" 
                            name="emergency_contact_relation" 
                            value="{{ old('emergency_contact_relation') }}" 
                            required 
                            placeholder="Istri, Suami, Orang Tua"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Riwayat Penyakit Khusus / Alergi Obat (Jika Ada)</label>
                    <textarea 
                        name="medical_conditions" 
                        rows="2" 
                        placeholder="Contoh: Riwayat asma, alergi penisilin, dll. Kosongkan jika tidak ada."
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                    >{{ old('medical_conditions') }}</textarea>
                </div>
            </section>

            <!-- 5. Dynamic Custom Fields (jika ada) -->
            @if($event->customFields->count() > 0)
                <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                    <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">5</span>
                        Informasi Tambahan Event
                    </h2>
                    <p class="text-xs text-slate-400 mb-6">Pertanyaan khusus yang disediakan oleh pihak panitia lomba.</p>

                    <div class="space-y-4">
                        @foreach($event->customFields as $field)
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">
                                    {{ $field->label }} {!! $field->is_required ? '<span class="text-rose-400">*</span>' : '' !!}
                                </label>

                                @if($field->field_type === 'select')
                                    <select name="custom_fields[{{ $field->field_key }}]" {{ $field->is_required ? 'required' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-sky-500">
                                        <option value="">-- Pilih Opsi --</option>
                                        @foreach($field->options as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input 
                                        type="text" 
                                        name="custom_fields[{{ $field->field_key }}]" 
                                        {{ $field->is_required ? 'required' : '' }} 
                                        placeholder="{{ $field->placeholder ?: '' }}"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-sky-500"
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 6. Metode Pembayaran Tripay Lengkap -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 font-black text-xs flex items-center justify-center">6</span>
                    Metode Pembayaran (Tripay Multi-Channel)
                </h2>
                <p class="text-xs text-slate-400 mb-6">Pilih salah satu metode pembayaran otomatis yang Anda inginkan.</p>

                @php
                    $groupedChannels = collect($paymentChannels)->groupBy('group');
                @endphp

                <div class="space-y-6">
                    @foreach($groupedChannels as $groupName => $channels)
                        <div>
                            <div class="text-xs font-extrabold uppercase tracking-wider text-sky-400 mb-2.5 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                                {{ $groupName }}
                                <span class="text-[10px] text-slate-500 font-normal">({{ count($channels) }} pilihan)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($channels as $ch)
                                    <label class="border-2 border-slate-700/80 hover:border-sky-500 rounded-xl p-3 flex items-center justify-between cursor-pointer transition bg-slate-800/40 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-500/10">
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="radio" 
                                                name="payment_method" 
                                                value="{{ $ch['code'] }}" 
                                                required 
                                                {{ $loop->parent->first && $loop->first ? 'checked' : '' }}
                                                class="text-sky-500 focus:ring-sky-500"
                                            >
                                            <div>
                                                <span class="font-bold text-white text-xs sm:text-sm block">{{ $ch['name'] }}</span>
                                                <span class="text-[10px] text-slate-400">Kode: <strong class="text-sky-300 font-mono">{{ $ch['code'] }}</strong></span>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- 7. Legal & Medical Waiver Agreement -->
            <section class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
                <h2 class="text-base font-bold text-white mb-3">Pernyataan Pelepasan Tanggung Jawab Hukum & Medis (Waiver)</h2>
                
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs text-slate-400 max-h-36 overflow-y-auto mb-4 leading-relaxed">
                    {{ $event->waiver_content ?: 'Dengan ini saya menyatakan bahwa saya mengikuti lomba lari ini atas kemauan sendiri dan dalam kondisi kesehatan yang prima. Saya membebaskan penyelenggara dari segala tuntutan hukum akibat cedera yang timbul selama perlombaan.' }}
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="waiver_accepted" value="1" required class="mt-1 rounded bg-slate-800 border-slate-700 text-sky-500">
                    <span class="text-xs text-slate-300 leading-normal">
                        Saya telah membaca, memahami, dan menyetujui seluruh ketentuan lomba, size chart jersey, serta pernyataan pelepasan tanggung jawab hukum di atas.
                    </span>
                </label>
            </section>

            <!-- Order Summary & Checkout Submit -->
            <div class="bg-slate-900 border-2 border-sky-500/50 rounded-2xl p-6 sm:p-8 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <span class="text-xs text-slate-400 uppercase tracking-widest block">Total Pembayaran</span>
                    <div id="grandTotalDisplay" class="text-3xl font-black text-sky-400 font-mono">
                        Rp 0
                    </div>
                    <span class="text-[11px] text-slate-500">Sudah termasuk biaya layanan platform Rp 5.000</span>
                </div>

                <button 
                    type="submit" 
                    class="w-full sm:w-auto bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-slate-950 font-black px-10 py-4 rounded-xl text-base transition shadow-xl shadow-sky-500/25 flex items-center justify-center gap-2 cursor-pointer"
                >
                    LANJUT KE PEMBAYARAN TRIPAY &rarr;
                </button>
            </div>
        </form>
    </main>

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

    <!-- Live BIB Script & Total Calculator -->
    <script>
        const fullNameInput = document.getElementById('fullNameInput');
        const bibNameInput = document.getElementById('bibNameInput');
        const bibNamePreview = document.getElementById('bibNamePreview');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');

        function updateBibPreview() {
            const val = bibNameInput.value.trim() || fullNameInput.value.trim() || 'NAMA ANDA';
            bibNamePreview.innerText = val.toUpperCase();
        }

        fullNameInput.addEventListener('input', () => {
            if (!bibNameInput.value) updateBibPreview();
        });
        bibNameInput.addEventListener('input', updateBibPreview);

        function updateTotal() {
            const checkedCat = document.querySelector('input[name="category_id"]:checked');
            if (checkedCat) {
                const price = parseFloat(checkedCat.getAttribute('data-price')) || 0;
                const total = price + 5000;
                grandTotalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
            }
        }

        document.querySelectorAll('input[name="category_id"]').forEach(radio => {
            radio.addEventListener('change', updateTotal);
        });

        updateTotal();
    </script>
</body>
</html>
