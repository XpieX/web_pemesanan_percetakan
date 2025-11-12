<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\ProductSize;
use App\Models\Addon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Forms\Components\Toggle;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pesanan';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Umum')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Barang Dipesan')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->columns(2)
                            ->label('Daftar Barang Dipesan')
                            ->schema([

                                // PILIH PRODUK
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(Product::pluck('name', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = Product::with(['sizes', 'addons'])->find($state);
                                        if ($product) {
                                            $set('unit_price', $product->price_per_unit ?? 0);
                                            $set('calculation_type', $product->calculation_type ?? 'quantity');
                                            $set('quantity', 1);
                                            $set('subtotal', $product->price_per_unit ?? 0); // ganti total_price → subtotal
                                            $set('available_sizes', $product->sizes->map(fn($s) => [
                                                'id' => $s->id,
                                                'name' => $s->name,
                                                'unit' => $s->unit,
                                                'price' => $s->price,
                                            ])->toArray());
                                            $set('available_addons', $product->addons->map(fn($a) => [
                                                'id' => $a->id,
                                                'name' => $a->name,
                                                'price' => $a->price,
                                            ])->toArray());
                                        } else {
                                            $set('unit_price', 0);
                                            $set('subtotal', 0);
                                            $set('available_sizes', []);
                                            $set('available_addons', []);
                                        }
                                    }),

                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->step(1)
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $price = $get('unit_price') ?? 0;
                                        $addons = $get('addons') ?? [];
                                        $availableAddons = $get('available_addons') ?? [];
                                        $addonTotal = collect($availableAddons)
                                            ->whereIn('id', $addons)
                                            ->sum('price');

                                        $set('subtotal', ($price * ($state ?? 0)) + $addonTotal); // hitung subtotal
                                    }),

                                // ... kode panjang lainnya tetap sama, tapi semua $set('total_price', ...) ubah jadi $set('subtotal', ...)

                                TextInput::make('subtotal')
                                    ->label('Subtotal Item')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true) // wajib agar ikut disimpan ke DB
                                    ->default(0),
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // hitung subtotal semua item
                                $subtotal = collect($state ?? [])->sum(fn($item) => $item['subtotal'] ?? 0);
                                $set('subtotal', $subtotal);
                                $set('total_price', $subtotal);
                            })
                            ->defaultItems(1)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Barang'),


                        // SUBTOTAL
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true),


                        // DISKON
                        TextInput::make('discount')
                            ->label('Diskon (Rp)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $subtotal = $get('subtotal') ?? 0;
                                $discount = $state ?? 0;
                                $set('total_price', max(0, $subtotal - $discount));
                            }),

                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(2),
                        // TOTAL
                        TextInput::make('total_price')
                            ->label('Total Akhir')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                SelectColumn::make('status_id')
                    ->label('Status')
                    ->options(OrderStatus::pluck('name', 'id'))
                    ->afterStateUpdated(fn($record, $state) => $record->update(['status_id' => $state]))
                    ->extraAttributes(function ($record) {
                        $status = $record->status?->name;
                        return match ($status) {
                            'Pending' => ['class' => 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100 font-semibold rounded-lg'],
                            'Processing' => ['class' => 'bg-blue-200 text-blue-800 dark:bg-blue-700 dark:text-blue-100 font-semibold rounded-lg'],
                            'Completed' => ['class' => 'bg-green-200 text-green-800 dark:bg-green-700 dark:text-green-100 font-semibold rounded-lg'],
                            'Cancelled' => ['class' => 'bg-red-200 text-red-800 dark:bg-red-700 dark:text-red-100 font-semibold rounded-lg'],
                            default => ['class' => 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100'],
                        };
                    }),

                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR', true),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
