<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipResource\Pages;
use App\Models\Arsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ArsipResource extends Resource
{
    protected static ?string $model = Arsip::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Data Arsip';
    protected static ?string $pluralModelLabel = 'Data Arsip';
    protected static ?string $modelLabel = 'Arsip';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')
                ->label('Judul Arsip')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('kategori')
                ->label('Kategori')
                ->maxLength(255),

            FileUpload::make('dokumen_path')
                ->label('Upload Dokumen')
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->directory('arsip/dokumen')
                ->downloadable()
                ->openable()
                ->preserveFilenames()
                ->visibility('public'),

            FileUpload::make('gambar_path')
                ->label('Upload Gambar')
                ->image()
                ->imagePreviewHeight('100')
                ->directory('arsip/gambar')
                ->preserveFilenames()
                ->visibility('public'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('judul')
                ->label('Judul')
                ->searchable()
                ->sortable(),

            TextColumn::make('kategori')
                ->label('Kategori')
                ->sortable(),

            TextColumn::make('dokumen_path')
                ->label('Dokumen')
                ->formatStateUsing(fn ($state) => $state ? '📄 Lihat Dokumen' : '-')
                ->url(fn ($record) => $record->dokumen_path ? asset('storage/' . $record->dokumen_path) : null, true)
                ->openUrlInNewTab(),

            ImageColumn::make('gambar_path')
                ->label('Gambar'),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i')
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->dateTime('d M Y H:i')
                ->sortable(),
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
            'index' => Pages\ListArsips::route('/'),
            'create' => Pages\CreateArsip::route('/create'),
            'edit' => Pages\EditArsip::route('/{record}/edit'),
        ];
    }
}
