<?php

namespace App\Filament\Organizer\Resources;

use App\Filament\Organizer\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaksi & Invoice';
    protected static ?string $navigationGroup = 'Keuangan & Transaksi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_code')
                    ->label('Kode Order')
                    ->disabled(),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nama Pembeli')
                    ->disabled(),
                Forms\Components\TextInput::make('customer_email')
                    ->label('Email')
                    ->disabled(),
                Forms\Components\TextInput::make('customer_phone')
                    ->label('No. Telepon')
                    ->disabled(),
                Forms\Components\TextInput::make('tripay_payment_method')
                    ->label('Metode Bayar Tripay')
                    ->disabled(),
                Forms\Components\TextInput::make('tripay_reference')
                    ->label('Tripay Reference')
                    ->disabled(),
                Forms\Components\TextInput::make('grand_total')
                    ->label('Total Bayar (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas (Paid)',
                        'expired' => 'Kadaluarsa (Expired)',
                        'failed' => 'Gagal (Failed)',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->label('Kode Order')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Pendaftar')
                    ->searchable()
                    ->description(fn (Order $record) => $record->customer_email),

                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->limit(25),

                Tables\Columns\TextColumn::make('tripay_payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'expired', 'failed' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Waktu Lunas')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Order')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
