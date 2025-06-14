<?php

namespace App\Filament\Resources\PomigorDepotResource\Pages;

use App\Filament\Resources\PomigorDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPomigorDepots extends ListRecords
{
    protected static string $resource = PomigorDepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Depot Baru')
                ->icon('heroicon-s-plus-circle')
                ->color('success'),
        ];
    }
}
