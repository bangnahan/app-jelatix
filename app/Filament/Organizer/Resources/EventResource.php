<?php

namespace App\Filament\Organizer\Resources;

use App\Filament\Organizer\Resources\EventResource\Pages;
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
    protected static ?string $navigationGroup = 'Manajemen Event Lari';
    protected static ?int $navigationSort = 1;

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
                                    ->default(fn () => auth()->user()?->organizer_id ?: 1)
                                    ->visible(fn () => auth()->user()?->isSuperAdmin())
                                    ->required(),

                                Forms\Components\Hidden::make('organizer_id')
                                    ->default(fn () => auth()->user()?->organizer_id ?: 1)
                                    ->hidden(fn () => auth()->user()?->isSuperAdmin()),

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
                                    ->helperText('📐 Ukuran Rekomendasi: 1200 x 675 pixel (Rasio aspek 16:9), atau minimal 800 x 450 pixel. Format file: JPG, PNG, atau WebP (Maks. 3 MB). Gambar ini akan menjadi banner utama pada kartu event di beranda publik serta header halaman pendaftaran.')
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

                        Forms\Components\Tabs\Tab::make('Kategori Lari')
                            ->schema([
                                Forms\Components\Repeater::make('categories')
                                    ->relationship('categories')
                                    ->label('Kategori Lomba (5K, 10K, 21K, 42K, dll)')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Kategori')
                                            ->placeholder('Contoh: 10K Open')
                                            ->required(),
                                        Forms\Components\TextInput::make('distance_km')
                                            ->label('Jarak (KM)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->required(),
                                        Forms\Components\TextInput::make('normal_price')
                                            ->label('Harga Normal (Rp)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                        Forms\Components\TextInput::make('quota')
                                            ->label('Kuota Slot')
                                            ->numeric()
                                            ->default(500)
                                            ->required(),
                                        Forms\Components\TextInput::make('bib_prefix')
                                            ->label('Prefix BIB')
                                            ->placeholder('Contoh: 10K')
                                            ->maxLength(10),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Buka')
                                            ->default(true),
                                    ])
                                    ->columns(6)
                                    ->columnSpanFull()
                                    ->addActionLabel('+ Tambah Kategori Lari'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Ukuran Jersey')
                            ->schema([
                                Forms\Components\Repeater::make('jerseySizes')
                                    ->relationship('jerseySizes')
                                    ->label('Pilihan Ukuran Jersey Lari (XS sampai 5XL)')
                                    ->schema([
                                        Forms\Components\Select::make('size_name')
                                            ->label('Ukuran')
                                            ->options([
                                                'XS' => 'XS',
                                                'S' => 'S',
                                                'M' => 'M',
                                                'L' => 'L',
                                                'XL' => 'XL',
                                                'XXL' => 'XXL (2XL)',
                                                '3XL' => '3XL',
                                                '4XL' => '4XL',
                                                '5XL' => '5XL',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('gender_type')
                                            ->label('Tipe Gender')
                                            ->options([
                                                'unisex' => 'Unisex',
                                                'male' => 'Pria (Men)',
                                                'female' => 'Wanita (Women)',
                                            ])
                                            ->default('unisex')
                                            ->required(),

                                        Forms\Components\TextInput::make('chest_width_cm')
                                            ->label('Lebar Dada (cm)')
                                            ->numeric(),

                                        Forms\Components\TextInput::make('body_length_cm')
                                            ->label('Panjang Baju (cm)')
                                            ->numeric(),

                                        Forms\Components\TextInput::make('stock')
                                            ->label('Total Stok')
                                            ->numeric()
                                            ->default(100)
                                            ->required(),
                                    ])
                                    ->columns(5)
                                    ->columnSpanFull()
                                    ->addActionLabel('+ Tambah Pilihan Ukuran')
                                    ->helperText('Pilihan ukuran jersey lomba dari XS hingga 5XL dengan panduan ukuran cm dan alokasi stok.'),
                            ]),
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
                    ->label('Penyelenggara')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),

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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
