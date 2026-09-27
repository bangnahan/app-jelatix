<x-filament-panels::page>
    <div class="space-y-6" x-data="rpcScannerHandler(@js($this->selectedEventId))">
        <!-- Top Bar: Event Selector & Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Event Picker -->
            <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="p-2.5 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900">
                        <x-heroicon-o-flag class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Event Aktif Venue</div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Pilih Lomba untuk RPC</div>
                    </div>
                </div>

                <div class="w-full sm:w-80">
                    <select 
                        wire:model.live="selectedEventId" 
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-sky-500 focus:border-sky-500 shadow-sm"
                    >
                        @foreach($this->getEvents() as $event)
                            <option value="{{ $event->id }}">{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Stats 1: Total Peserta Lunas -->
            @php $stats = $this->stats; @endphp
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900">
                    <x-heroicon-o-users class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Peserta Lunas</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</div>
                </div>
            </div>

            <!-- Stats 2: Sudah Ambil RPC -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900">
                    <x-heroicon-o-check-badge class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Sudah Ambil (Claimed)</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['claimed']) }}</div>
                </div>
            </div>

            <!-- Stats 3: Belum Ambil -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900">
                    <x-heroicon-o-clock class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Belum Ambil (Sisa)</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['unclaimed']) }}</div>
                </div>
            </div>

            <!-- Stats 4: Persentase Pengambilan -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900">
                    <x-heroicon-o-chart-pie class="w-6 h-6" />
                </div>
                <div class="w-full">
                    <div class="flex justify-between items-center text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        <span>Progres RPC</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $stats['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full transition-all duration-500" style="width: {{ $stats['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Scanner Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Side: Camera Scanner & Manual Input (5 Columns) -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Camera Box -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 shadow-xl text-white">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-800 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-300">Kamera Scanner QR</span>
                        </div>
                        <span id="cameraStatusText" class="text-[11px] text-gray-400">Inisialisasi...</span>
                    </div>

                    <!-- Video Viewport (wire:ignore to prevent re-render flicker) -->
                    <div wire:ignore class="relative rounded-xl overflow-hidden bg-black aspect-square flex items-center justify-center border border-gray-800">
                        <div id="reader" class="w-full h-full"></div>
                        <div id="scannerOverlay" class="absolute inset-0 pointer-events-none border-2 border-sky-500/40 rounded-xl flex items-center justify-center">
                            <div class="w-48 h-48 border-2 border-dashed border-sky-400 rounded-lg opacity-70 animate-pulse"></div>
                        </div>
                    </div>

                    <!-- Camera Controls -->
                    <div class="mt-3 flex gap-2">
                        <button 
                            type="button" 
                            id="toggleCameraBtn" 
                            @click="switchCamera()" 
                            class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-medium py-2.5 px-3 rounded-xl text-xs transition border border-gray-700 flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <x-heroicon-o-arrow-path class="w-4 h-4 text-sky-400" />
                            <span>Ganti Kamera Depan/Belakang</span>
                        </button>
                    </div>
                </div>

                <!-- Manual Input Card -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Input Manual (Nomor BIB / NIK / Token)
                    </label>
                    <form wire:submit.prevent="verifyToken(manualToken)" class="flex gap-2">
                        <input 
                            type="text" 
                            wire:model="manualToken" 
                            placeholder="Contoh: 10K1001 atau 3271..." 
                            class="flex-1 text-sm rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-sky-500 focus:border-sky-500 shadow-sm"
                        >
                        <button 
                            type="submit" 
                            class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            <span>Cari</span>
                        </button>
                    </form>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2">
                        Gunakan input manual jika barcode tiket pelari rusak atau kamera tidak dapat membaca.
                    </p>
                </div>
            </div>

            <!-- Right Side: Participant Details & Action (7 Columns) -->
            <div class="lg:col-span-7">
                @if($participantData)
                    <div class="bg-white dark:bg-gray-900 border-2 {{ $participantData['is_rpc_claimed'] ? 'border-rose-500/60' : 'border-emerald-500/60' }} rounded-2xl p-6 shadow-xl space-y-5">
                        
                        <!-- Status Banner -->
                        @if($participantData['is_rpc_claimed'])
                            <div class="p-4 rounded-xl bg-rose-500/10 border-2 border-rose-500/40 text-rose-700 dark:text-rose-300 flex items-start gap-3">
                                <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-rose-500 shrink-0 mt-0.5" />
                                <div>
                                    <div class="font-extrabold text-base">PERINGATAN: RACE PACK SUDAH PERNAH DIAMBIL!</div>
                                    <div class="text-xs mt-1 text-rose-600 dark:text-rose-400">
                                        Diserahkan pada <strong>{{ $participantData['rpc_claimed_at'] }}</strong> 
                                        @if($participantData['rpc_claimed_by'])
                                            oleh kru <strong>{{ $participantData['rpc_claimed_by'] }}</strong>.
                                        @endif
                                    </div>
                                    @if($participantData['is_proxy_claimed'])
                                        <div class="text-xs mt-1 font-semibold text-rose-700 dark:text-rose-300">
                                            Pengambilan via Surat Kuasa: {{ $participantData['proxy_collector_name'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-emerald-500/10 border-2 border-emerald-500/40 text-emerald-800 dark:text-emerald-200 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <x-heroicon-s-check-circle class="w-6 h-6 text-emerald-500 shrink-0" />
                                    <div>
                                        <div class="font-extrabold text-base">SIAP DIAMBIL (BELUM PERNAH CLAIM)</div>
                                        <div class="text-xs text-emerald-700 dark:text-emerald-300">Tiket valid dan terverifikasi untuk diserahkan.</div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500 text-white">
                                    VERIFIED
                                </span>
                            </div>
                        @endif

                        @if($warningMessage)
                            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-semibold flex items-center gap-2">
                                <x-heroicon-o-information-circle class="w-5 h-5 text-amber-500 shrink-0" />
                                <span>{{ $warningMessage }}</span>
                            </div>
                        @endif

                        <!-- BIB & Runner Header -->
                        <div class="text-center py-2 border-b border-gray-200 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">NOMOR DADA (BIB)</div>
                            <div class="text-5xl font-black text-sky-600 dark:text-sky-400 font-mono tracking-wider my-1">
                                {{ $participantData['bib_number'] }}
                            </div>
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide">
                                {{ $participantData['bib_name'] }}
                            </div>
                        </div>

                        <!-- JERSEY SIZE HIGHLIGHT (VERY CRITICAL FOR CREW) -->
                        <div class="p-4 rounded-xl bg-amber-500/10 border-2 border-amber-500/40 text-center shadow-inner">
                            <span class="text-xs font-extrabold text-amber-700 dark:text-amber-300 uppercase tracking-widest block">
                                UKURAN JERSEY WAJIB DISERAHKAN:
                            </span>
                            <span class="text-3xl sm:text-4xl font-black text-amber-500 uppercase tracking-wider block mt-1">
                                {{ $participantData['jersey_size'] }}
                            </span>
                        </div>

                        <!-- Runner Details Grid -->
                        <div class="grid grid-cols-2 gap-3 text-xs bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700/60">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Nama Lengkap</span>
                                <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $participantData['full_name'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Kategori Lomba</span>
                                <span class="font-bold text-sky-600 dark:text-sky-400 text-sm">
                                    {{ $participantData['category_name'] }} ({{ $participantData['distance_km'] }} KM)
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Nomor Identitas (NIK/Paspor)</span>
                                <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $participantData['id_number'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Golongan Darah</span>
                                <span class="font-bold text-rose-500">{{ $participantData['blood_type'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Jenis Kelamin</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $participantData['gender'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block font-medium">Kontak Darurat</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $participantData['emergency_contact'] }}</span>
                            </div>
                        </div>

                        <!-- Proxy Pickup Form (Surat Kuasa) -->
                        @if(!$participantData['is_rpc_claimed'])
                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-800 dark:text-gray-200 select-none">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="isProxy" 
                                        class="rounded border-gray-300 dark:border-gray-700 text-sky-600 shadow-sm focus:ring-sky-500"
                                    >
                                    <span>Pengambilan Diwakilkan (Surat Kuasa)</span>
                                </label>

                                @if($isProxy)
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">Nama Penerima Kuasa</label>
                                            <input 
                                                type="text" 
                                                wire:model="proxyName" 
                                                placeholder="Nama lengkap di KTP..." 
                                                class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-sky-500 focus:border-sky-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">NIK KTP Penerima Kuasa</label>
                                            <input 
                                                type="text" 
                                                wire:model="proxyNik" 
                                                placeholder="16 digit NIK KTP..." 
                                                class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-sky-500 focus:border-sky-500 font-mono"
                                            >
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="space-y-2 pt-2">
                            @if(!$participantData['is_rpc_claimed'])
                                <button 
                                    type="button" 
                                    wire:click="claimRacePack" 
                                    wire:loading.attr="disabled"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black py-4 px-6 rounded-xl text-sm transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider"
                                >
                                    <span wire:loading.remove wire:target="claimRacePack" class="flex items-center gap-2">
                                        <x-heroicon-s-check-circle class="w-6 h-6" />
                                        SERAHKAN RACE PACK SEKARANG
                                    </span>
                                    <span wire:loading wire:target="claimRacePack" class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Memproses Penyerahan...
                                    </span>
                                </button>
                            @endif

                            <button 
                                type="button" 
                                wire:click="resetParticipant" 
                                class="w-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 px-4 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                <span>Tutup / Scan Peserta Berikutnya</span>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Empty State: Waiting for Scan -->
                    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[460px]">
                        <div class="w-16 h-16 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center mb-4 border border-sky-100 dark:border-sky-900">
                            <x-heroicon-o-qr-code class="w-8 h-8" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
                            Arahkan Kamera ke QR Code E-Ticket
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mb-6">
                            Kamera akan otomatis memindai QR code pelari dan menampilkan nomor BIB serta ukuran jersey yang harus diserahkan.
                        </p>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-[11px] text-gray-600 dark:text-gray-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Scanner Siap Menerima Scan QR
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Claims Activity Log -->
        @if(count($recentClaims) > 0)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-sky-500" />
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Pengambilan Terakhir (Sesi Ini)</h4>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Total 6 penyerahan terkini</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($recentClaims as $claim)
                        <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-sky-600 dark:text-sky-400 text-sm">{{ $claim['bib_number'] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                        {{ $claim['jersey'] }}
                                    </span>
                                </div>
                                <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1 truncate max-w-[150px]">
                                    {{ $claim['full_name'] }}
                                </div>
                                @if($claim['is_proxy'])
                                    <div class="text-[10px] text-purple-600 dark:text-purple-400 mt-0.5">
                                        Kuasa: {{ $claim['proxy_name'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400 block">{{ $claim['claimed_at'] }}</span>
                                <span class="text-[10px] text-gray-400 block">{{ $claim['claimed_by'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Scanner & Audio Handler Scripts -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rpcScannerHandler', (eventId) => ({
                html5QrCode: null,
                cameras: [],
                currentCameraIndex: 0,
                isScanning: false,

                init() {
                    this.initAudio();
                    this.initScanner();

                    Livewire.on('scanner-sound', (event) => {
                        const soundType = event.sound || event[0]?.sound || 'success';
                        if (soundType === 'success') {
                            this.playBeep(880, 'sine', 0.18);
                        } else if (soundType === 'warning') {
                            this.playBeep(440, 'triangle', 0.15);
                            setTimeout(() => this.playBeep(330, 'triangle', 0.25), 180);
                        } else {
                            this.playBeep(220, 'square', 0.35);
                        }
                    });
                },

                initAudio() {
                    window.scannerAudioCtx = window.scannerAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
                },

                playBeep(freq, type, duration) {
                    try {
                        const ctx = window.scannerAudioCtx;
                        if (ctx.state === 'suspended') {
                            ctx.resume();
                        }
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = type;
                        osc.frequency.value = freq;
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + duration);
                        osc.stop(ctx.currentTime + duration);
                    } catch (e) {
                        console.log('Audio error:', e);
                    }
                },

                async initScanner() {
                    const statusText = document.getElementById('cameraStatusText');
                    try {
                        this.html5QrCode = new Html5Qrcode("reader");
                        this.cameras = await Html5Qrcode.getCameras();

                        if (!this.cameras || this.cameras.length === 0) {
                            if (statusText) statusText.innerText = "Kamera tidak terdeteksi";
                            return;
                        }

                        // Prefer back camera
                        this.currentCameraIndex = this.cameras.length - 1;
                        await this.startCurrentCamera();
                    } catch (err) {
                        if (statusText) statusText.innerText = "Kamera non-aktif / Ditolak";
                        console.log("Scanner init error:", err);
                    }
                },

                async startCurrentCamera() {
                    if (!this.cameras || this.cameras.length === 0) return;
                    const statusText = document.getElementById('cameraStatusText');
                    const cameraId = this.cameras[this.currentCameraIndex].id;

                    try {
                        if (this.html5QrCode.isScanning) {
                            await this.html5QrCode.stop();
                        }

                        const qrConfig = { 
                            fps: 12, 
                            qrbox: { width: 220, height: 220 },
                            aspectRatio: 1.0
                        };

                        await this.html5QrCode.start(
                            cameraId, 
                            qrConfig, 
                            (decodedText) => this.onScanSuccess(decodedText)
                        );

                        if (statusText) statusText.innerText = "Kamera Aktif & Memindai";
                    } catch (err) {
                        if (statusText) statusText.innerText = "Gagal membuka kamera";
                        console.error("Camera start error:", err);
                    }
                },

                async switchCamera() {
                    if (!this.cameras || this.cameras.length <= 1) {
                        alert("Hanya 1 kamera yang terdeteksi pada perangkat ini.");
                        return;
                    }
                    this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
                    await this.startCurrentCamera();
                },

                onScanSuccess(decodedText) {
                    if (this.isScanning) return;
                    this.isScanning = true;

                    // Play immediate subtle scan tick
                    this.playBeep(1200, 'sine', 0.08);

                    this.$wire.verifyToken(decodedText).finally(() => {
                        // Allow next scan after 2 seconds cooldown
                        setTimeout(() => {
                            this.isScanning = false;
                        }, 2000);
                    });
                }
            }));
        });
    </script>
</x-filament-panels::page>
