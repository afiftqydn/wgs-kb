<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTypeResource\Pages;
use App\Filament\Resources\ProductTypeResource\RelationManagers;
use App\Models\ProductType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductTypeResource extends Resource
{
    protected static ?string $model = ProductType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Jenis Produk';
    protected static ?string $pluralModelLabel = 'Data Jenis Produk';
    protected static ?string $modelLabel = 'Jenis Produk';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min_amount')
                    ->label('Jumlah Minimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('max_amount')
                    ->label('Jumlah Maksimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('escalation_threshold')
                    ->label('Ambang Batas Eskalasi (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->nullable(),
                Forms\Components\TagsInput::make('required_documents')
                    ->label('Dokumen yang Dibutuhkan')
                    ->helperText('Masukkan nama dokumen satu per satu dan tekan Enter.')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('payment_simulation_image')
                    ->label('Simulasi Pembayaran (JPEG/PNG)')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->directory('product-type-simulations')
                    ->visibility('public')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record, Tables\Actions\DeleteAction $action) {
                        // Cek apakah ada data 'loanApplications' yang terhubung dengan record ini.
                        if ($record->loanApplications()->exists()) {
                            // Jika ada, kirim notifikasi error.
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Jenis produk ini tidak dapat dihapus karena sudah digunakan oleh data Pengajuan Pinjaman.')
                                ->send();
                            
                            // Batalkan aksi penghapusan.
                            $action->halt();
                        }
                    }),
                Action::make('view_simulation')
                    ->label('Simulasi')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Check Simulasi Pembayaran')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth('4xl')
                    ->modalHeading(fn (ProductType $record) => 'Simulasi Pembayaran: ' . $record->name)
                    ->visible(fn (ProductType $record): bool => (bool) $record->payment_simulation_image)
                    ->modalContent(fn (ProductType $record) =>
                        view('livewire.view-payment-simulation-image', [
                            'imageUrl' => Storage::url($record->payment_simulation_image),
                        ])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductTypeRulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductTypes::route('/'),
            'create' => Pages\CreateProductType::route('/create'),
            'edit' => Pages\EditProductType::route('/{record}/edit'),
        ];
    }
}