<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTypeResource\Pages;
use App\Models\ProductType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;


class ProductTypeResource extends Resource
{
    protected static ?string $model = ProductType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube'; // Ganti ikon
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Jenis Produk';
    protected static ?string $pluralModelLabel = 'Data Jenis Produk'; // Consistent and descriptive
    protected static ?string $modelLabel = 'Jenis Produk'; 
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(), // Agar field ini mengambil lebar penuh
                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('min_amount')
                    ->label('Jumlah Minimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->default(0),
                TextInput::make('max_amount')
                    ->label('Jumlah Maksimal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->default(0),
                TagsInput::make('required_documents') // Kolom ini adalah JSON di DB [cite: 5]
                    ->label('Dokumen yang Dibutuhkan')
                    ->helperText('Masukkan nama dokumen satu per satu dan tekan Enter.')
                    ->columnSpanFull(),
                TextInput::make('escalation_threshold')
                    ->label('Ambang Batas Eskalasi (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->nullable(),
            ])->columns(2); // Mengatur form menjadi 2 kolom
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('min_amount')
                    ->money('IDR') // Menampilkan sebagai format mata uang Rupiah
                    ->sortable(),
                TextColumn::make('max_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('required_documents')
                    ->label('Dokumen Dibutuhkan')
                    ->formatStateUsing(function ($state) {
                        if (is_object($state) && method_exists($state, 'toArray')) {
                            // Jika $state adalah ArrayObject atau objek lain yang bisa diubah jadi array
                            return implode(', ', $state->toArray());
                        } elseif (is_array($state)) {
                            return implode(', ', $state);
                        }
                        return ''; // Default jika bukan array/objek yang diharapkan
                    })
                    ->listWithLineBreaks(), // Opsional, untuk menampilkan tiap item di baris baru jika banyak
                TextColumn::make('escalation_threshold')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('Tidak ada'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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

    public static function getRelations(): array
    {
        return [
            //
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
