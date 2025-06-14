<?php

namespace App\Filament\Resources\ReferrerResource\Pages;

use App\Filament\Resources\ReferrerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReferrer extends EditRecord
{
    protected static string $resource = ReferrerResource::class;

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
