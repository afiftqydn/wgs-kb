<?php

namespace App\Filament\Resources;

use Filament\Forms;
// use App\Filament\Resources\CustomerResource\RelationManagers;
use Filament\Tables;
use App\Models\Region;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Referrer; // Import Referrer model
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;    // Import User model (untuk created_by)
use Filament\Forms\Set; // Untuk mengisi field lain secara otomatis

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Manajemen Pengajuan'; // Grup baru atau yang sesuai
    protected static ?string $navigationLabel = 'Data Nasabah';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getCreationFormSchema()); // Panggil method baru di sini
    }
    // TAMBAHKAN METHOD BARU INI:
    public static function getCreationFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Pribadi Nasabah')
                ->schema([
                    Forms\Components\TextInput::make('nik')
                        ->label('NIK')
                        ->maxLength(255)
                        ->unique(Customer::class, 'nik', ignoreRecord: true)
                        ->nullable(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap Nasabah')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label('Nomor Telepon')
                        ->tel()
                        ->maxLength(255)
                        ->nullable(),
                    Forms\Components\TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->maxLength(255)
                        ->unique(Customer::class, 'email', ignoreRecord: true)
                        ->nullable(),
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat Lengkap')
                        ->columnSpanFull()
                        ->nullable(),
                    Forms\Components\Select::make('region_id')
                        ->label('Wilayah Domisili Nasabah')
                        ->relationship(
                            name: 'region',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query) {
                                $user = Auth::user();

                                // Jika bukan peran global, terapkan filter
                                if (!$user->hasRole(['Tim IT', 'Kepala Cabang', 'Admin Cabang'])) {
                                    
                                    // Jika peran level UNIT, mereka bisa memilih Unitnya atau SubUnit di bawahnya
                                    if ($user->hasAnyRole(['Kepala Unit', 'Admin Unit'])) {
                                        if ($user->region_id) {
                                            $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                                            $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                                            $query->whereIn('id', $accessibleRegionIds);
                                        } else {
                                            $query->whereRaw('1 = 0');
                                        }
                                    } 
                                    // Jika peran level SUBUNIT, mereka HANYA bisa memilih SubUnitnya sendiri
                                    elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
                                        if ($user->region_id) {
                                            $query->where('id', $user->region_id);
                                        } else {
                                            $query->whereRaw('1 = 0');
                                        }
                                    } 
                                    else {
                                        $query->whereRaw('1 = 0'); // Peran lain tidak bisa memilih
                                    }
                                }
                                // Untuk peran global, tidak ada filter, semua wilayah ditampilkan
                            }
                        )
                        // --- TAMBAHKAN KODE INI UNTUK MEMPERBAIKI MASALAH ---
                        ->default(function () {
                            $user = Auth::user();
                            // Jika pengguna adalah SubUnit, otomatis set nilainya ke region_id mereka
                            if ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
                                return $user->region_id;
                            }
                            return null;
                        })
                        ->disabled(function () {
                            // Kunci (disable) field ini jika pengguna adalah SubUnit
                            return Auth::user()->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit']);
                        })
                        // ----------------------------------------------------
                        ->searchable()
                        ->preload()
                        ->required(),

                ])->columns(2),

            Forms\Components\Section::make('Informasi Referral (Jika Ada)')
                ->schema([
                    Forms\Components\Select::make('referrer_id')
                        // ... (konfigurasi referrer_id seperti sebelumnya)
                        ->relationship('referrer', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                            if ($state) {
                                $referrer = Referrer::find($state);
                                if ($referrer) {
                                    $set('referral_code_used', $referrer->generated_referral_code);
                                }
                            } else {
                                $set('referral_code_used', null);
                            }
                        })
                        ->nullable(),
                    Forms\Components\TextInput::make('referral_code_used')
                        // ... (konfigurasi referral_code_used seperti sebelumnya)
                        ->nullable(),
                ])->columns(2),
            // Kolom created_by tidak perlu ada di form ini karena diisi otomatis oleh model event
        ];
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Nasabah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Wilayah Domisili')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('referral_code_used')
                    ->label('Kode Referral')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\ViewAction::make(), // Tambahkan ViewAction
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // 1. Jika pengguna adalah peran global (Cabang atau Tim IT), tampilkan semua nasabah.
        if ($user->hasRole(['Tim IT', 'Kepala Cabang', 'Analis Cabang', 'Admin Cabang'])) {
            // Kembalikan query asli dengan eager loading
            return parent::getEloquentQuery()->with(['region', 'creator', 'referrer']);
        }

        // 2. Jika pengguna berada di level UNIT (Kepala Unit, Analis Unit, Admin Unit)
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                // Ambil ID dari semua SubUnit yang berada di bawah Unit pengguna ini
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                
                // Gabungkan ID Unit pengguna dengan ID semua SubUnit di bawahnya
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);

                // Tampilkan nasabah yang region_id (domisilinya) ada di dalam daftar wilayah yang bisa diakses
                return parent::getEloquentQuery()
                    ->whereIn('region_id', $accessibleRegionIds)
                    ->with(['region', 'creator', 'referrer']);
            }
        }

        // 3. Jika pengguna berada di level SUBUNIT (Kepala SubUnit, Admin SubUnit)
        if ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                // Hanya tampilkan nasabah yang region_id (domisilinya) sama dengan region_id pengguna
                return parent::getEloquentQuery()
                    ->where('region_id', $user->region_id)
                    ->with(['region', 'creator', 'referrer']);
            }
        }
        
        // 4. Untuk peran lain (seperti Manager Keuangan yang tidak terkait wilayah), 
        // jangan tampilkan data nasabah apa pun secara default.
        return parent::getEloquentQuery()->whereRaw('1 = 0'); // Query yang selalu mengembalikan hasil kosong
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['region_id'])) {
            $data['region_id'] = Auth::user()->region_id;
        }

        return $data;
    }    // getRelations() dan getPages() bisa dibiarkan default atau disesuaikan jika perlu
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            // 'view' => Pages\ViewCustomer::route('/{record}'), // Aktifkan jika ada halaman view
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
