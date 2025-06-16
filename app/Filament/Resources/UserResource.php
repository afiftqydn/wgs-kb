<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
// use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Region; // Import Region model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Spatie\Permission\Models\Role; // Import Role model

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle'; // Ikon orang, sudah sesuai
    protected static ?string $navigationGroup = 'Administrasi Sistem'; // Sudah konsisten
    protected static ?string $navigationLabel = 'Data Pengguna Sistem';
    protected static ?string $pluralModelLabel = 'Data Pengguna Sistem';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?int $navigationSort = 8;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    // ->required() // Hanya required saat membuat user baru
                    ->required(fn(string $context): bool => $context === 'create') // Hanya required di form create
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null) // Hash password jika diisi
                    ->dehydrated(fn($state) => !empty($state)) // Hanya kirim ke DB jika diisi (untuk update opsional)
                    ->helperText('Kosongkan jika tidak ingin mengubah password saat edit.'),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload() // Preload options untuk performa lebih baik jika datanya tidak terlalu banyak
                    ->label('Wilayah Operasional')
                    ->nullable(),
                Forms\Components\TextInput::make('wgs_job_title')
                    ->label('Jabatan WGS')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\Select::make('wgs_level')
                    ->label('Level WGS')
                    ->options([
                        'GLOBAL' => 'Global',
                        'CABANG' => 'Cabang',
                        'UNIT' => 'Unit',
                        'SUBUNIT' => 'SubUnit',
                    ])
                    ->nullable(),
                Forms\Components\Select::make('roles') // Untuk Spatie roles
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload() // Preload roles
                    ->label('Peran Sistem (Roles)')
                    // ->options(Role::all()->pluck('name', 'id')) // Alternatif jika relationship tidak langsung jalan
                    ->helperText('Pilih satu atau lebih peran untuk pengguna ini.'),
                Forms\Components\DateTimePicker::make('email_verified_at') // Untuk admin bisa set manual jika perlu
                    ->label('Email Terverifikasi pada')
                    ->nullable(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name') // Menampilkan nama dari relasi region
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('wgs_job_title')
                    ->label('Jabatan WGS')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('wgs_level')
                    ->label('Level WGS')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('roles.name') // Menampilkan nama dari relasi roles (Spatie)
                    ->label('Peran Sistem')
                    ->badge()
                    ->searchable(), // Pencarian berdasarkan nama peran mungkin perlu kustomisasi lebih lanjut
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
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
            // RelationManagers\RolesRelationManager::class, // Bisa dibuat jika ingin manajemen role lebih detail di halaman user
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


// ✅ Jumlah user sebagai badge
public static function getNavigationBadge(): ?string
{
    return static::$model::count();
}

// ✅ Tooltip badge saat hover
public static function getNavigationBadgeTooltip(): ?string
{
    return 'Jumlah Total Pengguna';
}
}