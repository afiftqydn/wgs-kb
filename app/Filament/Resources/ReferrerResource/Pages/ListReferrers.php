<?php

namespace App\Filament\Resources\ReferrerResource\Pages;

use App\Filament\Resources\ReferrerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReferrers extends ListRecords
{
    protected static string $resource = ReferrerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Pemberi Referensi')
                ->icon('heroicon-s-plus-circle')
                ->color('success'),
        ];
    }
}
