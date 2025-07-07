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
                    ->label('Nama Aturan (Contoh: Komisi Unit, Fee Referral)'),
                
                // TAMBAHKAN DROPDOWN INI
                Forms\Components\Select::make('recipient_level')
                    ->label('Penerima Komisi')
                    ->options([
                        'Unit' => 'Unit Pemroses',
                        'Cabang' => 'Cabang Induk',
                        'Referral' => 'Referral/Marketing',
                    ])
                    ->required(),

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
                Tables\Columns\TextColumn::make('recipient_level')->label('Penerima'), // Tampilkan di tabel
                Tables\Columns\TextColumn::make('type')->label('Jenis'),
                Tables\Columns\TextColumn::make('value')->label('Nilai')->numeric(),
            ])
            // ... sisa kode actions ...
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
