<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use App\Models\Region; // Import model Region
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $pluralModelLabel = 'Data Karyawan';
    protected static ?string $modelLabel = 'Data Karyawan';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Buat Karyawan Baru')->tabs([
                    Tabs\Tab::make('Data Pribadi')
                        ->icon('heroicon-o-user')
                        ->schema([
                            FileUpload::make('pas_foto')
                                ->label('Pas Foto')
                                ->image()
                                ->avatar()
                                ->imageEditor()
                                ->directory('karyawan/foto')
                                ->columnSpanFull(),
                            TextInput::make('nama_lengkap')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Grid::make(2)->schema([
                                TextInput::make('tempat_lahir')->required(),
                                DatePicker::make('tanggal_lahir')->required()->native(false),
                            ]),
                            Radio::make('jenis_kelamin')
                                ->options([
                                    'Pria' => 'Pria',
                                    'Wanita' => 'Wanita'
                                ])->required(),
                            Select::make('agama')
                                ->options([
                                    'Islam' => 'Islam',
                                    'Kristen Protestan' => 'Kristen Protestan',
                                    'Kristen Katolik' => 'Kristen Katolik',
                                    'Hindu' => 'Hindu',
                                    'Buddha' => 'Buddha',
                                    'Khonghucu' => 'Khonghucu',
                                ])->required()->searchable(),
                            Select::make('status_pernikahan')
                                ->options([
                                    'Belum Menikah' => 'Belum Menikah',
                                    'Menikah' => 'Menikah',
                                    'Cerai Hidup' => 'Cerai Hidup',
                                    'Cerai Mati' => 'Cerai Mati',
                                ])->required()->searchable(),
                            Textarea::make('alamat_ktp')
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('alamat_domisili')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('Informasi Pekerjaan')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            TextInput::make('jabatan')->required(),
                            Select::make('status_karyawan')
                                ->options([
                                    'Tetap/PKWTT' => 'Tetap/PKWTT',
                                    'Kontrak/PKWT' => 'Kontrak/PKWT',
                                    'Magang' => 'Magang',
                                    'Harian' => 'Harian',
                                ])
                                ->required()
                                ->live(),
                            DatePicker::make('tanggal_bergabung')->required()->native(false),
                            DatePicker::make('tanggal_berakhir_kontrak')
                                ->label('Tanggal Berakhir Kontrak (jika PKWT)')
                                ->native(false)
                                ->visible(fn (Get $get) => $get('status_karyawan') === 'Kontrak/PKWT'),
                            // Mengubah TextInput 'kantor' menjadi Select 'region_id'
                            Select::make('region_id')
                                ->label('Kantor/Wilayah')
                                ->relationship('region', 'name') // Menghubungkan ke model Region, menampilkan kolom 'name'
                                ->required()
                                ->searchable()
                                ->preload() // Opsional: muat semua opsi di awal untuk pencarian lebih cepat
                                ->getOptionLabelUsing(fn ($value): ?string => Region::find($value)?->name), // Pastikan label tampil dengan benar saat diedit
                            TextInput::make('no_hp')
                                ->label('No. HP Aktif')
                                ->tel()
                                ->required(),
                        ]),

                    Tabs\Tab::make('Finansial & Legal')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                             TextInput::make('npwp')->label('Nomor NPWP')->unique(ignoreRecord: true)->nullable(),
                             TextInput::make('bpjs_ketenagakerjaan')->label('Nomor BPJS Ketenagakerjaan')->unique(ignoreRecord: true)->nullable(),
                             TextInput::make('bpjs_kesehatan')->label('Nomor BPJS Kesehatan')->unique(ignoreRecord: true)->nullable(),
                             Section::make('Informasi Rekening Bank')->schema([
                                 TextInput::make('nama_bank')->label('Nama Bank')->nullable(),
                                 TextInput::make('nomor_rekening')->label('Nomor Rekening')->nullable(),
                                 TextInput::make('nama_pemilik_rekening')->label('Atas Nama')->nullable(),
                             ])->columns(3),
                        ]),

                    Tabs\Tab::make('Kontak Darurat')
                        ->icon('heroicon-o-phone-arrow-up-right')
                        ->schema([
                            TextInput::make('nama_kontak_darurat')->required(),
                            Select::make('hubungan_kontak_darurat')
                                ->label('Hubungan')
                                ->options([
                                    'Orang Tua' => 'Orang Tua',
                                    'Pasangan' => 'Pasangan',
                                    'Saudara' => 'Saudara',
                                    'Anak' => 'Anak',
                                    'Lainnya' => 'Lainnya',
                                ])
                                ->required(),
                            TextInput::make('no_hp_kontak_darurat')->tel()->required(),
                        ]),

                    Tabs\Tab::make('Dokumen Digital')
                        ->icon('heroicon-o-document-arrow-up')
                        ->schema([
                            FileUpload::make('file_ktp')
                                ->label('Upload KTP')
                                ->directory('karyawan/ktp')
                                ->acceptedFileTypes(['application/pdf', 'image/*']),
                            FileUpload::make('file_npwp')
                                ->label('Upload NPWP')
                                ->directory('karyawan/npwp')
                                ->acceptedFileTypes(['application/pdf', 'image/*']),
                            FileUpload::make('file_perjanjian_kerja')
                                ->label('Upload Surat Perjanjian Kerja')
                                ->directory('karyawan/kontrak')
                                ->acceptedFileTypes(['application/pdf']),
                        ])->columns(1),

                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('pas_foto')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jabatan')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status_karyawan')
                    ->label('Status')
                    ->colors([
                        'success' => 'Tetap/PKWTT',
                        'warning' => 'Kontrak/PKWT',
                        'info' => 'Magang',
                        'gray' => 'Harian',
                    ])
                    ->searchable(),
                TextColumn::make('region.name') // Menampilkan nama region dari relasi
                    ->label('Kantor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Asumsi: User yang login memiliki kolom 'region_id'
                $loggedInUserRegionId = auth()->user()->region_id;

                if ($loggedInUserRegionId) {
                    // Jika user adalah admin cabang, dia bisa melihat karyawan di semua unit dan subunit di bawah cabang tersebut.
                    // Jika user adalah admin unit, dia bisa melihat karyawan di unit dan subunit di bawahnya.
                    // Jika user adalah admin subunit, dia hanya melihat karyawan di subunitnya.

                    $loggedInRegion = Region::find($loggedInUserRegionId);

                    if ($loggedInRegion) {
                        if ($loggedInRegion->type === 'CABANG') {
                            // Dapatkan semua ID unit dan subunit di bawah cabang ini
                            $regionIds = $loggedInRegion->children->pluck('id')->toArray(); // Unit
                            foreach ($loggedInRegion->children as $unit) {
                                $regionIds = array_merge($regionIds, $unit->children->pluck('id')->toArray()); // Subunit
                            }
                            $regionIds[] = $loggedInRegion->id; // Termasuk cabang itu sendiri jika ada karyawan yang langsung terdaftar di cabang

                            $query->whereIn('region_id', $regionIds);
                        } elseif ($loggedInRegion->type === 'UNIT') {
                            // Dapatkan semua ID subunit di bawah unit ini
                            $regionIds = $loggedInRegion->children->pluck('id')->toArray(); // Subunit
                            $regionIds[] = $loggedInRegion->id; // Termasuk unit itu sendiri

                            $query->whereIn('region_id', $regionIds);
                        } elseif ($loggedInRegion->type === 'SUBUNIT') {
                            // Hanya tampilkan karyawan di subunit ini
                            $query->where('region_id', $loggedInUserRegionId);
                        }
                    }
                }
                // Jika tidak ada region_id pada user, atau tidak ada loggedInRegion,
                // query akan mengembalikan semua data (Anda bisa menambahkan logic default di sini)
            });
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Sesuaikan badge count dengan filter region
        $loggedInUserRegionId = auth()->user()->region_id;
        if ($loggedInUserRegionId) {
            $loggedInRegion = Region::find($loggedInUserRegionId);
            if ($loggedInRegion) {
                if ($loggedInRegion->type === 'CABANG') {
                    $regionIds = $loggedInRegion->children->pluck('id')->toArray();
                    foreach ($loggedInRegion->children as $unit) {
                        $regionIds = array_merge($regionIds, $unit->children->pluck('id')->toArray());
                    }
                    $regionIds[] = $loggedInRegion->id;
                    return static::$model::whereIn('region_id', $regionIds)->count();
                } elseif ($loggedInRegion->type === 'UNIT') {
                    $regionIds = $loggedInRegion->children->pluck('id')->toArray();
                    $regionIds[] = $loggedInRegion->id;
                    return static::$model::whereIn('region_id', $regionIds)->count();
                } elseif ($loggedInRegion->type === 'SUBUNIT') {
                    return static::$model::where('region_id', $loggedInUserRegionId)->count();
                }
            }
        }
        return static::$model::count(); // Default jika tidak ada filter region
    }
}