<x-filament-widgets::widget>
    @php
        $recap = $this->recapData;
        $event = $this->selectedEvent;
        $sizes = $recap['sizes'] ?? [];
        $categories = $recap['categories'] ?? [];
        $matrix = $recap['matrix'] ?? [];
        $grandTotalPaid = $recap['grand_total_paid'] ?? 0;
        $grandTotalPending = $recap['grand_total_pending'] ?? 0;
        $grandTotal = $recap['grand_total'] ?? 0;

        // Cari ukuran terpopuler
        $mostPopular = null;
        if (!empty($sizes)) {
            $sorted = collect($sizes)->sortByDesc('paid_qty')->first();
            if ($sorted && $sorted['paid_qty'] > 0) {
                $mostPopular = $sorted['size_name'] . ' (' . $sorted['percentage'] . '%)';
            }
        }

        // Teks ringkasan untuk WhatsApp Vendor
        $waLines = [];
        $waLines[] = "*REKAP KEBUTUHAN PRODUKSI JERSEY*";
        $waLines[] = "Event: " . ($event ? $event->title : 'Jelatix Event');
        $waLines[] = "Tanggal Update: " . now()->format('d M Y, H:i') . " WIB";
        $waLines[] = "Status: Peserta Lunas (Siap Naik Jahit)\n";
        foreach ($sizes as $s) {
            $waLines[] = "• Ukuran {$s['size_name']} ({$s['gender_type']}): {$s['paid_qty']} pcs" . ($s['pending_qty'] > 0 ? " (est. pending: +{$s['pending_qty']})" : "");
        }
        $waLines[] = "\n*TOTAL SIAP PRODUKSI: " . number_format($grandTotalPaid) . " pcs*";
        if ($grandTotalPending > 0) {
            $waLines[] = "_(Menunggu pembayaran: +" . number_format($grandTotalPending) . " pcs)_";
        }
        $waText = implode("\n", $waLines);
    @endphp

    <div 
        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-5"
        x-data="{
            copied: false,
            copySummary() {
                const text = @js($waText);
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2500);
                });
            }
        }"
    >
        <!-- Header & Action Controls -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Rekap Kebutuhan Produksi Jersey</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                            Live Pendaftar
                        </span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Data real-time untuk vendor konveksi selagi pendaftaran masih dibuka, tanpa menunggu closing.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <!-- Event Picker -->
                @if(count($this->getEvents()) > 1)
                    <select 
                        wire:model.live="selectedEventId" 
                        class="text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-sky-500 focus:border-sky-500 py-2"
                    >
                        @foreach($this->getEvents() as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->title }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- View Mode Toggle -->
                @if(count($categories) > 1)
                    <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-0.5 text-xs font-semibold">
                        <button 
                            type="button" 
                            wire:click="$set('viewMode', 'summary')" 
                            class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'summary' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Total Ukuran
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('viewMode', 'by_category')" 
                            class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'by_category' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Per Kategori Lomba
                        </button>
                    </div>
                @endif

                <!-- Copy WhatsApp Summary -->
                <button 
                    type="button" 
                    @click="copySummary()" 
                    class="bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 font-bold px-3 py-2 rounded-xl text-xs transition border border-emerald-200 dark:border-emerald-800 flex items-center gap-1.5 cursor-pointer"
                    title="Salin rekap ringkas untuk dikirim ke chat WhatsApp vendor konveksi"
                >
                    <template x-if="!copied">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-o-clipboard-document class="w-4 h-4 text-emerald-600" />
                            <span>Salin Chat Vendor</span>
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-extrabold">
                            <x-heroicon-s-check class="w-4 h-4" />
                            <span>Tersalin!</span>
                        </span>
                    </template>
                </button>

                <!-- Export CSV -->
                <button 
                    type="button" 
                    wire:click="exportCsv" 
                    class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-3.5 py-2 rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer"
                >
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    <span>Export CSV Vendor</span>
                </button>
            </div>
        </div>

        <!-- Quick Summary Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-sm">
                    PO
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block uppercase tracking-wider">Siap Diproduksi (Lunas)</span>
                    <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($grandTotalPaid) }} <span class="text-xs font-semibold text-gray-500">pcs</span></span>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black text-sm">
                    WAIT
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block uppercase tracking-wider">Menunggu Pembayaran</span>
                    <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ number_format($grandTotalPending) }} <span class="text-xs font-semibold text-gray-500">pcs</span></span>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 font-black text-sm">
                    ALL
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block uppercase tracking-wider">Total Permintaan</span>
                    <span class="text-xl font-black text-purple-600 dark:text-purple-400">{{ number_format($grandTotal) }} <span class="text-xs font-semibold text-gray-500">pcs</span></span>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5 flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 font-black text-sm">
                    TOP
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block uppercase tracking-wider">Ukuran Terpopuler</span>
                    <span class="text-base font-black text-sky-600 dark:text-sky-400">{{ $mostPopular ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Mode 1: Summary Table by Size -->
        @if($viewMode === 'summary')
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="py-3 px-4">Ukuran Jersey</th>
                            <th class="py-3 px-3">Tipe Gender</th>
                            <th class="py-3 px-3 text-center">Panduan (LD x P)</th>
                            <th class="py-3 px-3 text-right">Siap Produksi (Lunas)</th>
                            <th class="py-3 px-3 text-right">Pending Bayar</th>
                            <th class="py-3 px-3 text-right">Total Terdaftar</th>
                            <th class="py-3 px-4 min-w-[140px]">Distribusi (%)</th>
                            <th class="py-3 px-4 text-center">Status Kuota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @forelse($sizes as $size)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center justify-center font-black font-mono px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs">
                                        {{ $size['size_name'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 uppercase text-gray-600 dark:text-gray-400">
                                    {{ $size['gender_type'] }}
                                </td>
                                <td class="py-3 px-3 text-center text-gray-500 dark:text-gray-400 font-mono text-[11px]">
                                    @if($size['chest_width_cm'] || $size['body_length_cm'])
                                        {{ $size['chest_width_cm'] ?: '-' }} x {{ $size['body_length_cm'] ?: '-' }} cm
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-black font-mono text-sm text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($size['paid_qty']) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-gray-500 dark:text-gray-400">
                                    {{ $size['pending_qty'] > 0 ? '+'.number_format($size['pending_qty']) : '-' }}
                                </td>
                                <td class="py-3 px-3 text-right font-black font-mono text-gray-900 dark:text-white">
                                    {{ number_format($size['total_qty']) }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                            <div 
                                                class="bg-amber-500 h-full rounded-full transition-all duration-300" 
                                                style="width: {{ $size['percentage'] }}%"
                                            ></div>
                                        </div>
                                        <span class="text-[11px] font-bold font-mono text-gray-600 dark:text-gray-300 w-10 text-right">
                                            {{ $size['percentage'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($size['is_unlimited'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900">
                                            Unlimited
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                            Batas: {{ $size['stock'] }} (Sisa {{ $size['available_stock'] }})
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    Belum ada data ukuran jersey pada event ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50/80 dark:bg-gray-800/80 font-black text-gray-900 dark:text-white border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td colspan="3" class="py-3.5 px-4 uppercase tracking-wider text-xs">Total Keseluruhan PO Produksi</td>
                            <td class="py-3.5 px-3 text-right font-mono text-base text-emerald-600 dark:text-emerald-400">
                                {{ number_format($grandTotalPaid) }} pcs
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono text-gray-500">
                                {{ $grandTotalPending > 0 ? '+'.number_format($grandTotalPending).' pcs' : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono text-base">
                                {{ number_format($grandTotal) }} pcs
                            </td>
                            <td class="py-3.5 px-4 font-mono">100.0%</td>
                            <td class="py-3.5 px-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <!-- Mode 2: Cross-Tabulation Matrix by Category -->
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="py-3 px-4 min-w-[160px]">Kategori Lomba</th>
                            @foreach($sizes as $size)
                                <th class="py-3 px-2 text-center font-mono font-black text-amber-600 dark:text-amber-400">
                                    {{ $size['size_name'] }}
                                </th>
                            @endforeach
                            <th class="py-3 px-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                Total Kategori
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @foreach($categories as $cat)
                            @php $catTotal = 0; @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                    {{ $cat->name }} ({{ $cat->distance_km }} KM)
                                </td>
                                @foreach($sizes as $size)
                                    @php
                                        $qty = $matrix[$cat->id][$size['id']] ?? 0;
                                        $catTotal += $qty;
                                    @endphp
                                    <td class="py-3 px-2 text-center font-mono {{ $qty > 0 ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600' }}">
                                        {{ $qty > 0 ? number_format($qty) : '-' }}
                                    </td>
                                @endforeach
                                <td class="py-3 px-4 text-right font-black font-mono text-sm text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($catTotal) }} pcs
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/80 dark:bg-gray-800/80 font-black text-gray-900 dark:text-white border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="py-3.5 px-4 uppercase tracking-wider text-xs">Total Seluruh Kategori</td>
                            @foreach($sizes as $size)
                                <td class="py-3.5 px-2 text-center font-mono font-black text-sm text-amber-600 dark:text-amber-400">
                                    {{ number_format($size['paid_qty']) }}
                                </td>
                            @endforeach
                            <td class="py-3.5 px-4 text-right font-mono text-base text-emerald-600 dark:text-emerald-400">
                                {{ number_format($grandTotalPaid) }} pcs
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Tip Box for Production PO -->
        <div class="p-3.5 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-900 flex items-start gap-3">
            <x-heroicon-o-light-bulb class="w-5 h-5 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" />
            <div class="text-xs text-sky-900 dark:text-sky-200 leading-relaxed">
                <strong>Alur Produksi Cepat Panitia:</strong> Konveksi jersey lari rata-rata membutuhkan waktu produksi 2–3 minggu. Anda dapat melakukan Purchase Order (PO) <strong>Batch 1</strong> untuk pendaftar lunas saat ini ({{ number_format($grandTotalPaid) }} pcs). Nanti menjelang pendaftaran ditutup, Anda tinggal melakukan PO <strong>Batch 2</strong> untuk sisa peserta tambahan tanpa khawatir jersey terlambat tiba sebelum hari RPC.
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
