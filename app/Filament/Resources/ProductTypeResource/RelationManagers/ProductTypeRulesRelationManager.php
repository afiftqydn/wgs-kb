<?php

namespace App\Filament\Resources\ProductTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductTypeRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'productTypeRules';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Aturan'),
                Forms\Components\Select::make('type')
                    ->options([
                        'percentage' => 'Persentase (%)',
                        'flat' => 'Tetap (Rp)',
                    ])
                    ->required()
                    ->label('Jenis Aturan'),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->label('Nilai'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Aturan'),
                Tables\Columns\TextColumn::make('type')->label('Jenis'),
                Tables\Columns\TextColumn::make('value')->label('Nilai')->numeric(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
