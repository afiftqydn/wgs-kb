<?php

namespace App\Filament\Resources\ReferrerResource\Pages;

use App\Filament\Resources\ReferrerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReferrer extends CreateRecord
{
    protected static string $resource = ReferrerResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
