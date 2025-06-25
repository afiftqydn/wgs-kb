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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

// --- [PENTING] Impor semua class yang dibutuhkan untuk SoftDeletes ---
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;


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
                            FileUpload::make('pas_foto')->label('Pas Foto')->image()->avatar()->imageEditor()->directory('karyawan/foto')->columnSpanFull(),
                            TextInput::make('nama_lengkap')->required()->maxLength(255),
                            TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                            Grid::make(2)->schema([
                                TextInput::make('tempat_lahir')->required(),
                                DatePicker::make('tanggal_lahir')->required(),
                            ]),
                            Radio::make('jenis_kelamin')->options(['Pria' => 'Pria', 'Wanita' => 'Wanita'])->required(),
                            Select::make('agama')->options(['Islam' => 'Islam', 'Kristen Protestan' => 'Kristen Protestan', 'Kristen Katolik' => 'Kristen Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Khonghucu' => 'Khonghucu'])->required()->searchable(),
                            Select::make('status_pernikahan')->options(['Belum Menikah' => 'Belum Menikah', 'Menikah' => 'Menikah', 'Cerai Hidup' => 'Cerai Hidup', 'Cerai Mati' => 'Cerai Mati'])->required()->searchable(),
                            Textarea::make('alamat_ktp')->required()->columnSpanFull(),
                            Textarea::make('alamat_domisili')->required()->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Informasi Pekerjaan')->icon('heroicon-o-briefcase')->schema([
                        TextInput::make('jabatan')->required(),
                        Select::make('status_karyawan')->options(['Tetap/PKWTT' => 'Tetap/PKWTT', 'Kontrak/PKWT' => 'Kontrak/PKWT', 'Magang' => 'Magang', 'Harian' => 'Harian'])->required()->live(),
                        DatePicker::make('tanggal_bergabung')->required(),
                        DatePicker::make('tanggal_berakhir_kontrak')->label('Tanggal Berakhir Kontrak (jika PKWT)')->native(false)->visible(fn (Get $get) => $get('status_karyawan') === 'Kontrak/PKWT'),
                        Select::make('region_id')->label('Kantor/Wilayah')->relationship('region', 'name')->required()->searchable()->preload(),
                        TextInput::make('no_hp')->label('No. HP Aktif')->tel()->required(),
                    ]),
                    Tabs\Tab::make('Finansial & Legal')->icon('heroicon-o-banknotes')->schema([
                        TextInput::make('npwp')->label('Nomor NPWP')->unique(ignoreRecord: true)->nullable(),
                        TextInput::make('bpjs_ketenagakerjaan')->label('Nomor BPJS Ketenagakerjaan')->unique(ignoreRecord: true)->nullable(),
                        TextInput::make('bpjs_kesehatan')->label('Nomor BPJS Kesehatan')->unique(ignoreRecord: true)->nullable(),
                        Section::make('Informasi Rekening Bank')->schema([
                            TextInput::make('nama_bank')->label('Nama Bank')->nullable(),
                            TextInput::make('nomor_rekening')->label('Nomor Rekening')->nullable(),
                            TextInput::make('nama_pemilik_rekening')->label('Atas Nama')->nullable(),
                        ])->columns(3),
                    ]),
                    Tabs\Tab::make('Kontak Darurat')->icon('heroicon-o-phone-arrow-up-right')->schema([
                        TextInput::make('nama_kontak_darurat')->required(),
                        Select::make('hubungan_kontak_darurat')->label('Hubungan')->options(['Orang Tua' => 'Orang Tua', 'Pasangan' => 'Pasangan', 'Saudara' => 'Saudara', 'Anak' => 'Anak', 'Lainnya' => 'Lainnya'])->required(),
                        TextInput::make('no_hp_kontak_darurat')->tel()->required(),
                    ]),
                    Tabs\Tab::make('Dokumen Digital')->icon('heroicon-o-document-arrow-up')->schema([
                        FileUpload::make('file_ktp')->label('Upload KTP')->directory('karyawan/ktp')->acceptedFileTypes(['application/pdf', 'image/*']),
                        FileUpload::make('file_npwp')->label('Upload NPWP')->directory('karyawan/npwp')->acceptedFileTypes(['application/pdf', 'image/*']),
                        FileUpload::make('file_perjanjian_kerja')->label('Upload Surat Perjanjian Kerja')->directory('karyawan/kontrak')->acceptedFileTypes(['application/pdf']),
                    ])->columns(1),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('pas_foto')->label('Foto')->circular()->size(50),
                Tables\Columns\TextColumn::make('nama_lengkap')->label('Nama & Jabatan')->description(fn (Karyawan $record): string => $record->jabatan)->searchable(['nama_lengkap', 'jabatan'])->sortable(),
                Tables\Columns\BadgeColumn::make('status_karyawan')->label('Status')->colors(['success' => 'Tetap/PKWTT', 'warning' => 'Kontrak/PKWT', 'info' => 'Magang', 'gray' => 'Harian'])->searchable(),
                Tables\Columns\TextColumn::make('region.name')->label('Kantor')->badge()->icon('heroicon-o-building-office')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP')->icon('heroicon-o-phone')->searchable()->copyable()->copyMessage('No. HP disalin!')->url(fn (Karyawan $record): ?string => $record->no_hp ? "tel:{$record->no_hp}" : null)->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')->label('Tanggal Dihapus')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_karyawan')->label('Filter Status')->options(['Tetap/PKWTT' => 'Tetap/PKWTT', 'Kontrak/PKWT' => 'Kontrak/PKWT', 'Magang' => 'Magang', 'Harian' => 'Harian']),
                Tables\Filters\SelectFilter::make('region_id')->label('Filter Kantor')->relationship('region', 'name')->searchable()->preload(),
                // Filter untuk menampilkan data yang di-soft-delete
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('gray'),
                    Tables\Actions\EditAction::make()->color('warning'),
                    // Aksi-aksi untuk Soft Deletes
                    DeleteAction::make(), // Otomatis melakukan soft delete
                    RestoreAction::make(), // Hanya muncul pada data yang terhapus
                    ForceDeleteAction::make(), // Hanya muncul pada data yang terhapus
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Panggil query parent dan hapus scope global SoftDeletes agar filter Trashed berfungsi
        $query = parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
        
        // Terapkan scope region kustom Anda setelahnya
        return static::applyRegionScope($query);
    }

    public static function getNavigationBadge(): ?string
    {
        // Hitung badge navigasi hanya dari data yang aktif (tidak terhapus) dan sesuai region
        $query = static::getModel()::query()->whereNull('deleted_at');
        return static::applyRegionScope($query)->count();
    }
    
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    /**
     * Fungsi terpusat untuk menerapkan filter berdasarkan region pengguna.
     */
    protected static function applyRegionScope(Builder $query): Builder
    {
        $user = Auth::user();
        if ($user && $user->region_id) {
            $loggedInRegion = Region::with(['children.children'])->find($user->region_id);
            if ($loggedInRegion) {
                $regionIds = [];
                if ($loggedInRegion->type === 'CABANG') {
                    $regionIds[] = $loggedInRegion->id;
                    foreach ($loggedInRegion->children as $unit) {
                        $regionIds[] = $unit->id;
                        $regionIds = array_merge($regionIds, $unit->children->pluck('id')->toArray());
                    }
                } elseif ($loggedInRegion->type === 'UNIT') {
                    $regionIds = $loggedInRegion->children->pluck('id')->toArray();
                    $regionIds[] = $loggedInRegion->id;
                } elseif ($loggedInRegion->type === 'SUBUNIT') {
                    $regionIds[] = $loggedInRegion->id;
                }

                if (!empty($regionIds)) {
                    return $query->whereIn('region_id', $regionIds);
                }
            }
        }
        
        // Jika user adalah super admin (tidak punya region_id) atau kondisi lain, kembalikan query apa adanya
        return $query;
    }
}