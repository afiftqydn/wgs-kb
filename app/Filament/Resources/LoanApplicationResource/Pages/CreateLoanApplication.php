<?php

namespace App\Filament\Resources\LoanApplicationResource\Pages;

use App\Filament\Resources\LoanApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoanApplication extends CreateRecord
{
    protected static string $resource = LoanApplicationResource::class;
    protected function getRedirectUrl(): string
    {
        // Mengambil URL dari halaman 'index' resource ini secara dinamis
        return static::getResource()::getUrl('index');
    }
}
