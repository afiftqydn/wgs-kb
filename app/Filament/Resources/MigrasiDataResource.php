<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MigrasiDataResource\Pages;
use App\Models\MigrasiData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MigrasiDataResource extends Resource
{
    protected static ?string $model = MigrasiData::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Data Migrasi';
    protected static ?string $modelLabel = 'Data Migrasi';
    protected static ?string $pluralModelLabel = 'Data Migrasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_nasabah')->required()->maxLength(255),
                        Forms\Components\TextInput::make('nik')->label('NIK')->numeric()->unique(ignoreRecord: true)->length(16),
                        Forms\Components\TextInput::make('nama_ibu_kandung')->maxLength(255),
                        Forms\Components\Select::make('jenis_kelamin')->options(['L' => 'Laki-laki', 'P' => 'Perempuan']),
                        Forms\Components\TextInput::make('tempat_lahir'),
                        Forms\Components\DatePicker::make('tanggal_lahir'),
                        Forms\Components\TextInput::make('agama'),
                    ])->columns(2),

                Forms\Components\Section::make('Alamat dan Kontak')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')->columnSpanFull(),
                        Forms\Components\TextInput::make('desa'),
                        Forms\Components\TextInput::make('kecamatan'),
                        Forms\Components\TextInput::make('kota_kabupaten'),
                        Forms\Components\TextInput::make('provinsi'),
                        Forms\Components\TextInput::make('no_hp')->label('No. HP')->tel(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Keanggotaan')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_register'),
                        Forms\Components\TextInput::make('simpok')->label('Simpanan Pokok')->numeric()->prefix('Rp'),
                        Forms\Components\TextInput::make('simwajib')->label('Simpanan Wajib')->numeric()->prefix('Rp'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_nasabah')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nik')->label('NIK')->searchable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP'),
                Tables\Columns\TextColumn::make('kota_kabupaten')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_register')->date('d M Y')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->color('gray'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMigrasiData::route('/'),
            'create' => Pages\CreateMigrasiData::route('/create'),
            'edit' => Pages\EditMigrasiData::route('/{record}/edit'),
        ];
    }
}