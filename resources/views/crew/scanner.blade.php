<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RPC Scanner Venue - Jelatix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            color: #f8fafc;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        #reader video {
            border-radius: 1rem;
            object-fit: cover;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-slate-900 border-b border-slate-800 p-4 sticky top-0 z-40 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-extrabold tracking-tight text-white text-base">JELATIX RPC SCANNER</span>
        </div>
        <a href="/organizer" class="text-xs text-sky-400 hover:underline">Panel EO &rarr;</a>
    </header>

    <!-- Main Scanner Area -->
    <main class="max-w-md mx-auto w-full p-4 flex-1">
        <!-- Camera Box -->
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 shadow-xl mb-4">
            <div id="reader" class="overflow-hidden rounded-xl bg-black min-h-[260px] flex items-center justify-center text-slate-500 text-xs">
                <span>Memuat kamera scanner...</span>
            </div>

            <div class="mt-3 flex gap-2">
                <button id="toggleCameraBtn" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-semibold py-2 px-3 rounded-xl text-xs transition cursor-pointer">
                    Ganti Kamera / Mulai
                </button>
            </div>
        </div>

        <!-- Manual Input Form -->
        <div class="bg-slate-800/50 border border-slate-700/60 rounded-xl p-3 mb-4">
            <form id="manualForm" class="flex gap-2">
                <input 
                    type="text" 
                    id="manualInput" 
                    placeholder="Ketik Nomor BIB atau NIK..." 
                    class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 outline-none focus:border-sky-500"
                >
                <button type="submit" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 py-2 rounded-lg text-xs cursor-pointer">
                    Cek
                </button>
            </form>
        </div>

        <!-- Participant Detail Card (Hidden initially) -->
        <div id="resultCard" class="hidden bg-slate-900 border-2 border-sky-500/50 rounded-2xl p-5 shadow-2xl transition">
            <!-- Warning / Success Status Banner -->
            <div id="statusBanner" class="p-3 rounded-xl mb-4 text-center font-bold text-sm"></div>

            <div class="text-center mb-4">
                <div class="text-xs text-slate-400 uppercase tracking-widest">Nomor Dada (BIB)</div>
                <div id="resBibNumber" class="text-4xl font-extrabold text-sky-400 mono tracking-wider my-1"></div>
                <div id="resBibName" class="text-sm font-semibold uppercase text-slate-200"></div>
            </div>

            <!-- Jersey Size Highlight (Critical for Crew) -->
            <div class="bg-amber-500/10 border-2 border-amber-500/40 rounded-xl p-3 text-center mb-4">
                <span class="text-xs text-amber-300 font-bold uppercase tracking-wider block">Ukuran Jersey Wajib Diserahkan:</span>
                <span id="resJerseySize" class="text-2xl font-black text-amber-400 uppercase tracking-wide block mt-0.5"></span>
            </div>

            <div class="space-y-2 text-xs border-t border-slate-800 pt-3 mb-5">
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama Lengkap:</span>
                    <span id="resFullName" class="font-bold text-white"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kategori:</span>
                    <span id="resCategory" class="font-bold text-sky-300"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Golongan Darah:</span>
                    <span id="resBloodType" class="font-bold text-rose-400"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nomor Identitas:</span>
                    <span id="resIdNumber" class="font-mono text-slate-300"></span>
                </div>
            </div>

            <!-- Proxy Pickup Toggle -->
            <div id="proxySection" class="border-t border-slate-800 pt-3 mb-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300 mb-2">
                    <input type="checkbox" id="proxyToggle" class="rounded bg-slate-800 border-slate-700 text-sky-500">
                    <span>Pengambilan Diwakilkan (Surat Kuasa)</span>
                </label>
                <div id="proxyInputs" class="hidden space-y-2">
                    <input type="text" id="proxyName" placeholder="Nama Penerima Kuasa..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-white">
                    <input type="text" id="proxyNik" placeholder="NIK KTP Penerima Kuasa..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-white">
                </div>
            </div>

            <!-- Submit Claim Button -->
            <div class="space-y-2">
                <button 
                    id="claimBtn" 
                    class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold py-3.5 px-4 rounded-xl text-sm transition shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    SERAHKAN RACE PACK
                </button>
                <button 
                    id="scanNextBtn" 
                    class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-2.5 px-4 rounded-xl text-xs transition cursor-pointer"
                >
                    Tutup / Scan Peserta Berikutnya
                </button>
            </div>
        </div>
    </main>

    <footer class="p-3 text-center text-xs text-slate-500">
        Jelatix Race Pack Collection System &bull; Venue Mode
    </footer>

    <!-- Audio Beeps via Web Audio API -->
    <script>
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playBeep(freq, type, duration) {
            try {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = type;
                osc.frequency.value = freq;
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + duration);
                osc.stop(audioCtx.currentTime + duration);
            } catch (e) {}
        }
        function soundSuccess() { playBeep(880, 'sine', 0.2); }
        function soundError() { playBeep(220, 'square', 0.4); }

        let currentParticipant = null;
        let html5QrCode = null;

        document.addEventListener('DOMContentLoaded', () => {
            html5QrCode = new Html5Qrcode("reader");

            const qrConfig = { fps: 10, qrbox: { width: 220, height: 220 } };

            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    const cameraId = cameras[cameras.length - 1].id; // Kamera belakang
                    html5QrCode.start(cameraId, qrConfig, onScanSuccess);
                }
            }).catch(err => {
                console.log("Kamera tidak aktif:", err);
            });

            document.getElementById('proxyToggle').addEventListener('change', (e) => {
                document.getElementById('proxyInputs').classList.toggle('hidden', !e.target.checked);
            });

            document.getElementById('manualForm').addEventListener('submit', (e) => {
                e.preventDefault();
                const val = document.getElementById('manualInput').value.trim();
                if (val) verifyToken(val);
            });

            document.getElementById('scanNextBtn').addEventListener('click', () => {
                document.getElementById('resultCard').classList.add('hidden');
                document.getElementById('manualInput').value = '';
                currentParticipant = null;
            });

            document.getElementById('claimBtn').addEventListener('click', submitClaim);
        });

        function onScanSuccess(decodedText) {
            soundSuccess();
            verifyToken(decodedText);
        }

        async function verifyToken(token) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/crew/verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ token: token })
                });

                const data = await res.json();
                if (!res.ok) {
                    soundError();
                    alert(data.message || 'Token tidak valid');
                    return;
                }

                currentParticipant = data.participant;
                renderParticipant(currentParticipant);
            } catch (err) {
                soundError();
                alert('Terjadi kesalahan koneksi.');
            }
        }

        function renderParticipant(p) {
            document.getElementById('resBibNumber').innerText = p.bib_number;
            document.getElementById('resBibName').innerText = p.bib_name;
            document.getElementById('resJerseySize').innerText = p.jersey_size;
            document.getElementById('resFullName').innerText = p.full_name;
            document.getElementById('resCategory').innerText = `${p.category_name} (${p.distance_km} KM)`;
            document.getElementById('resBloodType').innerText = p.blood_type;
            document.getElementById('resIdNumber').innerText = p.id_number;

            const banner = document.getElementById('statusBanner');
            const claimBtn = document.getElementById('claimBtn');
            const proxySection = document.getElementById('proxySection');

            if (p.is_rpc_claimed) {
                banner.className = 'p-3 rounded-xl mb-4 text-center font-bold text-sm bg-rose-500/20 text-rose-300 border border-rose-500/40';
                banner.innerText = `⚠️ SUDAH DIAMBIL pada ${p.rpc_claimed_at}`;
                claimBtn.classList.add('hidden');
                proxySection.classList.add('hidden');
            } else {
                banner.className = 'p-3 rounded-xl mb-4 text-center font-bold text-sm bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
                banner.innerText = `SIAP DIAMBIL (BELUM PERNAH CLAIM)`;
                claimBtn.classList.remove('hidden');
                proxySection.classList.remove('hidden');
            }

            document.getElementById('resultCard').classList.remove('hidden');
            document.getElementById('resultCard').scrollIntoView({ behavior: 'smooth' });
        }

        async function submitClaim() {
            if (!currentParticipant) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const isProxy = document.getElementById('proxyToggle').checked;
            const proxyName = document.getElementById('proxyName').value;
            const proxyNik = document.getElementById('proxyNik').value;

            try {
                const res = await fetch('/crew/claim', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        participant_id: currentParticipant.id,
                        is_proxy: isProxy,
                        proxy_name: proxyName,
                        proxy_nik: proxyNik
                    })
                });

                const data = await res.json();
                if (!res.ok) {
                    soundError();
                    alert(data.message || 'Gagal menyerahkan race pack');
                    return;
                }

                soundSuccess();
                alert(data.message);
                currentParticipant.is_rpc_claimed = true;
                currentParticipant.rpc_claimed_at = data.claimed_at;
                renderParticipant(currentParticipant);
            } catch (err) {
                soundError();
                alert('Gagal memproses penyerahan.');
            }
        }
    </script>
</body>
</html>
