<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
// use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Region;
use App\Models\Referrer; // Import Referrer model
use App\Models\User;    // Import User model (untuk created_by)
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set; // Untuk mengisi field lain secara otomatis

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Manajemen Nasabah'; // Grup baru atau yang sesuai
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
                        ->relationship('region', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
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

    // Method untuk mengisi created_by secara otomatis
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['region', 'creator', 'referrer']);
    }

    // Menggunakan mutateFormDataBeforeCreate untuk mengisi created_by
    // Ini adalah cara yang lebih umum di Filament v3
    // Atau, Anda bisa melakukannya di event Eloquent (creating) pada model Customer.

    // getRelations() dan getPages() bisa dibiarkan default atau disesuaikan jika perlu
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
