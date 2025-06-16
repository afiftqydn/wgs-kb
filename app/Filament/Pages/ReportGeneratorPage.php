<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\LoanApplication;
use Illuminate\Support\Collection; // Untuk tipe data hasil


class ReportGeneratorPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static string $view = 'filament.pages.report-generator-page';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Generator Laporan';
    protected static ?int $navigationSort = 11;
   

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $status = null;
    public ?int $productTypeId = null;
    public ?int $regionId = null;
    public ?Collection $reportData = null; 
    public bool $showReport = false;

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ]);

        $query = LoanApplication::query()->with(['customer', 'productType', 'inputRegion', 'assignee']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->productTypeId) {
            $query->where('product_type_id', $this->productTypeId);
        }

        $this->reportData = $query->get();
        $this->showReport = true; 

    }
}
