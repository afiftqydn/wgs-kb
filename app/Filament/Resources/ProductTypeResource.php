<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Livewire\Livewire;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductType;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;

// Import Actions dan Infolist Components yang diperlukan
use Filament\Forms\Components\FileUpload;
use App\Http\Livewire\ViewPaymentSimulationImage;
use App\Filament\Resources\ProductTypeResource\Pages;
use App\Filament\Resources\ProductTypeResource\RelationManagers;

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
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('min_amount')
                    ->label('Jumlah Minimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('max_amount')
                    ->label('Jumlah Maksimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('escalation_threshold')
                    ->label('Ambang Batas Eskalasi (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->nullable(),
                TagsInput::make('required_documents')
                    ->label('Dokumen yang Dibutuhkan')
                    ->helperText('Masukkan nama dokumen satu per satu dan tekan Enter.')
                    ->columnSpanFull(),
                FileUpload::make('payment_simulation_image')
                    ->label('Simulasi Pembayaran (JPEG)')
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('min_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('max_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
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
                    )
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