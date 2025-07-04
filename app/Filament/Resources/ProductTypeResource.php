<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTypeResource\Pages;
use App\Filament\Resources\ProductTypeResource\RelationManagers; // 1. Pastikan ini ada
use App\Models\ProductType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;

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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * 2. Aktifkan kembali Relation Manager di sini
     */
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
