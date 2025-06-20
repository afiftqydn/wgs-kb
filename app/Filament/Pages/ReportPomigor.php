<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable; // <-- Import
use Filament\Tables\Concerns\InteractsWithTable; // <-- Import
use Filament\Forms\Form;
use Filament\Tables\Table; // <-- Import
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\Region;
use App\Models\PomigorDepot;
use App\Models\PomigorStockMovement; // <-- Import untuk query
use Filament\Tables\Columns\TextColumn; // <-- Import untuk kolom tabel
use Illuminate\Database\Eloquent\Builder; // <-- Import untuk query
use Illuminate\Support\Facades\Auth; // <-- Import jika menggunakan batasan wilayah

class ReportPomigor extends Page implements HasForms, HasTable // <-- Implementasikan HasTable
{
    use InteractsWithForms, InteractsWithTable; // <-- Gunakan Trait InteractsWithTable

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.report-pomigor';
    protected static ?string $navigationGroup = 'Manajemen POMIGOR';
    protected static ?string $navigationLabel = 'Laporan POMIGOR';
    protected static ?string $title = 'Generator Laporan POMIGOR';

    public ?array $data = []; 
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $regionId = null;
    public ?string $depotId = null;
    public ?string $transactionType = null;

    public array $reportData = [];
    public bool $showReport = false;

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
            'regionId' => null,
            'depotId' => null,
            'transactionType' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')->label('Tanggal Awal')->required()->reactive(), // Jadikan reaktif
                DatePicker::make('endDate')->label('Tanggal Akhir')->required()->reactive(),
                Select::make('regionId')->label('Wilayah Unit')
                    ->options(Region::where('type', 'UNIT')->pluck('name', 'id'))
                    ->searchable()
                    ->live() // live() akan memicu update reaktif
                    ->afterStateUpdated(fn (Set $set) => $set('depotId', null)),
                Select::make('depotId')->label('Depot Spesifik')
                    ->options(function (Get $get) {
                        $unitId = $get('regionId');
                        if (!$unitId) { return PomigorDepot::all()->pluck('name', 'id'); }
                        return PomigorDepot::where('region_id', $unitId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive(),
                Select::make('transactionType')->label('Jenis Transaksi')
                    ->options([
                        'REFILL' => 'Pengisian Ulang (Refill)',
                        'SALE_REPORTED' => 'Laporan Penjualan',
                        'ADJUSTMENT_INCREASE' => 'Penyesuaian Penambahan',
                        'ADJUSTMENT_DECREASE' => 'Penyesuaian Pengurangan',
                    ])
                    ->reactive(),
            ])
            ->statePath('data');
    }

        public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $filters = $this->form->getState();
                $query = PomigorStockMovement::query()
                    ->with(['pomigorDepot.region', 'recorder']);

                // Terapkan filter tanggal
                if (!empty($filters['startDate'])) {
                    $query->whereDate('transaction_date', '>=', $filters['startDate']);
                }
                if (!empty($filters['endDate'])) {
                    $query->whereDate('transaction_date', '<=', $filters['endDate']);
                }
                
                // Terapkan filter jenis transaksi
                if (!empty($filters['transactionType'])) {
                    $query->where('transaction_type', $filters['transactionType']);
                }

                // Terapkan filter depot atau wilayah
                if (!empty($filters['depotId'])) {
                    $query->where('pomigor_depot_id', $filters['depotId']);
                } elseif (!empty($filters['regionId'])) {
                    $query->whereHas('pomigorDepot', function (Builder $q) use ($filters) {
                        $q->where('region_id', $filters['regionId']);
                    });
                }
                
                // Terapkan batasan wilayah berdasarkan peran pengguna
                $user = Auth::user();
                if ($user->hasAnyRole(['Kepala Unit', 'Admin Unit', 'Analis Unit'])) {
                    $query->whereHas('pomigorDepot', function (Builder $q) use ($user) {
                        $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                        $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                        $q->whereIn('region_id', $accessibleRegionIds);
                    });
                }

                return $query;
            })
            ->columns([
                TextColumn::make('transaction_date')->label('Tgl Transaksi')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('pomigorDepot.name')->label('Nama Depot')->searchable()->sortable(),
                TextColumn::make('pomigorDepot.region.name')->label('Unit')->searchable()->sortable(),
                TextColumn::make('transaction_type')->label('Jenis Transaksi')->badge()->color(fn (string $state): string => match ($state) {
                    'REFILL' => 'success',
                    'SALE_REPORTED' => 'danger',
                    'ADJUSTMENT_INCREASE' => 'info',
                    'ADJUSTMENT_DECREASE' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('quantity_liters')->label('Jumlah (Liter)')->numeric()->alignRight()->sortable(),
                TextColumn::make('recorder.name')->label('Dicatat Oleh')->searchable()->sortable(),
            ])
            ->defaultSort('transaction_date', 'desc');
    }



    public function generateReport()
    {
        // ... (logika generate laporan)
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('view_report::generator::page'); 
    }
}