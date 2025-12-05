<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderRekapResource\Pages;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;

class OrderRecapResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Rekap Pesanan';

    public static function table(Table $table): Table
    {
        $statusMap = OrderStatus::pluck('id', 'name')->toArray();
        $selesaiId = $statusMap['Selesai'] ?? null;
        $batalId   = $statusMap['Batal'] ?? null;

        return $table
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->options(OrderStatus::whereIn('name', ['Selesai', 'Batal'])
                        ->pluck('name', 'id'))
                    ->label('Filter Status'),
            ])

            ->query(
                Order::query()
                    ->whereIn('status_id', array_filter([$selesaiId, $batalId]))
            )
            ->columns([
                TextColumn::make("status.name")
                    ->label("Status")
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Selesai' => 'success',
                        'Batal' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make("customer.name")
                    ->label("Customer")
                    ->searchable(),

                TextColumn::make("order_date")
                    ->label("Tanggal")
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make("total_price")
                    ->label("Total")
                    ->money('idr')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
