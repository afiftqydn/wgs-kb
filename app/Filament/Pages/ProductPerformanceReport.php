<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class ProductPerformanceReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.product-performance-report';
    protected static ?string $title = 'Laporan Kinerja Produk';

    // 1. Ganti properti individu dengan satu array data
    public ?array $data = [];
    
    // Properti untuk menyimpan hasil laporan
    public array $reportData = [];

    public function mount(): void
    {
        // 2. Isi form dengan nilai default menggunakan statePath 'data'
        $this->form->fill([
            'month' => date('m'),
            'year' => date('Y'),
        ]);
        
        // Panggil generateReportData setelah form diisi
        $this->generateReportData();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('month')
                ->label('Bulan')
                ->options(collect(range(1, 12))->mapWithKeys(function ($m) {
                    return [str_pad($m, 2, '0', STR_PAD_LEFT) => Carbon::create(null, $m)->monthName];
                })->toArray())
                ->required(),
            Select::make('year')
                ->label('Tahun')
                ->options(collect(range(date('Y'), date('Y') - 5))->mapWithKeys(function ($y) {
                    return [$y => $y];
                })->toArray())
                ->required(),
        ])->statePath('data'); // State path sudah benar
    }

    public function generateReportData(): void
    {
        // 3. Ambil data dari state form yang sudah terikat
        $formData = $this->form->getState();
        
        $startDate = Carbon::createFromDate($formData['year'], $formData['month'], 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $this->reportData = LoanApplication::query()
            ->join('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->select(
                'product_types.name as product_name',
                DB::raw('COUNT(loan_applications.id) as total_applications'),
                DB::raw('SUM(CASE WHEN loan_applications.status = "APPROVED" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN loan_applications.status = "APPROVED" THEN loan_applications.amount_requested ELSE 0 END) as total_approved_value')
            )
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->groupBy('product_types.name')
            ->orderBy('total_applications', 'desc')
            ->get()
            ->toArray();
    }
    
    public function submit(): void
    {
        // 4. Cukup panggil generateReportData, karena data sudah terikat
        $this->generateReportData();
    }
}
