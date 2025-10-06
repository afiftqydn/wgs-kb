<?php

namespace App\Filament\Resources;

use Exception; // Diperlukan untuk menangkap error
use Filament\Forms;
use Filament\Tables;
use App\Models\Region;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Referrer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Forms\Set;
use Filament\Notifications\Notification; // Diperlukan untuk menampilkan notifikasi

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Manajemen Pengajuan';
    protected static ?string $navigationLabel = 'Data Nasabah';
    protected static ?string $modelLabel = 'Data Nasabah';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getCreationFormSchema());
    }

    public static function getCreationFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Pribadi Nasabah')
                ->schema([
                    
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap Nasabah')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('nik')
                        ->label('NIK')
                        ->maxLength(255)
                        ->unique(Customer::class, 'nik', ignoreRecord: true)
                        ->nullable(),
                    Forms\Components\TextInput::make('phone')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(255)
                        ->nullable(),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
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

                                if (!$user->hasRole(['Tim IT', 'Kepala Cabang', 'Admin Cabang'])) {
                                    
                                    if ($user->hasAnyRole(['Kepala Unit', 'Admin Unit'])) {
                                        if ($user->region_id) {
                                            $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                                            $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                                            $query->whereIn('id', $accessibleRegionIds);
                                        } else {
                                            $query->whereRaw('1 = 0');
                                        }
                                    } 
                                    elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
                                        if ($user->region_id) {
                                            $query->where('id', $user->region_id);
                                        } else {
                                            $query->whereRaw('1 = 0');
                                        }
                                    } 
                                    else {
                                        $query->whereRaw('1 = 0');
                                    }
                                }
                            }
                        )
                        ->default(function () {
                            $user = Auth::user();
                            if ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
                                return $user->region_id;
                            }
                            return null;
                        })
                        ->disabled(function () {
                            return Auth::user()->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit']);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Informasi Referral (Jika Ada)')
                ->schema([
                    Forms\Components\Select::make('referrer_id')
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
                        ->nullable(),
                ])->columns(2),
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
                Tables\Actions\ViewAction::make(),
                
                // =========================================================
                // AWAL PERBAIKAN: Modifikasi DeleteAction
                // =========================================================
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        try {
                            // Coba hapus data
                            $record->delete();
                            // Jika berhasil, kirim notifikasi sukses
                            Notification::make()
                                ->success()
                                ->title('Data Nasabah Dihapus')
                                ->body('Data nasabah berhasil dihapus.')
                                ->send();
                        } catch (Exception $e) {
                            // Jika gagal (karena Exception dari Model), kirim notifikasi error
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Data')
                                ->body($e->getMessage()) // Tampilkan pesan error dari Model
                                ->send();
                        }
                    }),
                // =========================================================
                // AKHIR PERBAIKAN
                // =========================================================
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // =========================================================
                    // AWAL PERBAIKAN: Modifikasi DeleteBulkAction
                    // =========================================================
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $successCount = 0;
                            $failedCount = 0;
                            $errorMessage = '';

                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                    $successCount++;
                                } catch (Exception $e) {
                                    $failedCount++;
                                    // Simpan pesan error pertama sebagai contoh
                                    if (empty($errorMessage)) {
                                        $errorMessage = $e->getMessage();
                                    }
                                }
                            }
                            
                            // Kirim notifikasi jika ada yang berhasil dihapus
                            if ($successCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Proses Selesai')
                                    ->body("Berhasil menghapus {$successCount} data nasabah.")
                                    ->send();
                            }

                            // Kirim notifikasi jika ada yang gagal dihapus
                            if ($failedCount > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Data Gagal Dihapus')
                                    ->body("{$failedCount} data gagal dihapus karena memiliki data terkait. Contoh error: " . $errorMessage)
                                    ->persistent() // Agar notifikasi tidak mudah hilang
                                    ->send();
                            }
                        }),
                    // =========================================================
                    // AKHIR PERBAIKAN
                    // =========================================================
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole(['Tim IT', 'Kepala Cabang', 'Analis Cabang', 'Admin Cabang'])) {
            return parent::getEloquentQuery()->with(['region', 'creator', 'referrer']);
        }

        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                return parent::getEloquentQuery()
                    ->whereIn('region_id', $accessibleRegionIds)
                    ->with(['region', 'creator', 'referrer']);
            }
        }

        if ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                return parent::getEloquentQuery()
                    ->where('region_id', $user->region_id)
                    ->with(['region', 'creator', 'referrer']);
            }
        }
        
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['region_id'])) {
            $data['region_id'] = Auth::user()->region_id;
        }

        return $data;
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            // 'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}