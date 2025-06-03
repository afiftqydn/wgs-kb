<?php

namespace App\Filament\Resources\LoanApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Get; // Untuk mengambil nilai dari form induk jika perlu (lebih advance)
use App\Models\ProductType; // Jika jenis dokumen mau dinamis dari produk di form induk

class DocumentsRelationManager extends RelationManager
{
  protected static string $relationship = 'documents'; // Nama relasi di model LoanApplication

  // Anda bisa override nama record jika mau, defaultnya adalah nama relasi
  // protected static ?string $recordTitleAttribute = 'file_name'; 

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Select::make('document_type')
          ->label('Jenis Dokumen')
          // Opsi ini bisa dibuat lebih dinamis jika Anda bisa mengakses
          // product_type_id dari record LoanApplication induk.
          // Ini contoh statis atau bisa Anda kembangkan:
          ->options(function (RelationManager $livewire): array {
            // Dapatkan product_type_id dari owner record (LoanApplication)
            $loanApplication = $livewire->getOwnerRecord();
            if ($loanApplication && $loanApplication->product_type_id) {
              $product = ProductType::find($loanApplication->product_type_id);
              if ($product && !empty($product->required_documents)) {
                $docs = $product->required_documents->toArray();
                return array_combine($docs, $docs);
              }
            }
            return [
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
          ->disk('public')
          ->directory('application-documents') // Sesuaikan dengan konfigurasi Anda
          ->required()
          ->preserveFilenames()
          ->storeFileNamesIn('file_name') // Menyimpan nama asli ke kolom 'file_name'
          ->maxSize(5120) // 5MB
          ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
        // Kolom uploaded_by akan diisi otomatis oleh model event ApplicationDocument
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      // ->recordTitleAttribute('file_name') // Kolom yang dijadikan judul record
      ->columns([
        Tables\Columns\TextColumn::make('document_type')->searchable(),
        Tables\Columns\TextColumn::make('file_name')->searchable()->label('Nama File'),
        Tables\Columns\TextColumn::make('uploader.name')->label('Diunggah Oleh')->placeholder('N/A'),
        Tables\Columns\TextColumn::make('created_at')->label('Tanggal Unggah')->dateTime()->sortable(),
      ])
      ->filters([
        //
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make(), // Tombol untuk menambah dokumen baru
      ])
      ->actions([
        Tables\Actions\ViewAction::make(), // Untuk melihat detail (misal, preview file)
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }
}
