<?php

namespace App\Filament\Resources\MigrasiDataResource\Pages;

use App\Filament\Resources\MigrasiDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMigrasiData extends EditRecord
{
    protected static string $resource = MigrasiDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
