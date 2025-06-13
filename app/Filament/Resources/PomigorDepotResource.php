<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PomigorDepotResource\Pages;
use App\Filament\Resources\PomigorDepotResource\RelationManagers;
use App\Filament\Resources\CustomerResource; // Untuk createOptionForm
use App\Models\PomigorDepot;
use App\Models\Region;
use App\Models\Customer;
use App\Models\User; // Jika ingin menampilkan nama creator di tabel
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle; // Tidak digunakan di form ini, tapi jaga-jaga jika ada
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn; // Tidak digunakan di tabel ini, tapi jaga-jaga
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth; // Untuk canViewAny

class PomigorDepotResource extends Resource
{
    protected static ?string $model = PomigorDepot::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront'; // Ikon sudah cukup mewakili gudang/depot
    protected static ?string $navigationGroup = 'Manajemen POMIGOR';
    protected static ?string $navigationLabel = 'Data Depot';
    protected static ?string $pluralModelLabel = 'Data Depot';
    protected static ?string $modelLabel = 'Depot';
    protected static ?int $navigationSort = 7;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Detail Depot POMIGOR')
                    ->columns(2)
                    ->schema([
                        TextInput::make('depot_code')
                            ->label('Kode Depot')
                            ->maxLength(255)
                            ->disabled() // Dinonaktifkan karena di-generate otomatis
                            ->dehydrated(false) // Jangan kirim nilai ini saat 'create' karena di-generate model
                                               // Saat 'edit', nilai yang ada akan ditampilkan tapi tetap disabled
                            ->placeholder('Akan digenerate otomatis saat pembuatan')
                            ->columnSpanFull(), 
                        TextInput::make('name')
                            ->label('Nama Depot')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('region_id')
                            ->label('Unit Pengelola (WGS)')
                            ->relationship('region', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('type', 'UNIT'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih Unit WGS yang bertanggung jawab atas depot ini.'),
                        Select::make('customer_id')
                            ->label('Nasabah Pengurus Depot')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm(CustomerResource::getCreationFormSchema()) // Menggunakan skema dari CustomerResource
                            ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action->modalWidth('5xl'))
                            ->helperText('Pilih nasabah WGS yang menjadi pengurus depot ini.'),
                        Textarea::make('address')
                            ->label('Alamat Lengkap Depot')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->helperText('Contoh: -0.0222820'),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->helperText('Contoh: 109.3456340'),
                        TextInput::make('current_stock_liters')
                            ->label('Stok Saat Ini (Liter)')
                            ->numeric()
                            ->disabled() // Stok diisi otomatis oleh pergerakan stok via Observer
                            ->default(0)
                            ->formatStateUsing(fn (?string $state): string => number_format(floatval($state), 2, ',', '.')) // Format tampilan
                            ->helperText('Stok akan terupdate otomatis berdasarkan histori pergerakan.'),
                        Select::make('status')
                            ->label('Status Depot')
                            ->options([
                                'ACTIVE' => 'Aktif',
                                'INACTIVE' => 'Tidak Aktif',
                                'MAINTENANCE' => 'Dalam Perawatan',
                            ])
                            ->default('ACTIVE')
                            ->required(),
                        // created_by akan diisi otomatis oleh model event PomigorDepot
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('depot_code')->label('Kode Depot')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Depot')->searchable()->sortable()->wrap(),
                TextColumn::make('region.name')->label('Unit Pengelola')->searchable()->sortable(),
                TextColumn::make('customer.name')->label('Nasabah Pengurus')->searchable()->sortable()->wrap(),
                TextColumn::make('current_stock_liters')
                    ->label('Stok (Liter)')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        'MAINTENANCE' => 'warning',
                        default => 'gray',
                    })->searchable()->sortable(),
                TextColumn::make('creator.name')->label('Didaftarkan Oleh')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Tgl. Didaftarkan')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Tgl. Diperbarui')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->label('Unit Pengelola')
                    ->relationship('region', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('type', 'UNIT')),
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Aktif',
                        'INACTIVE' => 'Tidak Aktif',
                        'MAINTENANCE' => 'Dalam Perawatan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(), // Hati-hati dengan delete jika ada pergerakan stok terkait
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        // Eager load relasi yang sering diakses di tabel atau view
        return parent::getEloquentQuery()->with(['region', 'customer', 'creator']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PomigorStockMovementsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPomigorDepots::route('/'),
            'create' => Pages\CreatePomigorDepot::route('/create'),
            'view' => Pages\ViewPomigorDepot::route('/{record}'), // Pastikan file ViewPomigorDepot.php sudah dibuat
            'edit' => Pages\EditPomigorDepot::route('/{record}/edit'),
        ];
    }    

    // Contoh sederhana kontrol akses, sesuaikan dengan permission yang lebih detail jika perlu
    public static function canViewAny(): bool
    {
        // Izinkan jika user adalah salah satu dari peran ini
        return Auth::check() && Auth::user()->hasAnyRole(['Admin Unit', 'Kepala Unit', 'Admin Cabang', 'Kepala Cabang', 'Tim IT']);
    }
}