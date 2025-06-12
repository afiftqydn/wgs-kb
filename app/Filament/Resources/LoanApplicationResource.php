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
use Filament\Notifications\Notification as FilamentNotification; // Untuk toast notifikasi

class LoanApplicationResource extends Resource
{
    protected static ?string $model = LoanApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Nasabah & Permohonan';
    protected static ?int $navigationSort = 2;

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
                                ->label('Nasabah')->relationship('customer', 'name')
                                ->searchable()->preload()->required()
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn (LoanApplication $record) => $record->status === 'DRAFT' && (Auth::user()->can('edit_loan_applications') || $record->created_by === Auth::id())),

                // --- AKSI ALUR KERJA ---
                Action::make('processToAnalyst') // Admin Unit -> Analis Unit
                    ->label('Proses & Teruskan ke Analis')->icon('heroicon-o-arrow-right-circle')->color('info')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        return $record->status === 'SUBMITTED' &&
                               ($user->hasRole('Admin Unit') || $user->can('process_submitted_application_admin_unit')) &&
                               $record->processing_region_id === $user->region_id; // Admin Unit di region proses
                    })
                    ->form([
                        Select::make('assigned_to_analyst')->label('Tugaskan ke Analis Unit')
                            ->options(function (LoanApplication $record) {
                                return User::where('region_id', $record->processing_region_id)
                                    ->whereHas('roles', fn ($query) => $query->where('name', 'Analis Unit'))
                                    ->pluck('name', 'id');
                            })->required()->searchable(),
                        Textarea::make('workflow_notes_admin_unit')->label('Catatan dari Admin Unit')->nullable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_admin_unit'] ?? null);
                        $record->status = 'UNDER_REVIEW';
                        $record->assigned_to = $data['assigned_to_analyst'];
                        $record->save();
                        FilamentNotification::make()->title('Permohonan Diproses')->success()->body('Permohonan ' . $record->application_number . ' telah diteruskan ke Analis Unit.')->send();
                    })->modalHeading('Proses Permohonan ke Analis')->modalButton('Ya, Proses & Teruskan'),

                Action::make('approveNormallyByAnalyst') // Analis Unit -> Approve (Normal)
                    ->label('Setujui (Nominal Normal)')->icon('heroicon-o-hand-thumb-up')->color('success')
                    ->requiresConfirmation()->modalHeading('Setujui Permohonan (Nominal Normal)?')->modalButton('Ya, Setujui & Teruskan ke Kepala Unit Review')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        if (!($record->status === 'UNDER_REVIEW' && ($user->hasRole('Analis Unit') || $user->can('decide_application_analis_unit')) && $record->assigned_to === $user->id)) return false;
                        $product = $record->productType; // Eager load this for performance
                        if ($product && $product->escalation_threshold !== null) return $record->amount_requested <= $product->escalation_threshold;
                        return true;
                    })
                    ->form([
                        Textarea::make('workflow_notes_analis_approve_normal')->label('Catatan Persetujuan Analis (Opsional)')->nullable(),
                        Select::make('assigned_to_kepala_unit_for_review')->label('Teruskan ke Kepala Unit untuk Review')
                            ->options(function (LoanApplication $record) {
                                return User::where('region_id', $record->processing_region_id)
                                    ->whereHas('roles', fn ($query) => $query->where('name', 'Kepala Unit'))
                                    ->pluck('name', 'id');
                            })->required()->searchable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_analis_approve_normal'] ?? 'Permohonan (nominal normal) disetujui oleh Analis Unit.');
                        $record->status = 'APPROVED';
                        $record->assigned_to = $data['assigned_to_kepala_unit_for_review'];
                        $record->save();
                        FilamentNotification::make()->title('Permohonan Disetujui (Analis)')->success()->send();
                    }),

                Action::make('escalateToCabangByAnalyst') // Analis Unit -> Eskalasi
                    ->label('Eskalasi ke Cabang')->icon('heroicon-o-arrow-up-circle')->color('warning')
                    ->requiresConfirmation()->modalHeading('Eskalasi Permohonan ke Cabang?')->modalDescription('Permohonan ini akan diteruskan ke Kepala Cabang karena nominalnya melebihi ambang batas.')->modalButton('Ya, Eskalasi ke Cabang')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        if (!($record->status === 'UNDER_REVIEW' && ($user->hasRole('Analis Unit') || $user->can('escalate_application_analis_unit')) && $record->assigned_to === $user->id)) return false;
                        $product = $record->productType; // Eager load
                        if ($product && $product->escalation_threshold !== null) return $record->amount_requested > $product->escalation_threshold;
                        return false;
                    })
                    ->form([
                        Textarea::make('workflow_notes_analis_escalate')->label('Rekomendasi/Catatan Eskalasi Analis (Wajib)')->required()->minLength(10),
                        Select::make('assigned_to_kepala_cabang')->label('Tugaskan ke Kepala Cabang')
                            ->options(fn () => User::whereHas('roles', fn ($query) => $query->where('name', 'Kepala Cabang'))->pluck('name', 'id'))
                            ->required()->searchable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_analis_escalate']);
                        $record->status = 'ESCALATED';
                        $record->assigned_to = $data['assigned_to_kepala_cabang'];
                        $record->save();
                        FilamentNotification::make()->title('Permohonan Diekskalasi')->info()->body('Permohonan ' . $record->application_number . ' telah dieskalasi ke Kepala Cabang.')->send();
                    }),

                Action::make('rejectApplicationByAnalyst') // Analis Unit -> Tolak
                    ->label('Tolak Permohonan (Analis)')->icon('heroicon-o-hand-thumb-down')->color('danger')
                    ->requiresConfirmation()->modalHeading('Tolak Permohonan Ini?')->modalButton('Ya, Tolak & Teruskan ke Kepala Unit Review')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        return $record->status === 'UNDER_REVIEW' &&
                               ($user->hasRole('Analis Unit') || $user->can('decide_application_analis_unit')) &&
                               $record->assigned_to === $user->id;
                    })
                    ->form([
                        Textarea::make('workflow_notes_analis_reject')->label('Alasan Penolakan Analis (Wajib Diisi)')->required()->minLength(10),
                        Select::make('assigned_to_kepala_unit_for_review')->label('Teruskan ke Kepala Unit untuk Review')
                            ->options(function (LoanApplication $record) {
                                return User::where('region_id', $record->processing_region_id)
                                    ->whereHas('roles', fn ($query) => $query->where('name', 'Kepala Unit'))
                                    ->pluck('name', 'id');
                            })->required()->searchable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_analis_reject']);
                        $record->status = 'REJECTED';
                        $record->assigned_to = $data['assigned_to_kepala_unit_for_review'];
                        $record->save();
                        FilamentNotification::make()->title('Permohonan Ditolak (Analis)')->danger()->send();
                    }),
                
                Action::make('markAsReviewedByKepalaUnit') // Kepala Unit -> Review
                    ->label('Tandai Sudah Direview (Kepala Unit)')->icon('heroicon-o-eye')->color('gray')
                    ->requiresConfirmation()->modalHeading('Tandai Permohonan Sudah Direview?')->modalButton('Ya, Tandai Sudah Direview')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        return in_array($record->status, ['APPROVED', 'REJECTED']) &&
                               ($user->hasRole('Kepala Unit') || $user->can('review_decided_application_kepala_unit')) &&
                               $record->assigned_to === $user->id;
                    })
                    ->form([
                        Textarea::make('workflow_notes_kepala_unit_review')->label('Catatan Review Kepala Unit (Opsional)')->nullable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_kepala_unit_review'] ?? 'Permohonan telah direview oleh Kepala Unit.');
                        $record->assigned_to = null; 
                        $record->save(); 
                        FilamentNotification::make()->title('Permohonan Direview')->info()->body('Permohonan ' . $record->application_number . ' telah direview oleh Kepala Unit.')->send();
                    }),

                Action::make('approveEscalatedApplication') // Kepala Cabang -> Approve Eskalasi
                    ->label('Setujui Permohonan Eskalasi')->icon('heroicon-o-hand-thumb-up')->color('success')
                    ->requiresConfirmation()->modalHeading('Setujui Permohonan Eskalasi Ini?')->modalButton('Ya, Setujui Eskalasi')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        return $record->status === 'ESCALATED' &&
                               ($user->hasRole('Kepala Cabang') || $user->can('decide_escalated_application_kepala_cabang')) &&
                               $record->assigned_to === $user->id;
                    })
                    ->form([
                        Textarea::make('workflow_notes_kacab_approve')->label('Catatan Persetujuan Kepala Cabang (Opsional)')->nullable(),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_kacab_approve'] ?? 'Permohonan eskalasi disetujui oleh Kepala Cabang.');
                        $record->status = 'APPROVED';
                        $record->assigned_to = null; 
                        $record->save();
                        FilamentNotification::make()->title('Eskalasi Disetujui')->success()->send();
                    }),

                Action::make('rejectEscalatedApplication') // Kepala Cabang -> Tolak Eskalasi
                    ->label('Tolak Permohonan Eskalasi')->icon('heroicon-o-hand-thumb-down')->color('danger')
                    ->requiresConfirmation()->modalHeading('Tolak Permohonan Eskalasi Ini?')->modalButton('Ya, Tolak Eskalasi')
                    ->visible(function (LoanApplication $record): bool {
                        $user = Auth::user();
                        return $record->status === 'ESCALATED' &&
                               ($user->hasRole('Kepala Cabang') || $user->can('decide_escalated_application_kepala_cabang')) &&
                               $record->assigned_to === $user->id;
                    })
                    ->form([
                        Textarea::make('workflow_notes_kacab_reject')->label('Alasan Penolakan Kepala Cabang (Wajib)')->required()->minLength(10),
                    ])
                    ->action(function (LoanApplication $record, array $data) {
                        $record->setWorkflowNotes($data['workflow_notes_kacab_reject']);
                        $record->status = 'REJECTED';
                        $record->assigned_to = null;
                        $record->save();
                        FilamentNotification::make()->title('Eskalasi Ditolak')->danger()->send();
                    }),
                
                Tables\Actions\DeleteAction::make()->visible(fn (LoanApplication $record) => $record->status === 'DRAFT' && (Auth::user()->can('delete_loan_applications') || $record->created_by === Auth::id())),
                
                Action::make('printDecisionLetter')
                    ->label('Cetak Surat Keputusan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (LoanApplication $record): string => route('loanApplication.decisionLetter', $record))
                    ->openUrlInNewTab() // Buka PDF di tab baru
                    ->visible(fn (LoanApplication $record): bool => in_array($record->status, ['APPROVED', 'REJECTED'])), // Hanya tampil jika sudah disetujui/ditolak

                // Tables\Actions\DeleteAction::make()->visible(fn (LoanApplication $record) => $record->status === 'DRAFT' /* ... */),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'productType', 'creator', 'assignee', 'inputRegion']);
    }

    public static function getRelations(): array
    {
        return [
            // Aktifkan jika Anda sudah membuatnya dan ingin menggunakannya:
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\WorkflowsRelationManager::class, // Sebaiknya dibuat untuk menampilkan histori
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
}