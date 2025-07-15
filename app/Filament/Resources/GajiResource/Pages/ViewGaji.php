<?php

namespace App\Filament\Resources\GajiResource\Pages;

use App\Filament\Resources\GajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGaji extends ViewRecord
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}