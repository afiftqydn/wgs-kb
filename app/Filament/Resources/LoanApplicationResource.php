<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Region;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductType;
use App\Models\LoanApplication;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get; // Untuk reactive form
use Filament\Forms\Set; // Untuk reactive form
use App\Filament\Resources\LoanApplicationResource\Pages;
use Illuminate\Support\Facades\Storage; // Untuk file upload
use App\Filament\Resources\LoanApplicationResource\RelationManagers;

class LoanApplicationResource extends Resource
{
    protected static ?string $model = LoanApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Nasabah & Permohonan';
    protected static ?int $navigationSort = 2; // Urutan di navigasi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([ // Menggunakan Wizard untuk form yang panjang
                    Forms\Components\Wizard\Step::make('Informasi Permohonan')
                        ->schema([
                            Forms\Components\TextInput::make('application_number')
                                ->label('Nomor Permohonan')
                                ->disabled() // Di-generate otomatis oleh model
                                ->helperText('Akan ter-generate otomatis setelah disimpan.'),
                            Forms\Components\Select::make('customer_id')
                                ->label('Nasabah')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm(CustomerResource::getFormSchema()) // Izinkan buat nasabah baru dari sini
                                ->createOptionAction(fn(Forms\Components\Actions\Action $action) => $action->modalWidth('5xl')),
                            Forms\Components\Select::make('product_type_id')
                                ->label('Jenis Produk Pembiayaan')
                                ->relationship('productType', 'name')
                                ->searchable()
                                ->preload()
                                ->live() // Agar reaktif
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    // Bisa digunakan untuk menampilkan required_documents dari produk yang dipilih
                                    // atau mengisi min/max amount jika perlu
                                    $product = ProductType::find($state);
                                    if ($product) {
                                        $set('amount_requested', $product->min_amount); // Contoh: set default amount
                                        // Anda bisa menyimpan $product->required_documents ke state lain untuk ditampilkan
                                    }
                                })
                                ->required(),
                            Forms\Components\TextInput::make('amount_requested')
                                ->label('Jumlah Diminta (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->minValue(0),
                            Forms\Components\Textarea::make('purpose')
                                ->label('Tujuan Pembiayaan')
                                ->columnSpanFull()
                                ->nullable(),
                            Forms\Components\Select::make('input_region_id')
                                ->label('Wilayah Input')
                                ->relationship('inputRegion', 'name')
                                ->default(fn() => Auth::user()->region_id) // Default ke region user login
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->label('Status Awal')
                                ->options([
                                    'DRAFT' => 'Draft (Simpan Sementara)',
                                    'SUBMITTED' => 'Submitted (Ajukan Permohonan)',
                                ])
                                ->default('DRAFT')
                                ->required()
                                ->helperText('Pilih "Submitted" untuk langsung mengajukan permohonan.'),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Unggah Dokumen Pendukung')
                        ->schema([
                            Repeater::make('documents') // Menggunakan Repeater untuk multiple documents
                                ->label('Dokumen Pendukung')
                                ->relationship() // Menggunakan relasi 'documents()' di model LoanApplication
                                ->schema([
                                    Forms\Components\Select::make('document_type')
                                        ->label('Jenis Dokumen')
                                        // Idealnya, opsi ini dinamis berdasarkan product_type_id yang dipilih
                                        // Untuk sekarang, kita buat statis atau bisa diisi manual
                                        ->options(function (Get $get): array {
                                            $productTypeId = $get('../../product_type_id'); // Ambil product_type_id dari step wizard sebelumnya
                                            if ($productTypeId) {
                                                $product = ProductType::find($productTypeId);
                                                if ($product && !empty($product->required_documents)) {
                                                    $docs = $product->required_documents->toArray(); // Asumsikan required_documents adalah array
                                                    return array_combine($docs, $docs);
                                                }
                                            }
                                            return [ // Opsi default jika tidak ada atau produk belum dipilih
                                                'KTP' => 'KTP',
                                                'NPWP' => 'NPWP',
                                                'Surat Keterangan Usaha' => 'Surat Keterangan Usaha',
                                                'Slip Gaji' => 'Slip Gaji',
                                                'Lainnya' => 'Dokumen Lainnya',
                                            ];
                                        })
                                        ->required(),
                                    FileUpload::make('file_path')
                                        ->label('File Dokumen')
                                        ->disk('public') // Simpan ke disk 'public' (cek config/filesystems.php)
                                        ->directory('application-documents') // Buat folder di dalam disk public
                                        ->required()
                                        ->preserveFilenames()
                                        // Menyimpan info tambahan saat file diunggah
                                        ->storeFileNamesIn('file_name') // Nama kolom untuk menyimpan nama asli file
                                        ->maxSize(5120) // Ukuran maksimal 5MB
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']), // Tipe file yang diterima
                                    // file_size dan mime_type bisa diisi otomatis menggunakan model event di ApplicationDocument
                                ])
                                ->columnSpanFull()
                                ->addActionLabel('Tambah Dokumen')
                                ->collapsible()
                                ->defaultItems(1), // Default 1 item dokumen saat form dibuka
                        ]),
                ])->columnSpanFull(), // Wizard mengambil span penuh
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('application_number')
                    ->label('No. Permohonan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Nama Nasabah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productType.name')
                    ->label('Jenis Produk')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('amount_requested')
                    ->label('Jumlah Diminta')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'DRAFT' => 'gray',
                        'SUBMITTED' => 'info',
                        'UNDER_REVIEW' => 'warning',
                        'ESCALATED' => 'primary',
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Ditugaskan Ke')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum Ditugaskan')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'SUBMITTED' => 'Submitted',
                        'UNDER_REVIEW' => 'Under Review',
                        'ESCALATED' => 'Escalated',
                        'APPROVED' => 'Approved',
                        'REJECTED' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('product_type_id')
                    ->label('Jenis Produk')
                    ->relationship('productType', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Tambahkan ViewAction
                Tables\Actions\EditAction::make()->visible(fn(LoanApplication $record) => $record->status === 'DRAFT'), // Hanya bisa edit jika DRAFT
                Tables\Actions\DeleteAction::make()->visible(fn(LoanApplication $record) => $record->status === 'DRAFT'), // Hanya bisa hapus jika DRAFT
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Untuk mengisi relasi saat data diambil
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'productType', 'creator', 'assignee', 'inputRegion']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentsRelationManager::class, // Akan kita buat nanti jika repeater kurang ideal
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanApplications::route('/'),
            'create' => Pages\CreateLoanApplication::route('/create'),
            'view' => Pages\ViewLoanApplication::route('/{record}'), // Aktifkan jika perlu halaman view khusus
            'edit' => Pages\EditLoanApplication::route('/{record}/edit'),
        ];
    }
}
