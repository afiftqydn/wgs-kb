<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use App\Models\Region;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; // <-- [PERBAIKAN] Mengimpor facade Auth

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
                            Select::make('region_id')
                                ->label('Kantor/Wilayah')
                                ->relationship('region', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
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
                // [PENINGKATAN] Foto tetap, tapi ukurannya diatur agar konsisten.
                Tables\Columns\ImageColumn::make('pas_foto')
                    ->label('Foto')
                    ->circular()
                    ->size(50), // Ukuran standar yang baik (40px)

                // [PENINGKATAN] Menggabungkan Nama dan Jabatan untuk tampilan yang lebih ringkas dan modern.
                // Nama dibuat menonjol, dan jabatan sebagai deskripsi di bawahnya.
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama & Jabatan')
                    ->description(fn (Karyawan $record): string => $record->jabatan)
                    ->searchable(['nama_lengkap', 'jabatan']) // Pencarian bisa untuk nama & jabatan
                    ->sortable(),

                // [PENINGKATAN] Menggunakan BadgeColumn yang memang dibuat khusus untuk ini.
                Tables\Columns\BadgeColumn::make('status_karyawan')
                    ->label('Status')
                    ->colors([
                        'success' => 'Tetap/PKWTT',
                        'warning' => 'Kontrak/PKWT',
                        'info' => 'Magang',
                        'gray' => 'Harian', // 'secondary' untuk warna abu-abu
                    ])
                    ->searchable(),

                // [PENINGKATAN] Kolom Kantor dengan ikon untuk petunjuk visual.
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Kantor')
                    ->badge()
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable(),

                // [PENINGKATAN] No. HP yang interaktif: bisa di-klik untuk menelepon dan bisa disalin.
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->copyable() // Menambahkan tombol untuk menyalin nomor
                    ->copyMessage('No. HP disalin!')
                    ->url(fn (Karyawan $record): ?string => $record->no_hp ? "tel:{$record->no_hp}" : null)
                    ->toggleable(isToggledHiddenByDefault: false), // Ditampilkan secara default

                // Kolom ini disembunyikan by default, bagus untuk info tambahan.
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // [PENINGKATAN] Filter interaktif berdasarkan status karyawan.
                Tables\Filters\SelectFilter::make('status_karyawan')
                    ->label('Filter Status')
                    ->options([
                        'Tetap/PKWTT' => 'Tetap/PKWTT',
                        'Kontrak/PKWT' => 'Kontrak/PKWT',
                        'Magang' => 'Magang',
                        'Harian' => 'Harian',
                    ]),
                // [PENINGKATAN] Filter interaktif berdasarkan kantor/wilayah.
                Tables\Filters\SelectFilter::make('region_id')
                    ->label('Filter Kantor')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // [PENINGKATAN] Mengelompokkan aksi dalam dropdown (ActionGroup) agar baris lebih rapi.
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('gray'), // Warna netral untuk lihat
                    Tables\Actions\EditAction::make()->color('warning'),   // Warna kuning untuk edit
                    Tables\Actions\DeleteAction::make(),                   // Defaultnya sudah merah (danger)
                ]),
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // [PERBAIKAN & PENYEDERHANAAN] Menggunakan query yang sudah difilter untuk menghitung badge
        return static::getEloquentQuery()->count();
    }

    public static function getEloquentQuery(): Builder
    {
        // [PENYEDERHANAAN] Mengaplikasikan scope region langsung ke query utama resource
        $query = parent::getEloquentQuery();
        return static::applyRegionScope($query);
    }

    /**
     * [PENYEDERHANAAN] Fungsi terpusat untuk menerapkan filter berdasarkan region pengguna.
     * Fungsi ini digunakan oleh table() dan getNavigationBadge() untuk menghindari duplikasi kode.
     */
    protected static function applyRegionScope(Builder $query): Builder
    {
        // [PERBAIKAN] Menggunakan Auth::user() untuk mendapatkan user yang login
        $user = Auth::user();

        // Pastikan user login dan memiliki region_id
        if ($user && $user->region_id) {
            // Mengambil region user beserta relasi turunannya untuk efisiensi
            $loggedInRegion = Region::with(['children.children'])->find($user->region_id);

            if ($loggedInRegion) {
                $regionIds = [];

                if ($loggedInRegion->type === 'CABANG') {
                    // Ambil ID cabang itu sendiri
                    $regionIds[] = $loggedInRegion->id;
                    // Ambil semua ID unit di bawah cabang
                    foreach ($loggedInRegion->children as $unit) {
                        $regionIds[] = $unit->id;
                        // Ambil semua ID subunit di bawah setiap unit
                        $regionIds = array_merge($regionIds, $unit->children->pluck('id')->toArray());
                    }
                } elseif ($loggedInRegion->type === 'UNIT') {
                    // Ambil ID unit itu sendiri dan semua ID subunit di bawahnya
                    $regionIds = $loggedInRegion->children->pluck('id')->toArray();
                    $regionIds[] = $loggedInRegion->id;
                } elseif ($loggedInRegion->type === 'SUBUNIT') {
                    // Hanya ambil ID subunit itu sendiri
                    $regionIds[] = $loggedInRegion->id;
                }

                // Jika ada region ID yang terkumpul, filter query
                if (!empty($regionIds)) {
                    return $query->whereIn('region_id', $regionIds);
                }
            }
        }

        // Jika user adalah super admin (tidak punya region_id) atau kondisi lain, kembalikan semua data
        return $query;
    }
}
