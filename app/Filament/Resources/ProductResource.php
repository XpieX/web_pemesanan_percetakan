<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Textarea, Select, Repeater, Section, Checkbox};

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $pluralLabel = 'Produk';
    protected static ?string $label = 'Produk';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Detail Produk')
                    ->schema([

                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable(),

                        TextInput::make('price_per_unit')
                            ->label('Harga Dasar')
                            ->numeric()
                            ->required(),

                        // Kalkulasi otomatis menentukan satuan
                        Select::make('calculation_type')
                            ->label('Tipe Perhitungan')
                            ->options([
                                'area' => 'Berdasarkan Luas (width × height)',
                                'quantity' => 'Berdasarkan Jumlah',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'area') {
                                    $set('unit', 'm');
                                } elseif ($state === 'quantity') {
                                    $set('unit', 'pcs');
                                }
                            })
                            ->required(),

                        Select::make('unit')
                            ->label('Satuan')
                            ->options([
                                'm' => 'Meter',
                                'pcs' => 'Pieces',
                            ])
                            ->disabled() // otomatis mengikuti calculation_type
                            ->required(),
                    ])
                    ->columns(2),

                // ===============================
                // 🔹 OPSI: AKTIFKAN PRODUCT SIZE
                // ===============================
                Section::make('Ukuran Produk (Opsional)')
                    ->schema([
                        Checkbox::make('enable_sizes')
                            ->label('Aktifkan ukuran untuk produk ini')
                            ->reactive(),

                        Repeater::make('sizes')
                            ->relationship('sizes')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Ukuran')
                                    ->required(),
                                TextInput::make('price')
                                    ->label('Harga Custom')
                                    ->numeric()
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->visible(fn(callable $get) => $get('enable_sizes') === true),
                    ]),

                // ===============================
                // 🔹 OPSI: AKTIFKAN ADDONS
                // ===============================
                Section::make('Addons Produk (Opsional)')
                    ->schema([
                        Checkbox::make('enable_addons')
                            ->label('Aktifkan addon tambahan')
                            ->reactive(),

                        Repeater::make('addons')
                            ->relationship('addons')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Addon')
                                    ->required(),

                                TextInput::make('price')
                                    ->label('Harga Addon')
                                    ->numeric()
                                    ->required(),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->visible(fn(callable $get) => $get('enable_addons') === true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('unit')
                    ->label('Satuan')
                    ->colors(['primary']),

                TextColumn::make('price_per_unit')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('calculation_type')
                    ->label('Perhitungan')
                    ->badge()
                    ->colors([
                        'success' => 'area',
                        'info' => 'quantity',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
