<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizerResource\Pages;
use App\Models\Organizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrganizerResource extends Resource
{
    protected static ?string $model = Organizer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Penyelenggara (EO)';

    protected static ?string $modelLabel = 'Organizer';

    protected static ?string $pluralModelLabel = 'Penyelenggara (Organizers)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Dasar Penyelenggara')
                            ->description('Identitas dan kontak resmi Event Organizer (EO)')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Organizer / Komunitas')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $operation) {
                                        if ($operation === 'create' && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Digunakan untuk tautan publik EO'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Resmi')
                                    ->email()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Nomor WhatsApp / Kontak')
                                    ->tel()
                                    ->maxLength(50),

                                Forms\Components\Textarea::make('description')
                                    ->label('Profil Singkat')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('logo_path')
                                    ->label('Logo Organizer')
                                    ->image()
                                    ->directory('organizers/logos')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Rekening Bank Pencairan Dana (Payout)')
                            ->description('Rekening tujuan pencairan hasil penjualan tiket event lari')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->placeholder('Contoh: BCA, Bank Mandiri, BRI, BNI')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('bank_account_number')
                                    ->label('Nomor Rekening')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('bank_account_holder')
                                    ->label('Atas Nama Rekening')
                                    ->maxLength(255),
                            ])->columns(3),

                        Forms\Components\Section::make('Akun Pengguna & Akses Login Portal EO')
                            ->description('Buat akun login pertama untuk PIC Penyelenggara agar bisa langsung mengakses https://app.jelatix.com/organizer')
                            ->schema([
                                Forms\Components\Toggle::make('create_user_account')
                                    ->label('Buat Akun Login untuk PIC Penyelenggara Ini')
                                    ->default(true)
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('pic_name')
                                    ->label('Nama Lengkap PIC EO')
                                    ->visible(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->required(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('pic_email')
                                    ->label('Email Login PIC')
                                    ->email()
                                    ->visible(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->required(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->maxLength(255)
                                    ->helperText('Email ini akan digunakan PIC untuk login ke portal https://app.jelatix.com/organizer'),

                                Forms\Components\TextInput::make('pic_password')
                                    ->label('Password Login Awal')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->required(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->default(fn () => Str::random(10))
                                    ->helperText('Password acak dibuat otomatis. Anda dapat menyesuaikannya atau mencatatnya untuk diberikan ke EO.'),

                                Forms\Components\Toggle::make('send_credentials_email')
                                    ->label('Kirim Kredensial Login (Email & Password) Otomatis ke Email PIC')
                                    ->visible(fn (Forms\Get $get) => (bool) $get('create_user_account'))
                                    ->default(true)
                                    ->helperText('Sistem akan mengirimkan email resmi berisi URL portal dan password login ke email PIC di atas.'),
                            ])->columns(2)->visibleOn('create'),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Pengaturan Platform')
                            ->schema([
                                Forms\Components\TextInput::make('commission_rate')
                                    ->label('Komisi Platform Jelatix')
                                    ->numeric()
                                    ->default(5.00)
                                    ->suffix('%')
                                    ->required()
                                    ->helperText('Persentase bagi hasil platform dari omset tiket'),

                                Forms\Components\Toggle::make('is_verified')
                                    ->label('Organizer Terverifikasi')
                                    ->default(true)
                                    ->helperText('EO terverifikasi memiliki akses penuh'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Nonaktifkan untuk membatasi akses EO'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0284c7&color=fff'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Organizer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon / WA')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($record) => $record->bank_name ? "{$record->bank_name} - {$record->bank_account_number}" : '-'),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Fee')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('events_count')
                    ->counts('events')
                    ->label('Event')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Status Verifikasi'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_accounts')
                    ->label('Akses Login')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->modalHeading(fn (Organizer $record) => "Kelola Akun Login PIC: {$record->name}")
                    ->form([
                        Forms\Components\Placeholder::make('existing_users')
                            ->label('Akun Login Terdaftar Saat Ini')
                            ->content(function (Organizer $record) {
                                $users = $record->users;
                                if ($users->isEmpty()) {
                                    return 'Belum ada akun pengguna untuk organizer ini.';
                                }
                                return $users->map(fn ($u) => "• {$u->name} ({$u->email}) - Role: {$u->role}")->join("\n");
                            }),

                        Forms\Components\TextInput::make('user_name')
                            ->label('Nama Lengkap PIC / Pengguna')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Organizer $record) => $record->users->first()?->name ?: $record->name),

                        Forms\Components\TextInput::make('user_email')
                            ->label('Email Login PIC')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Organizer $record) => $record->users->first()?->email ?: $record->email)
                            ->helperText('Jika email sudah ada di sistem, passwordnya akan diperbarui. Jika belum ada, akun baru akan otomatis dibuat.'),

                        Forms\Components\TextInput::make('user_password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->required()
                            ->default(fn () => Str::random(10))
                            ->helperText('Password baru untuk login ke portal https://app.jelatix.com/organizer'),

                        Forms\Components\Toggle::make('send_email_notification')
                            ->label('Kirim Kredensial Baru ke Email Pengguna')
                            ->default(true)
                            ->helperText('Otomatis kirimkan email resmi berisi password baru ke email PIC di atas.'),
                    ])
                    ->action(function (Organizer $record, array $data, \App\Services\MailketingService $mailketing) {
                        $user = \App\Models\User::where('email', $data['user_email'])->first();

                        if ($user) {
                            $user->update([
                                'organizer_id' => $record->id,
                                'name' => $data['user_name'],
                                'password' => bcrypt($data['user_password']),
                                'role' => in_array($user->role, ['superadmin', 'organizer_owner']) ? $user->role : 'organizer_owner',
                                'is_active' => true,
                            ]);
                        } else {
                            $user = \App\Models\User::create([
                                'organizer_id' => $record->id,
                                'name' => $data['user_name'],
                                'email' => $data['user_email'],
                                'password' => bcrypt($data['user_password']),
                                'role' => 'organizer_owner',
                                'is_active' => true,
                            ]);
                        }

                        if (!empty($data['send_email_notification'])) {
                            $loginUrl = rtrim(config('app.url', 'https://app.jelatix.com'), '/') . '/organizer';
                            if (str_contains($loginUrl, 'localhost') || str_contains($loginUrl, '127.0.0.1')) {
                                $loginUrl = 'https://app.jelatix.com/organizer';
                            }

                            $html = view('emails.organizer_credentials', [
                                'organizer' => $record,
                                'userName' => $user->name,
                                'userEmail' => $user->email,
                                'password' => $data['user_password'],
                                'loginUrl' => $loginUrl,
                            ])->render();

                            try {
                                $mailketing->sendEmail(
                                    recipientEmail: $user->email,
                                    recipientName: $user->name,
                                    subject: "[Akses Portal EO] Kredensial Login Penyelenggara: {$record->name}",
                                    htmlContent: $html
                                );
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Gagal kirim email reset password EO: " . $e->getMessage());
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Akun Login Berhasil Disimpan!')
                            ->body("Akun {$user->email} kini dapat login ke portal /organizer dengan password baru.")
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
            'index' => Pages\ListOrganizers::route('/'),
            'create' => Pages\CreateOrganizer::route('/create'),
            'edit' => Pages\EditOrganizer::route('/{record}/edit'),
        ];
    }
}
