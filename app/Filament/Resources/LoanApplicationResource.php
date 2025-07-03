<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanApplicationResource\Pages;
use App\Filament\Resources\LoanApplicationResource\RelationManagers;
use App\Filament\Resources\CustomerResource;
use App\Models\LoanApplication;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\Region;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification as FilamentNotification; 

class LoanApplicationResource extends Resource
{
    protected static ?string $model = LoanApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Manajemen Pengajuan'; 
    protected static ?string $navigationLabel = 'Pengajuan Pembiayaan';
    protected static ?string $pluralModelLabel = 'Data Pengajuan Pembiayaan';
    protected static ?string $modelLabel = 'Pengajuan Pembiayaan'; 
    protected static ?int $navigationSort =1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Informasi Permohonan')
                        ->schema([
                            TextInput::make('application_number')
                                ->label('Nomor Permohonan')->disabled()
                                ->helperText('Akan ter-generate otomatis setelah disimpan.'),
                            Select::make('customer_id')
                               ->label('Nasabah')
                                ->relationship(
                                    name: 'customer', 
                                    titleAttribute: 'name',
                                    modifyQueryUsing: function (Builder $query) {
                                        $user = auth()->user();
                                        if (!$user->hasRole(['Tim IT', 'Kepala Cabang', 'Analis Cabang', 'Admin Cabang'])) {

                                            // Jika peran level UNIT
                                            if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
                                                if ($user->region_id) {
                                                    $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                                                    $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                                                    $query->whereIn('region_id', $accessibleRegionIds);
                                                } else {
                                                    $query->whereRaw('1 = 0'); 
                                                }
                                            }
                                            // Jika peran level SUBUNIT
                                            elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
                                                if ($user->region_id) {
                                                    $query->where('region_id', $user->region_id);
                                                } else {
                                                    $query->whereRaw('1 = 0');
                                                }
                                            }
                                            else {
                                                
                                                 $query->whereRaw('1 = 0');
                                            }
                                        }
                                    }
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm(CustomerResource::getCreationFormSchema())
                                ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action->modalWidth('5xl')),
                            Select::make('product_type_id')
                                ->label('Jenis Produk Pembiayaan')->relationship('productType', 'name')
                                ->searchable()->preload()->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    $product = ProductType::find($state);
                                    if($product) {
                                        $set('amount_requested', $product->min_amount);
                                        $set('product_required_documents', $product->required_documents?->toArray() ?: []);
                                    } else {
                                        $set('product_required_documents', []);
                                    }
                                })->required(),
                            TextInput::make('amount_requested')
                                ->label('Jumlah Diminta (Rp)')->numeric()->prefix('Rp')->required()->minValue(0),
                            Textarea::make('purpose')
                                ->label('Tujuan Pembiayaan')->columnSpanFull()->nullable(),
                            Select::make('input_region_id')
                                ->label('Wilayah Input')->relationship('inputRegion', 'name')
                                ->default(fn () => Auth::check() ? Auth::user()->region_id : null) // Cek Auth::check()
                                ->searchable()->preload()->required(),
                            Select::make('status')
                                ->label('Status Awal')
                                ->options(['DRAFT' => 'Draft (Simpan Sementara)', 'SUBMITTED' => 'Submitted (Ajukan Permohonan)'])
                                ->default('DRAFT')->required()
                                ->helperText('Pilih "Submitted" untuk langsung mengajukan permohonan.'),
                            Forms\Components\Hidden::make('product_required_documents')->dehydrated(false),
                        ])->columns(2),
                    
                    Wizard\Step::make('Unggah Dokumen Pendukung')
                        ->schema([
                            Repeater::make('documents')
                                ->label('Dokumen Pendukung')->relationship()
                                ->schema([
                                    Select::make('document_type')->label('Jenis Dokumen')
                                        ->options(function (Get $get): array {
                                            $requiredDocs = $get('../../product_required_documents');
                                            if (!empty($requiredDocs) && is_array($requiredDocs)) {
                                                return array_combine($requiredDocs, $requiredDocs);
                                            }
                                            return ['KTP' => 'KTP', 'NPWP' => 'NPWP', 'Lainnya' => 'Dokumen Lainnya'];
                                        })->required()->searchable(),
                                    FileUpload::make('file_path')->label('File Dokumen')->disk('public')
                                        ->directory('application-documents')->required()->preserveFilenames()
                                        ->storeFileNamesIn('file_name')->maxSize(5120) // 5MB
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                                ])->columnSpanFull()->addActionLabel('Tambah Dokumen')->collapsible()->defaultItems(1),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('application_number')->label('No. Permohonan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Nama Nasabah')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('productType.name')->label('Jenis Produk')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('amount_requested')->label('Jumlah Diminta')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT' => 'gray', 'SUBMITTED' => 'info', 'UNDER_REVIEW' => 'warning',
                        'ESCALATED' => 'primary', 'APPROVED' => 'success', 'REJECTED' => 'danger',
                        'CANCELLED' => 'danger', default => 'gray',
                    })->searchable()->sortable(),
                Tables\Columns\TextColumn::make('creator.name')->label('Diinput Oleh')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignee.name')->label('Ditugaskan Ke')->searchable()->sortable()->placeholder('Belum Ditugaskan')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft', 'SUBMITTED' => 'Submitted', 'UNDER_REVIEW' => 'Under Review',
                        'ESCALATED' => 'Escalated', 'APPROVED' => 'Approved', 'REJECTED' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('product_type_id')->label('Jenis Produk')
                    ->relationship('productType', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->visible(),
                Tables\Actions\EditAction::make()->visible(fn (LoanApplication $record): bool => $record->status === 'DRAFT'), 

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // 1. Jika pengguna adalah Tim IT, Kepala Cabang, atau peran global lainnya, tampilkan semua data.
        if ($user->hasRole(['Tim IT', 'Kepala Cabang', 'Analis Cabang', 'Admin Cabang'])) {
            return parent::getEloquentQuery()->with(['customer', 'productType', 'creator', 'assignee', 'inputRegion']);
        }

        // 2. Jika pengguna berada di level UNIT (Kepala Unit, Analis Unit, Admin Unit)
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                // Ambil ID dari semua SubUnit yang berada di bawah Unit pengguna ini
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');

                // Gabungkan ID Unit pengguna dengan ID semua SubUnit di bawahnya
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);

                // Tampilkan permohonan yang input_region_id-nya ada di dalam daftar wilayah yang bisa diakses
                return parent::getEloquentQuery()
                    ->whereIn('input_region_id', $accessibleRegionIds)
                    ->with(['customer', 'productType', 'creator', 'assignee', 'inputRegion']);
            }
        }

        // 3. Jika pengguna berada di level SUBUNIT (Kepala SubUnit, Admin SubUnit)
        if ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                // Hanya tampilkan permohonan yang input_region_id-nya sama dengan region_id pengguna
                return parent::getEloquentQuery()
                    ->where('input_region_id', $user->region_id)
                    ->with(['customer', 'productType', 'creator', 'assignee', 'inputRegion']);
            }
        }

        // 4. Jika pengguna adalah Manager Keuangan (contoh peran global lain)
        // Anda bisa memutuskan apakah mereka bisa melihat semua atau tidak sama sekali.
        // if ($user->hasRole('Manager Keuangan')) {
        //     return parent::getEloquentQuery()->with([...]); // Contoh jika bisa lihat semua
        // }

        // 5. Fallback: Jika peran tidak cocok dengan kondisi di atas, jangan tampilkan apa pun
        // Ini lebih aman daripada menampilkan semua data secara tidak sengaja.
        return parent::getEloquentQuery()->whereRaw('1 = 0'); // Query yang selalu mengembalikan hasil kosong
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\WorkflowsRelationManager::class, 
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanApplications::route('/'),
            'create' => Pages\CreateLoanApplication::route('/create'),
            'view' => Pages\ViewLoanApplication::route('/{record}'),
            'edit' => Pages\EditLoanApplication::route('/{record}/edit'),
        ];
    }    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

}