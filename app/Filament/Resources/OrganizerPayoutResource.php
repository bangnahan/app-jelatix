<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizerPayoutResource\Pages;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\OrganizerPayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrganizerPayoutResource extends Resource
{
    protected static ?string $model = OrganizerPayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pencairan Dana (Payout)';

    protected static ?string $modelLabel = 'Payout';

    protected static ?string $pluralModelLabel = 'Pencairan Dana (Payouts)';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Detail Pengajuan Payout')
                            ->description('Pilih Penyelenggara dan Event yang dananya akan dicairkan')
                            ->schema([
                                Forms\Components\Select::make('organizer_id')
                                    ->label('Penyelenggara (EO)')
                                    ->relationship('organizer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, ?int $state) {
                                        if ($state) {
                                            $org = Organizer::find($state);
                                            if ($org) {
                                                $set('bank_name', $org->bank_name);
                                                $set('bank_account_number', $org->bank_account_number);
                                                $set('bank_account_holder', $org->bank_account_holder);
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('event_id')
                                    ->label('Event Lari Terkait')
                                    ->options(function (Forms\Get $get) {
                                        $organizerId = $get('organizer_id');
                                        if ($organizerId) {
                                            return Event::where('organizer_id', $organizerId)->pluck('title', 'id');
                                        }
                                        return Event::pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('milestone_phase')
                                    ->label('Tahap Termin Pencairan')
                                    ->options([
                                        'phase_1_closed_reg' => 'Termin 1: Pendaftaran Ditutup (Maks 70%)',
                                        'phase_2_post_race' => 'Termin 2: Pasca Event Selesai (Pelunasan 30%)',
                                        'custom' => 'Termin Khusus / Ad-hoc',
                                    ])
                                    ->default('phase_1_closed_reg')
                                    ->required(),
                            ])->columns(3),

                        Forms\Components\Section::make('Nominal & Kalkulasi Pembagian Dana')
                            ->schema([
                                Forms\Components\TextInput::make('requested_amount')
                                    ->label('Nominal Kotor Diajukan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                        $requested = (float) ($state ?? 0);
                                        $fee = (float) ($get('platform_fee_deducted') ?? 0);
                                        $set('net_payout_amount', max(0, $requested - $fee));
                                    }),

                                Forms\Components\TextInput::make('platform_fee_deducted')
                                    ->label('Potongan Komisi Platform')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                        $requested = (float) ($get('requested_amount') ?? 0);
                                        $fee = (float) ($state ?? 0);
                                        $set('net_payout_amount', max(0, $requested - $fee));
                                    }),

                                Forms\Components\TextInput::make('net_payout_amount')
                                    ->label('Nominal Bersih yang Ditransfer')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->helperText('Otomatis: Nominal Kotor dikurangi Potongan Komisi'),
                            ])->columns(3),

                        Forms\Components\Section::make('Rekening Tujuan Transfer')
                            ->description('Rekening bank penerima dana pencairan')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('bank_account_number')
                                    ->label('Nomor Rekening')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('bank_account_holder')
                                    ->label('Nama Pemilik Rekening')
                                    ->required()
                                    ->maxLength(255),
                            ])->columns(3),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status & Bukti Transfer')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Payout')
                                    ->options([
                                        'requested' => 'Diajukan (Requested)',
                                        'approved' => 'Disetujui (Approved)',
                                        'transferred' => 'Sudah Ditransfer (Transferred)',
                                        'rejected' => 'Ditolak (Rejected)',
                                    ])
                                    ->default('requested')
                                    ->required()
                                    ->live(),

                                Forms\Components\DateTimePicker::make('transferred_at')
                                    ->label('Waktu Transfer Selesai')
                                    ->visible(fn (Forms\Get $get) => $get('status') === 'transferred'),

                                Forms\Components\FileUpload::make('transfer_proof_path')
                                    ->label('Bukti Transfer Bank')
                                    ->image()
                                    ->directory('payouts/proofs')
                                    ->openable()
                                    ->downloadable()
                                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'transferred'])),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan Superadmin')
                                    ->rows(3)
                                    ->placeholder('Keterangan tambahan untuk EO'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Penyelenggara (EO)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->event?->title),

                Tables\Columns\TextColumn::make('milestone_phase')
                    ->label('Termin')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'phase_1_closed_reg' => 'Termin 1 (70%)',
                        'phase_2_post_race' => 'Termin 2 (30%)',
                        default => 'Khusus',
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('net_payout_amount')
                    ->label('Nominal Bersih')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('bank_account_number')
                    ->label('Rekening Tujuan')
                    ->formatStateUsing(fn ($record) => "{$record->bank_name} - {$record->bank_account_number}")
                    ->description(fn ($record) => "a.n {$record->bank_account_holder}")
                    ->copyable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'requested' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'transferred' => 'Ditransfer',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'requested' => 'warning',
                        'approved' => 'info',
                        'transferred' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('transferred_at')
                    ->label('Waktu Transfer')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Payout')
                    ->options([
                        'requested' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'transferred' => 'Sudah Ditransfer',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('organizer_id')
                    ->label('Penyelenggara')
                    ->relationship('organizer', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_transferred')
                    ->label('Tandai Ditransfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (OrganizerPayout $record) => in_array($record->status, ['requested', 'approved']))
                    ->form([
                        Forms\Components\DateTimePicker::make('transferred_at')
                            ->label('Waktu Transfer')
                            ->default(now())
                            ->required(),
                        Forms\Components\FileUpload::make('transfer_proof_path')
                            ->label('Unggah Bukti Transfer')
                            ->image()
                            ->directory('payouts/proofs')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Bukti / Referensi Bank'),
                    ])
                    ->action(function (OrganizerPayout $record, array $data) {
                        $record->update([
                            'status' => 'transferred',
                            'transferred_at' => $data['transferred_at'],
                            'transfer_proof_path' => $data['transfer_proof_path'] ?? $record->transfer_proof_path,
                            'notes' => $data['notes'] ?? $record->notes,
                            'approved_by_user_id' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Payout Berhasil Ditandai Lunas!')
                            ->body("Dana sebesar Rp " . number_format($record->net_payout_amount, 0, ',', '.') . " telah ditandai ditransfer ke {$record->organizer?->name}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListOrganizerPayouts::route('/'),
            'create' => Pages\CreateOrganizerPayout::route('/create'),
            'edit' => Pages\EditOrganizerPayout::route('/{record}/edit'),
        ];
    }
}
