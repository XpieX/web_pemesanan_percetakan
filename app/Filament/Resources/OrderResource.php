<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\ProductSize;
use App\Models\Addon;
use CodeWithDennis\SimpleAlert\Components\Infolists\SimpleAlert;
use Filament\Tables\Actions\Action;
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
use Filament\Notifications\Notification;
use Filament\Tables\Enums\ActionsPosition;

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

                                // === PILIH PRODUK ===
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(Product::pluck('name', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Ambil produk baru
                                        $product = Product::with(['sizes', 'addons'])->find($state);

                                        // Reset item fields (set use_preset_size LAST agar visibility re-eval benar)
                                        $set('product_size_id', null);
                                        $set('length', null);
                                        $set('width', null);
                                        $set('addons', []);
                                        $set('quantity', 1);
                                        $set('subtotal', 0);

                                        if ($product) {
                                            $set('unit_price', $product->price_per_unit ?? 0);
                                            $set('calculation_type', $product->calculation_type ?? 'quantity');
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
                                            $set('subtotal', $product->price_per_unit ?? 0);
                                        } else {
                                            $set('unit_price', 0);
                                            $set('subtotal', 0);
                                            $set('available_sizes', []);
                                            $set('available_addons', []);
                                        }

                                        // Trigger visibility re-eval terakhir
                                        $set('use_preset_size', false);

                                        // update parent subtotal
                                        $items = $get('../../items') ?? [];
                                        $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                        $set('../../subtotal', $parent);
                                        $set('../../total_price', $parent);
                                    }),

                                // === HARGA SATUAN ===
                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required(),

                                // === QUANTITY ===
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->live(debounce: 200)
                                    ->visible(fn($get) => $get('calculation_type') === 'quantity')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $quantity = max(1, $state ?? 1);
                                        $addons = $get('addons') ?? [];
                                        $availableAddons = $get('available_addons') ?? [];

                                        $addonTotalPerUnit = collect($availableAddons)
                                            ->whereIn('id', $addons)
                                            ->sum('price');

                                        // Total = (harga satuan + harga addon per unit) * jumlah
                                        $subtotal = ($unitPrice + $addonTotalPerUnit) * $quantity;
                                        $set('subtotal', $subtotal);

                                        // update parent subtotal
                                        $items = $get('../../items') ?? [];
                                        $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                        $set('../../subtotal', $parent);
                                        $set('../../total_price', $parent);
                                    }),

                                // === INPUT PANJANG & LEBAR (untuk area manual) ===
                                Grid::make(2)
                                    ->visible(fn($get) =>
                                    $get('calculation_type') === 'area' &&
                                        !$get('use_preset_size'))
                                    ->schema(
                                        [
                                            TextInput::make('length')
                                                ->label('Panjang')
                                                ->numeric()
                                                ->live(debounce: 200)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    // jika preset aktif, abaikan
                                                    if ($get('use_preset_size')) return;

                                                    $price = $get('unit_price') ?? 0;
                                                    $length = $get('length') ?? 0;
                                                    $width = $get('width') ?? 0;
                                                    $availableAddons = $get('available_addons') ?? [];
                                                    $addons = $get('addons') ?? [];

                                                    // Addon untuk area dianggap flat (sekali)
                                                    $addonFlat = collect($availableAddons)
                                                        ->whereIn('id', $addons)
                                                        ->sum('price');

                                                    $subtotal = ($price * $length * $width) + $addonFlat;
                                                    $set('subtotal', $subtotal);

                                                    // update parent subtotal
                                                    $items = $get('../../items') ?? [];
                                                    $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                                    $set('../../subtotal', $parent);
                                                    $set('../../total_price', $parent);
                                                }),

                                            TextInput::make('width')
                                                ->label('Lebar')
                                                ->numeric()
                                                ->live(debounce: 200)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    if ($get('use_preset_size')) return;

                                                    $price = $get('unit_price') ?? 0;
                                                    $length = $get('length') ?? 0;
                                                    $width = $get('width') ?? 0;
                                                    $availableAddons = $get('available_addons') ?? [];
                                                    $addons = $get('addons') ?? [];

                                                    $addonFlat = collect($availableAddons)
                                                        ->whereIn('id', $addons)
                                                        ->sum('price');

                                                    $subtotal = ($price * $length * $width) + $addonFlat;
                                                    $set('subtotal', $subtotal);

                                                    // update parent subtotal
                                                    $items = $get('../../items') ?? [];
                                                    $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                                    $set('../../subtotal', $parent);
                                                    $set('../../total_price', $parent);
                                                })
                                        ],
                                    ),

                                // === TOGGLE GUNAKAN PRESET UKURAN ===
                                Toggle::make('use_preset_size')
                                    ->label('Gunakan ukuran yang sudah ada')
                                    ->visible(fn($get) => !empty($get('available_sizes')))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            // ON: pakai preset — biarkan product_size_id boleh diisi oleh user
                                            // jangan otomatis set product_size_id null
                                            $set('length', null);
                                            $set('width', null);
                                        } else {
                                            // OFF: kembali ke custom size, kosongkan product_size_id agar tidak tersimpan
                                            $set('product_size_id', null);
                                        }

                                        // recalc subtotal parent
                                        $items = $get('../../items') ?? [];
                                        $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                        $set('../../subtotal', $parent);
                                        $set('../../total_price', $parent);
                                    }),


                                // === PILIH UKURAN PRESET ===
                                Select::make('product_size_id')
                                    ->label('Pilih Ukuran')
                                    ->visible(fn($get) => $get('use_preset_size') && !empty($get('available_sizes')))
                                    ->options(fn($get) => collect($get('available_sizes') ?? [])->mapWithKeys(
                                        fn($s) => [$s['id'] => "{$s['name']} ({$s['unit']}) - Rp{$s['price']}"]
                                    ))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $sizes = $get('available_sizes') ?? [];
                                        $selected = collect($sizes)->firstWhere('id', $state);
                                        if (!$selected) return;

                                        $price = $selected['price'];
                                        // Set harga satuan ke harga preset (treated as FLAT)
                                        $set('unit_price', $price);
                                        // Pastikan length/width tidak mengganggu
                                        $set('length', null);
                                        $set('width', null);

                                        $availableAddons = $get('available_addons') ?? [];
                                        $addons = $get('addons') ?? [];
                                        $addonFlat = collect($availableAddons)->whereIn('id', $addons)->sum('price');

                                        if ($get('calculation_type') === 'area') {
                                            // A: preset treated as final flat price
                                            $subtotal = $price + $addonFlat;
                                        } else {
                                            $quantity = $get('quantity') ?? 1;
                                            $addonPerUnit = collect($availableAddons)->whereIn('id', $addons)->sum('price');
                                            $subtotal = ($price + $addonPerUnit) * $quantity;
                                        }

                                        $set('subtotal', $subtotal);

                                        // update parent subtotal
                                        $items = $get('../../items') ?? [];
                                        $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                        $set('../../subtotal', $parent);
                                        $set('../../total_price', $parent);
                                    }),

                                // === ADDONS ===
                                Select::make('addons')
                                    ->label('Addon Tambahan')
                                    ->visible(fn($get) => !empty($get('available_addons')))
                                    ->multiple()
                                    ->options(fn($get) => collect($get('available_addons') ?? [])->mapWithKeys(
                                        fn($a) => [$a['id'] => "{$a['name']} (+Rp{$a['price']})"]
                                    ))
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $calcType = $get('calculation_type');
                                        $availableAddons = $get('available_addons') ?? [];
                                        $addonTotal = collect($availableAddons)
                                            ->whereIn('id', $state ?? [])
                                            ->sum('price');

                                        $price = $get('unit_price') ?? 0;
                                        $quantity = $get('quantity') ?? 1;

                                        if ($calcType === 'quantity') {
                                            // addons considered per-unit
                                            $subtotal = ($price + $addonTotal) * $quantity;
                                        } else {
                                            if ($get('use_preset_size')) {
                                                // preset for area -> flat price + addon (addon flat)
                                                $subtotal = $price + $addonTotal;
                                            } else {
                                                $length = $get('length') ?? 0;
                                                $width = $get('width') ?? 0;
                                                $subtotal = ($price * $length * $width) + $addonTotal;
                                            }
                                        }

                                        $set('subtotal', $subtotal);

                                        // update parent subtotal
                                        $items = $get('../../items') ?? [];
                                        $parent = collect($items)->sum(fn($i) => $i['subtotal'] ?? 0);
                                        $set('../../subtotal', $parent);
                                        $set('../../total_price', $parent);
                                    }),

                                // === SUBTOTAL ITEM ===
                                TextInput::make('subtotal')
                                    ->label('Subtotal Item')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),

                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Update subtotal pesanan (parent) setiap ada perubahan di repeater structure
                                $subtotalAkhir = collect($state ?? [])->sum(fn($item) => $item['subtotal'] ?? 0);
                                $set('../../subtotal', $subtotalAkhir);
                                $set('../../total_price', $subtotalAkhir);
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
                            ->formatStateUsing(fn($state) => (float) $state)
                            ->dehydrated(true),

                        // DISKON
                        TextInput::make('discount')
                            ->label('Diskon (Rp)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->formatStateUsing(fn($state) => (float) $state)
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
                            ->formatStateUsing(fn($state) => (int) $state)

                            ->disabled()
                            ->dehydrated(true),

                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        // ambil map status name => id sekali
        $statusMap = OrderStatus::pluck('id', 'name')->toArray();
        $selesaiId = $statusMap['Selesai'] ?? null;
        $batalId   = $statusMap['Batal'] ?? null;

        $query = Order::query();
        if ($batalId !== null) {
            // hide yang batal
            $query->where('status_id', '!=', $batalId);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make("status.name")
                    ->label("Status")
                    ->color(fn($state) => match ($state) {
                        'Selesai' => 'success',
                        'Proses'  => 'warning',
                        'Batal'   => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make("no")->label("No")->rowIndex(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal Pemesanan')
                    ->date('l, d M Y')
                    ->sortable()
                    ->searchable(),

                // ---------------------------
                // ORDER ITEMS (PRODUCTS)
                // ---------------------------
                Tables\Columns\TextColumn::make('items.product.name')
                    ->label('Nama Barang')
                    ->listWithLineBreaks()      // tampil per baris
                    ->limitList(10),            // optional

                TextColumn::make('orderItems')
                    ->label('Ukuran')
                    ->getStateUsing(function ($record) {

                        if (!$record->orderItems || $record->orderItems->isEmpty()) {
                            return ['-'];
                        }

                        return $record->orderItems->map(function ($item) {

                            $width  = (int) $item->width;
                            $length = (int) $item->length;

                            if (empty($width) || empty($length)) {
                                return $item->productSize->name ?? '-';
                            }

                            return "{$width} x {$length} cm";
                        })->toArray(); // ← penting: jadikan array, bukan string

                    })
                    ->separator("\n")         // ← setiap item dipisah newline
                    ->listWithLineBreaks()    // ← Filament render newline sebagai <br>
                    ->extraAttributes(['class' => 'text-center'])
                    ->wrap()->alignment('center'),

                Tables\Columns\TextColumn::make('items.quantity')
                    ->label('Qty')
                    ->numeric()
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('items.subtotal')
                    ->label('Subtotal')
                    ->money('idr')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Diskon')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('idr')
                    ->listWithLineBreaks(),
            ])
            ->actions([

                Tables\Actions\ActionGroup::make([
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->url(fn($record) => route('orders.print', $record))
                        ->openUrlInNewTab(),

                    Action::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-pencil')
                        ->visible(fn($record) => ! in_array($record->status_id, array_filter([$selesaiId, $batalId])))
                        ->form([
                            Forms\Components\Select::make('status_id')
                                ->label('Pilih Status')
                                ->options(OrderStatus::pluck('name', 'id')->toArray()),
                        ])
                        ->modalHeading('Ubah Status Pesanan')
                        ->modalSubmitActionLabel('Ubah')
                        ->requiresConfirmation()
                        ->action(function (array $data, $record, $livewire) use ($selesaiId, $batalId) {
                            $new = $data['status_id'];
                            $record->update(['status_id' => $new]);

                            Notification::make()
                                ->title('Status Diubah')
                                ->success()
                                ->send();

                            $livewire->dispatch('refreshTable');
                        }),
                ])
                    ->icon('heroicon-o-ellipsis-vertical') // ikon group
                    ->label('Actions')
            ], position: ActionsPosition::BeforeColumns)
            // ⬅️ membuat action pindah ke kir
            ->defaultSort('id', 'desc');
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
