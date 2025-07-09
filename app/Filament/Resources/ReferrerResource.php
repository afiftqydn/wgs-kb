<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferrerResource\Pages;
// use App\Filament\Resources\ReferrerResource\RelationManagers;
use App\Models\Referrer;
use App\Models\Region; // Import Region
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set; // Untuk mengisi field lain secara otomatis
use Filament\Forms\Get; // Untuk mengambil nilai field lain

class ReferrerResource extends Resource
{
    protected static ?string $model = Referrer::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Referral';
    protected static ?string $pluralModelLabel = 'Data Referral';
    protected static ?string $modelLabel = 'Data Referral';
    protected static ?int $navigationSort = 5;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Referrer (Marketing/Ormas)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Tipe Referrer')
                    ->options([
                        'MARKETING' => 'Marketing Perorangan',
                        'ORMAS' => 'Organisasi Masyarakat (Ormas)',
                    ])
                    ->required()
                    ->live() // Reaktif untuk auto-generate kode
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::generateReferralCode($set, $get)),
                Forms\Components\Select::make('region_id')
                    ->label('Wilayah WGS Terkait')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->live() // Reaktif untuk auto-generate kode
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::generateReferralCode($set, $get))
                    ->nullable(),
                Forms\Components\TextInput::make('unique_person_organization_code')
                    ->label('Kode Unik Internal Referrer')
                    ->helperText('Contoh: MKT001, ORGXYZ. Akan digunakan untuk generate kode referral.')
                    ->required()
                    ->maxLength(50)
                    ->live() // Reaktif untuk auto-generate kode
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::generateReferralCode($set, $get)),
                Forms\Components\TextInput::make('generated_referral_code')
                    ->label('Kode Referral (Ter-generate)')
                    ->maxLength(100)
                    ->unique(Referrer::class, 'generated_referral_code', ignoreRecord: true)
                    ->required()
                    ->helperText('Format: TIPE-KODEWILAYAH-KODEUNIK. Contoh: MRKT-KB00-MKT001')
                    ->readOnly(fn(string $context) => $context === 'create'), 
                Forms\Components\TextInput::make('contact_person')
                    ->label('Nama Narahubung')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('phone')
                    ->label('Nomor Telepon Kontak')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->label('Status Referrer')
                    ->options([
                        'ACTIVE' => 'Aktif',
                        'INACTIVE' => 'Tidak Aktif',
                    ])
                    ->default('ACTIVE')
                    ->required(),
            ])->columns(2);
    }

    // Fungsi helper untuk generate kode referral
    protected static function generateReferralCode(Set $set, Get $get): void
    {
        $type = $get('type');
        $regionId = $get('region_id');
        $uniqueCode = $get('unique_person_organization_code');
        $regionCode = 'XXXX'; // Default jika region tidak dipilih

        if ($regionId) {
            $region = Region::find($regionId);
            if ($region && $region->code) {
                $regionCode = $region->code;
            }
        }

        if ($type && $uniqueCode) {
            $prefix = ($type === 'MARKETING') ? 'MRKT' : 'ORMS';
            $set('generated_referral_code', strtoupper($prefix . '-' . $regionCode . '-' . $uniqueCode));
        } else {
            $set('generated_referral_code', ''); 
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Referrer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'MARKETING' => 'info',
                        'ORMAS' => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Wilayah WGS')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('generated_referral_code')
                    ->label('Kode Referral')
                    ->searchable()
                    ->copyable() // Tambahkan aksi copy
                    ->copyMessage('Kode referral disalin!'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'MARKETING' => 'Marketing Perorangan',
                        'ORMAS' => 'Organisasi Masyarakat (Ormas)',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Aktif',
                        'INACTIVE' => 'Tidak Aktif',
                    ]),
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
            'index' => Pages\ListReferrers::route('/'),
            'create' => Pages\CreateReferrer::route('/create'),
            'edit' => Pages\EditReferrer::route('/{record}/edit'),
        ];
    }
}
