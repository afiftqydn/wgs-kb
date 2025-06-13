<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $pluralModelLabel = 'Data Karyawan';
    protected static ?string $modelLabel = 'Karyawan';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('kantor')
                    ->label('Kantor')
                    ->options([
                        'Cabang Kalbar' => 'Cabang Kalbar',
                        'Cabang Jabar' => 'Cabang Jabar',
                        'Unit Pontianak' => 'Unit Pontianak',
                        'Unit Kubu Raya' => 'Unit Kubu Raya',
                        'Unit Mempawah' => 'Unit Mempawah',
                        'Unit Singkawang' => 'Unit Singkawang',
                        'Unit Sambas' => 'Unit Sambas',
                        'Unit Melawi' => 'Unit Melawi',
                        'Unit Sintang' => 'Unit Sintang',
                        'Unit Ketapang' => 'Unit Ketapang',
                        'Unit Kapuas Hulu' => 'Unit Kapuas Hulu',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('no_hp')
                    ->label('No. HP')
                    ->required()
                    ->tel()
                    ->maxLength(20),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kantor')
                    ->label('Kantor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    // ✅ Badge di sidebar
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    // ✅ Tooltip saat hover di badge
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah Total Karyawan';
    }
}
