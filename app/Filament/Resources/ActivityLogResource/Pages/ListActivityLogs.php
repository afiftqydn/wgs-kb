<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\OnlineUsersWidget; 

class ListActivityLogs extends ListRecords
{

    protected function getHeaderWidgets(): array
    {
        return [
            OnlineUsersWidget::class,
        ];
    }


    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
