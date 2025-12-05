<?php

namespace App\Filament\Resources\OrderRecapResource\Pages;

use App\Filament\Resources\OrderRecapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderRecap extends EditRecord
{
    protected static string $resource = OrderRecapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
