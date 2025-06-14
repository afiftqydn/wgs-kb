<?php

namespace App\Filament\Resources\PomigorDepotResource\Pages;

use App\Filament\Resources\PomigorDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPomigorDepot extends EditRecord
{
    protected static string $resource = PomigorDepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
