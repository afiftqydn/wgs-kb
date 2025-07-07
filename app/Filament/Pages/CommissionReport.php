<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\DB;

class CommissionReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Komisi';
    protected static string $view = 'filament.pages.commission-report';

    public ?array $data = [];
    public array $reportData = [];

    public function mount(): void
    {
        $this->form->fill([
            'report_type' => 'Unit',
            'month' => date('m'),
            'year' => date('Y'),
        ]);
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('report_type')
                ->label('Jenis Laporan')
                ->options([
                    'Unit' => 'Komisi Unit',
                    'Referral' => 'Fee Referral / Marketing',
                ])
                ->required()
                ->live() // Membuat dropdown ini reaktif
                ->afterStateUpdated(fn () => $this->generateReport()), // Memanggil generateReport saat berubah

            Select::make('month')
                ->label('Bulan')
                ->options(collect(range(1, 12))->mapWithKeys(function ($m) {
                    return [str_pad($m, 2, '0', STR_PAD_LEFT) => Carbon::create(null, $m)->monthName];
                })->toArray())
                ->required()
                ->live() // Membuat dropdown ini reaktif
                ->afterStateUpdated(fn () => $this->generateReport()), // Memanggil generateReport saat berubah

            Select::make('year')
                ->label('Tahun')
                ->options(collect(range(date('Y'), date('Y') - 5))->mapWithKeys(function ($y) {
                    return [$y => $y];
                })->toArray())
                ->required()
                ->live() // Membuat dropdown ini reaktif
                ->afterStateUpdated(fn () => $this->generateReport()), // Memanggil generateReport saat berubah
        ])->statePath('data');
    }

    public function generateReport(): void
    {
        $formData = $this->form->getState();
        $startDate = Carbon::createFromDate($formData['year'], $formData['month'], 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Logika untuk memilih query berdasarkan jenis laporan
        if ($formData['report_type'] === 'Unit') {
            $this->generateUnitCommissionReport($startDate, $endDate);
        } elseif ($formData['report_type'] === 'Referral') {
            $this->generateReferralFeeReport($startDate, $endDate);
        } else {
            $this->reportData = [];
        }
    }
    
    protected function generateUnitCommissionReport(Carbon $startDate, Carbon $endDate): void
    {
        // Query untuk Komisi Unit
        $this->reportData = LoanApplication::query()
            ->join('regions', 'loan_applications.input_region_id', '=', 'regions.id')
            ->join('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->join('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->select(
                'regions.name as recipient_name',
                DB::raw('COUNT(DISTINCT loan_applications.id) as total_applications'),
                DB::raw('SUM(CASE WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100) ELSE rules.value END) as total_commission')
            )
            ->where('loan_applications.status', 'APPROVED')
            ->where('rules.recipient_level', 'Unit')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->groupBy('regions.name')
            ->orderBy('total_commission', 'desc')
            ->get()
            ->toArray();
    }

    protected function generateReferralFeeReport(Carbon $startDate, Carbon $endDate): void
    {
        // Query untuk Fee Referral
        $this->reportData = LoanApplication::query()
            ->join('referrers', 'loan_applications.referrer_id', '=', 'referrers.id')
            ->join('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->join('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->select(
                'referrers.name as recipient_name',
                DB::raw('COUNT(DISTINCT loan_applications.id) as total_applications'),
                DB::raw('SUM(CASE WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100) ELSE rules.value END) as total_commission')
            )
            ->where('loan_applications.status', 'APPROVED')
            ->where('rules.recipient_level', 'Referral')
            ->whereNotNull('loan_applications.referrer_id')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->groupBy('referrers.name')
            ->orderBy('total_commission', 'desc')
            ->get()
            ->toArray();
    }
}
