<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Event Lari';

    protected static ?string $modelLabel = 'Event Lari';

    protected static ?string $pluralModelLabel = 'Daftar Event Lari';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('EventTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Lomba')
                            ->schema([
                                Forms\Components\Select::make('organizer_id')
                                    ->label('Penyelenggara (EO)')
                                    ->relationship('organizer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('title')
                                    ->label('Nama Event Lari')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL Slug')
                                    ->required()
                                    ->unique(Event::class, 'slug', ignoreRecord: true),

                                Forms\Components\FileUpload::make('banner_path')
                                    ->label('Gambar Cover / Banner Event')
                                    ->image()
                                    ->imageEditor()
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->directory('events/banners')
                                    ->maxSize(3072)
                                    ->helperText('📐 Ukuran Rekomendasi: 1200 x 675 pixel (Rasio aspek 16:9), atau minimal 800 x 450 pixel. Format file: JPG, PNG, atau WebP (Maksimal 3 MB). Gambar ini akan menjadi banner utama pada kartu event di beranda publik serta header halaman pendaftaran.')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('short_description')
                                    ->label('Ringkasan Singkat')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('full_description')
                                    ->label('Deskripsi Lengkap & Info Rute')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Waktu & Lokasi')
                            ->schema([
                                Forms\Components\DateTimePicker::make('event_start_date')
                                    ->label('Waktu Start Race')
                                    ->required(),

                                Forms\Components\DateTimePicker::make('event_end_date')
                                    ->label('Waktu Selesai Race'),

                                Forms\Components\TextInput::make('race_location_name')
                                    ->label('Nama Lokasi Race / Start Line')
                                    ->required(),

                                Forms\Components\TextInput::make('race_location_map_url')
                                    ->label('Google Maps URL')
                                    ->url(),

                                Forms\Components\Textarea::make('race_location_address')
                                    ->label('Alamat Lengkap Venue')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Race Pack Collection (RPC)')
                            ->schema([
                                Forms\Components\DateTimePicker::make('rpc_start_date')
                                    ->label('Mulai Pengambilan Race Pack'),

                                Forms\Components\DateTimePicker::make('rpc_end_date')
                                    ->label('Selesai Pengambilan Race Pack'),

                                Forms\Components\Textarea::make('rpc_location')
                                    ->label('Lokasi Booth Pengambilan Race Pack')
                                    ->rows(3)
                                    ->placeholder('Contoh: FX Sudirman Mall Lt. 3, Jakarta')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Pendaftaran & Pengaturan BIB')
                            ->schema([
                                Forms\Components\DateTimePicker::make('registration_open_date')
                                    ->label('Registrasi Dibuka')
                                    ->required(),

                                Forms\Components\DateTimePicker::make('registration_close_date')
                                    ->label('Registrasi Ditutup')
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Status Event')
                                    ->options([
                                        'draft' => 'Draft (Belum Tampil)',
                                        'published' => 'Published (Buka Pendaftaran)',
                                        'registration_closed' => 'Pendaftaran Ditutup',
                                        'completed' => 'Lomba Selesai',
                                    ])->default('draft')->required(),

                                Forms\Components\Toggle::make('auto_assign_bib')
                                    ->label('Terbitkan BIB Otomatis Saat Bayar Lunas')
                                    ->helperText('Jika non-aktif, BIB akan di-generate serentak saat registrasi ditutup')
                                    ->default(true),

                                Forms\Components\Textarea::make('waiver_content')
                                    ->label('Isi Pernyataan Pelepasan Tanggung Jawab (Waiver)')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->default('Dengan ini saya menyatakan bahwa saya dalam kondisi fisik yang sehat dan siap mengikuti lomba lari ini. Saya membebaskan panitia penyelenggara dan platform tiket dari segala tuntutan atas cedera atau gangguan kesehatan selama perlombaan.'),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_path')
                    ->label('Cover')
                    ->width(64)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover shadow-sm']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Event')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Penyelenggara (EO)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('race_location_name')
                    ->label('Lokasi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event_start_date')
                    ->label('Tanggal Race')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('registration_close_date')
                    ->label('Tutup Registrasi')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'registration_closed' => 'warning',
                        'completed' => 'info',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('auto_assign_bib')
                    ->label('Auto BIB')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'registration_closed' => 'Registration Closed',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('organizer_id')
                    ->label('Penyelenggara')
                    ->relationship('organizer', 'name'),
            ])
            ->actions([
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
