# PRD & Technical Design Plan — Jelatix (Running Event Ticketing Platform)

Dokumen lengkap perancangan arsitektur, skema database, integrasi Tripay, Mailketing, Filament v3, dan deployment Hestia CP telah disusun dan tersedia di:
- [PRD_Jelatix_Running_Ticketing.md](file:///Users/bangnahan/.gemini/antigravity-ide/brain/404f995c-4fb4-4a8a-b850-266c4280f5b3/PRD_Jelatix_Running_Ticketing.md)

## Ringkasan Spesifikasi Terpilih:
1. **Model Bisnis**: Multi-Organizer (SaaS/Marketplace Event Lari).
2. **Dashboard & Panel**: Filament PHP v3 (Superadmin Panel `/admin`, Organizer Panel `/organizer`, Scanner Crew Portal `/crew`).
3. **Mekanisme BIB**: Multi-mode:
   - **Auto-assign instan** saat pembayaran Tripay lunas.
   - **Custom BIB VVIP**: Input nomor khusus/cantik manual untuk tamu VVIP, pejabat, sponsor, atau atlet elite (dengan proteksi kunci agar tidak tergeser).
   - **Reserved BIB Pool**: Daftar nomor yang diblokir agar tidak terambil otomatis oleh peserta reguler.
   - **Bulk Re-mapping / Sorting**: Pengurutan ulang nomor massal berdasar gender/kategori/pace sebelum cetak fisik.
4. **Dynamic Custom Form Builder**:
   - EO dapat menambahkan form isian tambahan per event atau per kategori (tipe teks, angka, dropdown, radio, file upload misal bukti sertifikat maraton/ITRA, titik antar-jemput shuttle bus, nama klub). Disimpan fleksibel via JSON schema.
5. **Mitigasi War Tiket & Operasional Lapangan**:
   - **Cart Hold Reservation (15-30 Menit)**: Sinkronisasi waktu bayar Tripay untuk cegah overbooking & ghost sold-out.
   - **Self-Service Portal `/cek-tiket`**: Unduh ulang E-Ticket & QR Code mandiri tanpa panik jika email masuk spam.
   - **Waiver Medis & Hukum Digital**: Persetujuan wajib sebelum checkout guna mitigasi risiko hukum.
   - **Proxy RPC / Surat Kuasa**: Mengakomodasi pengambilan race pack yang diwakilkan via aplikasi scanner kru.
   - **Size Chart Modal & Final Policy**: Mencegah konflik penukaran ukuran jersey di venue.
6. **Payment Gateway**: Tripay Closed Payment API (QRIS & Virtual Account) dengan validasi signature HMAC-SHA256 & DB transaction locking.
7. **Email System**: Mailketing API / SMTP dijalankan asinkron via Laravel Queue Worker.
8. **Server & Deployment**: Hestia Control Panel (Nginx root `/public`, PHP 8.2+, Supervisor Worker, Cronjob Scheduler).
