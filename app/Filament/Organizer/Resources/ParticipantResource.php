<?php

namespace App\Filament\Organizer\Resources;

use App\Filament\Organizer\Resources\ParticipantResource\Pages;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Services\BibGeneratorService;
use App\Services\MailketingService;
use App\Services\TicketPdfService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Pelari & BIB';
    protected static ?string $navigationGroup = 'Manajemen Event Lari';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Nomor Dada & Status VIP')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('bib_number')
                            ->label('Nomor BIB')
                            ->placeholder('Contoh: 10K1001 atau 0001')
                            ->helperText('Nomor dada yang dicetak fisik'),
                        Forms\Components\Toggle::make('is_vip')
                            ->label('Peserta VVIP')
                            ->helperText('Tamu kehormatan / Sponsor / Atlet'),
                        Forms\Components\Toggle::make('is_custom_bib')
                            ->label('Kunci BIB (Custom VVIP)')
                            ->helperText('Jika aktif, nomor tidak akan tergeser saat bulk remap'),
                        Forms\Components\TextInput::make('custom_bib_reason')
                            ->label('Alasan Custom BIB / Catatan VIP')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Data Identitas Pelari')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('event_category_id')
                            ->label('Kategori Lari')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\Select::make('jersey_size_id')
                            ->label('Ukuran Jersey')
                            ->relationship('jerseySize', 'size_name')
                            ->required(),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('bib_name')
                            ->label('Nama di BIB (Max 14 Karakter)')
                            ->maxLength(14)
                            ->placeholder('Nama pendek di nomor dada'),
                        Forms\Components\Select::make('id_type')
                            ->label('Jenis Identitas')
                            ->options([
                                'KTP' => 'KTP',
                                'SIM' => 'SIM',
                                'Passport' => 'Passport',
                                'KIA' => 'KIA',
                            ])->default('KTP'),
                        Forms\Components\TextInput::make('id_number')
                            ->label('Nomor NIK / Paspor')
                            ->required(),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])->required(),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->required(),
                        Forms\Components\Select::make('blood_type')
                            ->label('Golongan Darah')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-',
                                'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-',
                                'O+' => 'O+', 'O-' => 'O-',
                                'Unknown' => 'Tidak Tahu',
                            ])->default('Unknown'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor WhatsApp/HP')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Kontak Darurat & Riwayat Medis')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->label('Nama Kontak Darurat')
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('No. Telp Darurat')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact_relation')
                            ->label('Hubungan')
                            ->placeholder('Istri, Orang Tua, Teman')
                            ->required(),
                        Forms\Components\Textarea::make('medical_conditions')
                            ->label('Riwayat Medis / Alergi')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bib_number')
                    ->label('BIB')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->color(fn (Participant $record) => $record->is_vip ? 'warning' : 'primary'),

                Tables\Columns\IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('bib_name')
                    ->label('Nama di BIB')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => strtoupper((string) $state)),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->description(fn (Participant $record) => $record->email . ' | ' . $record->phone),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jerseySize.size_name')
                    ->label('Jersey')
                    ->badge(),

                Tables\Columns\TextColumn::make('blood_type')
                    ->label('Gol. Darah')
                    ->badge()
                    ->color('danger'),

                Tables\Columns\IconColumn::make('is_rpc_claimed')
                    ->label('Ambil RPC')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_category_id')
                    ->label('Kategori Lari')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_vip')
                    ->label('Hanya Tamu VVIP'),

                Tables\Filters\TernaryFilter::make('is_rpc_claimed')
                    ->label('Status Pengambilan RPC'),

                Tables\Filters\SelectFilter::make('blood_type')
                    ->label('Golongan Darah')
                    ->options([
                        'A+' => 'A+', 'B+' => 'B+', 'AB+' => 'AB+', 'O+' => 'O+',
                    ]),
            ])
            ->actions([
                // 1. Action: Tetapkan Custom BIB VVIP
                Tables\Actions\Action::make('custom_bib')
                    ->label('Custom BIB VVIP')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('custom_bib_number')
                            ->label('Nomor Cantik / Khusus')
                            ->default(fn (Participant $record) => $record->bib_number)
                            ->required()
                            ->placeholder('Contoh: 0001, 8888, VIP-01'),
                        Forms\Components\TextInput::make('reason')
                            ->label('Alasan / Jabatan Tamu')
                            ->default(fn (Participant $record) => $record->custom_bib_reason ?: 'Tamu VVIP')
                            ->required(),
                    ])
                    ->action(function (Participant $record, array $data, BibGeneratorService $bibService) {
                        try {
                            $bibService->assignCustomVipBib($record, $data['custom_bib_number'], $data['reason']);

                            Notification::make()
                                ->title('Nomor BIB VVIP Berhasil Disimpan')
                                ->body("Peserta {$record->full_name} kini memiliki nomor BIB {$data['custom_bib_number']}.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menyimpan BIB')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 2. Action: Download E-Ticket PDF
                Tables\Actions\Action::make('download_ticket')
                    ->label('PDF Tiket')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Participant $record, TicketPdfService $ticketService): StreamedResponse {
                        $pdfBinary = $ticketService->generateTicketPdf($record);
                        $filename = 'E-Ticket-' . ($record->bib_number ?: $record->id) . '.pdf';

                        return response()->streamDownload(function () use ($pdfBinary) {
                            echo $pdfBinary;
                        }, $filename, ['Content-Type' => 'application/pdf']);
                    }),

                // 3. Action: Kirim Ulang Email Tiket via Mailketing
                Tables\Actions\Action::make('resend_email')
                    ->label('Kirim Ulang Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Ulang E-Ticket ke Peserta')
                    ->modalDescription(fn (Participant $record) => "Apakah Anda yakin ingin mengirim ulang tiket ke {$record->email}?")
                    ->action(function (Participant $record, MailketingService $mailketing, TicketPdfService $ticketService) {
                        $pdfBinary = $ticketService->generateTicketPdf($record);
                        $pdfBase64 = base64_encode($pdfBinary);

                        $subject = "E-Ticket & Nomor BIB: {$record->event?->title} - {$record->bib_number}";
                        $html = view('emails.ticket_confirmed', [
                            'participant' => $record,
                            'event' => $record->event,
                            'order' => $record->order,
                        ])->render();

                        $sent = $mailketing->sendEmail(
                            recipientEmail: $record->email,
                            recipientName: $record->full_name,
                            subject: $subject,
                            htmlContent: $html,
                            attachmentBase64: $pdfBase64,
                            attachmentName: "E-Ticket-{$record->bib_number}.pdf",
                            attachmentUrl: route('public.ticket.download', $record->qr_token)
                        );

                        if ($sent) {
                            Notification::make()
                                ->title('Email Berhasil Dikirim')
                                ->body("E-Ticket telah dikirim ke {$record->email}.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Pengiriman Email Gagal')
                                ->body('Periksa log sistem atau konfigurasi API Token Mailketing.')
                                ->danger()
                                ->send();
                        }
                    }),

                // 4. Action: Official BIB Transfer / Ganti Pelari
                Tables\Actions\Action::make('transfer_ticket')
                    ->label('Transfer Tiket')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->modalHeading('Official BIB Transfer (Ganti Pelari)')
                    ->modalDescription(fn (Participant $record) => "Ganti data pemilik nomor BIB {$record->bib_number} ({$record->full_name}) ke pelari baru.")
                    ->form([
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nama Lengkap Pelari Baru')
                            ->required(),
                        Forms\Components\TextInput::make('bib_name')
                            ->label('Nama di BIB Baru (Max 14 Karakter)')
                            ->maxLength(14)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Pelari Baru')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor WhatsApp Pelari Baru')
                            ->tel()
                            ->required(),
                        Forms\Components\Select::make('id_type')
                            ->label('Jenis Identitas')
                            ->options(['KTP' => 'KTP', 'SIM' => 'SIM', 'Passport' => 'Passport', 'KIA' => 'KIA'])
                            ->default('KTP')
                            ->required(),
                        Forms\Components\TextInput::make('id_number')
                            ->label('Nomor Identitas (NIK/Paspor)')
                            ->required(),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options(['male' => 'Laki-laki', 'female' => 'Perempuan'])
                            ->required(),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->required(),
                        Forms\Components\Select::make('blood_type')
                            ->label('Golongan Darah')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                                'Unknown' => 'Tidak Tahu',
                            ])->required(),
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->label('Nama Kontak Darurat')
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('No. Telp Darurat')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact_relation')
                            ->label('Hubungan')
                            ->required(),
                        Forms\Components\Textarea::make('medical_conditions')
                            ->label('Riwayat Medis / Alergi'),
                    ])
                    ->action(function (Participant $record, array $data, MailketingService $mailketing, TicketPdfService $ticketService) {
                        $oldName = $record->full_name;
                        $data['bib_name'] = strtoupper($data['bib_name']);
                        $data['qr_token'] = \Illuminate\Support\Str::random(32);
                        $record->update($data);

                        // Kirim email tiket baru ke peserta pengganti
                        $pdfBinary = $ticketService->generateTicketPdf($record);
                        $pdfBase64 = base64_encode($pdfBinary);
                        $subject = "Konfirmasi Transfer Tiket BIB {$record->bib_number}: {$record->event?->title}";
                        $html = view('emails.ticket_confirmed', [
                            'participant' => $record,
                            'event' => $record->event,
                            'order' => $record->order,
                        ])->render();

                        $mailketing->sendEmail(
                            recipientEmail: $record->email,
                            recipientName: $record->full_name,
                            subject: $subject,
                            htmlContent: $html,
                            attachmentBase64: $pdfBase64,
                            attachmentName: "E-Ticket-{$record->bib_number}.pdf",
                            attachmentUrl: route('public.ticket.download', $record->qr_token)
                        );

                        Notification::make()
                            ->title('Transfer Tiket Berhasil')
                            ->body("BIB {$record->bib_number} berhasil dialihkan dari {$oldName} ke {$record->full_name}. E-Ticket baru dikirim ke {$record->email}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                // Header Action 1: Export CSV untuk Vendor Cetak BIB & Timing Chip
                Tables\Actions\Action::make('export_vendor_csv')
                    ->label('Export Cetak BIB & Timing (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (): StreamedResponse {
                        $filename = 'data-peserta-cetak-bib-' . date('Ymd-His') . '.csv';
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        return response()->streamDownload(function () {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'BIB_NUMBER',
                                'NAMA_BIB',
                                'NAMA_LENGKAP',
                                'KATEGORI',
                                'JARAK_KM',
                                'GENDER',
                                'TGL_LAHIR',
                                'GOL_DARAH',
                                'UKURAN_JERSEY',
                                'NO_HP',
                                'EMAIL',
                                'KONTAK_DARURAT_NAMA',
                                'KONTAK_DARURAT_TELP',
                                'KONTAK_DARURAT_HUBUNGAN',
                                'RIWAYAT_MEDIS',
                                'STATUS_VIP',
                                'STATUS_RPC',
                                'QR_TOKEN',
                            ]);

                            Participant::with(['category', 'jerseySize'])
                                ->whereNotNull('bib_number')
                                ->orderBy('bib_number', 'asc')
                                ->chunk(100, function ($participants) use ($handle) {
                                    foreach ($participants as $p) {
                                        fputcsv($handle, [
                                            $p->bib_number,
                                            $p->bib_name,
                                            $p->full_name,
                                            $p->category?->name,
                                            $p->category?->distance_km,
                                            $p->gender,
                                            $p->birth_date?->format('Y-m-d'),
                                            $p->blood_type,
                                            $p->jerseySize?->size_name,
                                            $p->phone,
                                            $p->email,
                                            $p->emergency_contact_name,
                                            $p->emergency_contact_phone,
                                            $p->emergency_contact_relation,
                                            $p->medical_conditions,
                                            $p->is_vip ? 'VIP' : 'REGULER',
                                            $p->is_rpc_claimed ? 'SUDAH_KLAIM' : 'BELUM_KLAIM',
                                            $p->qr_token,
                                        ]);
                                    }
                                });

                            fclose($handle);
                        }, $filename, $headers);
                    }),

                // Header Action 2: Bulk Re-map BIBs (Aman dari VVIP)
                Tables\Actions\Action::make('bulk_remap')
                    ->label('Urutkan Ulang BIB (Bulk Remap)')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('category_id')
                            ->label('Pilih Kategori Lari')
                            ->options(EventCategory::pluck('name', 'id'))
                            ->required(),
                        Forms\Components\Select::make('sort_by')
                            ->label('Kriteria Pengurutan')
                            ->options([
                                'estimated_finish_time' => 'Estimasi Waktu Finish / Pace Lari',
                                'birth_date' => 'Usia Peserta',
                                'gender' => 'Jenis Kelamin',
                                'created_at' => 'Waktu Pendaftaran Awal',
                            ])->default('estimated_finish_time')->required(),
                        Forms\Components\Select::make('direction')
                            ->label('Arah Urutan')
                            ->options([
                                'asc' => 'Ascending (A-Z / Waktu Tercepat ke Terlambat)',
                                'desc' => 'Descending',
                            ])->default('asc')->required(),
                    ])
                    ->action(function (array $data, BibGeneratorService $bibService) {
                        $category = EventCategory::findOrFail($data['category_id']);
                        $remapped = $bibService->bulkRemapBibs($category, $data['sort_by'], $data['direction']);

                        Notification::make()
                            ->title('Penomoran BIB Selesai')
                            ->body("Berhasil mengurutkan {$remapped} pelari reguler. Nomor peserta VVIP tetap terlindungi.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
