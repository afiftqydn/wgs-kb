<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
// use App\Filament\Resources\RegionResource\RelationManagers; // Aktifkan jika ada relation manager
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin'; // Ganti ikon sesuai keinginan
    protected static ?string $navigationGroup = 'Manajemen Master'; // Grup di navigasi sidebar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'CABANG' => 'Cabang',
                        'UNIT' => 'Unit',
                        'SUBUNIT' => 'SubUnit',
                    ])
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name') // Menampilkan nama region parent
                    ->searchable()
                    // Opsi untuk hanya menampilkan region yang levelnya di atasnya
                    // ->options(function (callable $get) {
                    //     $type = $get('type');
                    //     if ($type === 'UNIT') {
                    //         return Region::where('type', 'CABANG')->pluck('name', 'id');
                    //     } elseif ($type === 'SUBUNIT') {
                    //         return Region::where('type', 'UNIT')->pluck('name', 'id');
                    //     }
                    //     return [];
                    // })
                    ->nullable(),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->unique(Region::class, 'code', ignoreRecord: true) // Pastikan kode unik, abaikan record saat ini (untuk edit)
                    ->nullable(),
                Forms\Components\Toggle::make('status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->helperText('Status wilayah (Aktif/Tidak Aktif)')
                    ->default(true), // Defaultnya aktif (true merepresentasikan 'ACTIVE' jika di-cast di model atau di-handle saat save)
                // Jika enum Anda 'ACTIVE'/'INACTIVE', Anda mungkin perlu custom logic saat save atau menggunakan Select.
                // Untuk enum 'ACTIVE'/'INACTIVE', lebih baik gunakan Select:
                // Forms\Components\Select::make('status')
                //     ->options([
                //         'ACTIVE' => 'Aktif',
                //         'INACTIVE' => 'Tidak Aktif',
                //     ])
                //     ->default('ACTIVE')
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge() // Menampilkan sebagai badge
                    ->color(fn(string $state): string => match ($state) {
                        'CABANG' => 'primary',
                        'UNIT' => 'warning',
                        'SUBUNIT' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name') // Menampilkan nama dari relasi parent
                    ->label('Induk Wilayah')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean() // Jika status Anda adalah boolean (0/1).
                    // Jika enum 'ACTIVE'/'INACTIVE', gunakan TextColumn dengan format badge:
                    // Tables\Columns\TextColumn::make('status')
                    //     ->badge()
                    //     ->color(fn (string $state): string => match ($state) {
                    //         'ACTIVE' => 'success',
                    //         'INACTIVE' => 'danger',
                    //         default => 'gray',
                    //     }),
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Bisa disembunyikan defaultnya
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tambahkan filter jika perlu, misal berdasarkan tipe atau status
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'CABANG' => 'Cabang',
                        'UNIT' => 'Unit',
                        'SUBUNIT' => 'SubUnit',
                    ]),
                // Tables\Filters\TernaryFilter::make('status') // Jika status boolean
                //     ->label('Status Aktif')
                //     ->trueLabel('Aktif')
                //     ->falseLabel('Tidak Aktif')
                //     ->queries(
                //         true: fn (Builder $query) => $query->where('status', true),
                //         false: fn (Builder $query) => $query->where('status', false),
                //         blank: fn (Builder $query) => $query,
                //     ),
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
            // RelationManagers\UsersRelationManager::class, // Jika ingin menampilkan user per region di halaman edit/view region
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegions::route('/'),
            'create' => Pages\CreateRegion::route('/create'),
            'edit' => Pages\EditRegion::route('/{record}/edit'),
        ];
    }
}
