<?php

namespace App\Filament\Resources\LoanApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ProductType;
use App\Models\ApplicationDocument; // Pastikan model di-import
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen Pendukung';
    protected static ?string $modelLabel = 'Dokumen';
    protected static ?string $pluralModelLabel = 'Dokumen';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(function (RelationManager $livewire): array {
                        $loanApplication = $livewire->getOwnerRecord();
                        if ($loanApplication && $loanApplication->product_type_id) {
                            $product = ProductType::find($loanApplication->product_type_id);
                            if ($product && is_array($product->required_documents) && !empty($product->required_documents)) {
                                $docs = $product->required_documents;
                                return array_combine($docs, $docs);
                            }
                        }
                        // Opsi default jika produk tidak ditemukan
                        return [
                            'Berkas Lampiran' => 'Berkas Lampiran',
                            'KTP' => 'KTP',
                            'NPWP' => 'NPWP',
                            'Lainnya' => 'Dokumen Lainnya',
                        ];
                    })
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File Dokumen')
                    ->disk('public')
                    ->directory('application-documents')
                    ->required()
                    ->preserveFilenames()
                    ->storeFileNamesIn('file_name')
                    ->maxSize(5120) // 5MB
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipe Dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable(),
                
                // PERBAIKAN UTAMA: Menggunakan relasi 'uploader' yang akan kita buat di model.
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diunggah Oleh')
                    ->default('Tidak diketahui') // Teks jika user tidak ditemukan (misal sudah dihapus)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Unggah')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Secara otomatis mengisi ID user yang sedang login ke kolom 'uploaded_by'
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (ApplicationDocument $record): string => Storage::disk('public')->url($record->file_path), shouldOpenInNewTab: true)
                    ->visible(fn (ApplicationDocument $record): bool => !empty($record->file_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
