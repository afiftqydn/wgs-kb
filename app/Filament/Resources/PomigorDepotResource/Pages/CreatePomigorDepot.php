<?php

namespace App\Filament\Resources\PomigorDepotResource\Pages;

use App\Filament\Resources\PomigorDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePomigorDepot extends CreateRecord
{
    protected static string $resource = PomigorDepotResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
