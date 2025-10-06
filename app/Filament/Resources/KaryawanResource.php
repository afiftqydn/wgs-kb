<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use App\Models\Region;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Forms\Components\ToggleButtons;

// Impor untuk SoftDeletes & Action
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;

// Impor untuk Action PDF
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;


class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Kepegawaian';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $pluralModelLabel = 'Data Karyawan';
    protected static ?string $modelLabel = 'Data Karyawan';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Buat Karyawan Baru')->tabs([
                    Tabs\Tab::make('Data Pribadi')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make('Foto Profil')
                                ->description('Unggah pas foto karyawan dengan format yang jelas.')
                                ->schema([
                                    FileUpload::make('pas_foto')
                                        ->label('Pas Foto')
                                        ->image()
                                        ->avatar()
                                        ->imageEditor()
                                        ->directory('karyawan/foto')
                                        ->columnSpanFull(),
                                ]),
                            Section::make('Informasi Utama')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_lengkap')
                                        ->prefixIcon('heroicon-o-user')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->prefixIcon('heroicon-o-envelope')
                                        ->email()
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),
                                    TextInput::make('tempat_lahir')
                                        ->prefixIcon('heroicon-o-map-pin')
                                        ->required(),
                                    DatePicker::make('tanggal_lahir')
                                        ->prefixIcon('heroicon-o-calendar-days')
                                        ->required(),
                                    
                                    // --- PENYESUAIAN BERDASARKAN SKEMA ENUM ---
                                    ToggleButtons::make('jenis_kelamin')
                                        ->label('Jenis Kelamin')
                                        ->inline()
                                        ->grouped()
                                        ->required()
                                        ->options([
                                            'Pria' => 'Pria',
                                            'Wanita' => 'Wanita',
                                        ])
                                        ->icons([
                                            'Pria' => 'heroicon-o-user',
                                            'Wanita' => 'heroicon-o-user-group',
                                        ])
                                        ->colors([
                                            'Pria' => 'info',
                                            'Wanita' => 'pink',
                                        ]),
                                    // --- AKHIR PENYESUAIAN ---

                                    Select::make('agama')
                                        ->prefixIcon('heroicon-o-hand-raised')
                                        ->options(['Islam' => 'Islam', 'Kristen Protestan' => 'Kristen Protestan', 'Kristen Katolik' => 'Kristen Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Khonghucu' => 'Khonghucu'])
                                        ->required()->searchable(),
                                    Select::make('status_pernikahan')
                                        ->prefixIcon('heroicon-o-users')
                                        ->options(['Belum Menikah' => 'Belum Menikah', 'Menikah' => 'Menikah', 'Cerai Hidup' => 'Cerai Hidup', 'Cerai Mati' => 'Cerai Mati'])
                                        ->required()->searchable(),
                                ]),
                            Section::make('Alamat')
                                ->schema([
                                    Textarea::make('alamat_ktp')
                                        ->label('Alamat Sesuai KTP')
                                        ->required(),
                                    Textarea::make('alamat_domisili')
                                        ->label('Alamat Domisili Saat Ini')
                                        ->required(),
                                ])->columns(1),
                        ]),

                    Tabs\Tab::make('Informasi Pekerjaan')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make('Detail Pekerjaan')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('jabatan')
                                        ->prefixIcon('heroicon-o-building-office')
                                        ->required(),
                                    Select::make('status_karyawan')
                                        ->prefixIcon('heroicon-o-check-badge')
                                        ->options(['Tetap/PKWTT' => 'Tetap/PKWTT', 'Kontrak/PKWT' => 'Kontrak/PKWT', 'Magang' => 'Magang', 'Harian' => 'Harian'])
                                        ->required()->live(),
                                    DatePicker::make('tanggal_bergabung')
                                        ->prefixIcon('heroicon-o-calendar-days')
                                        ->required(),
                                    DatePicker::make('tanggal_berakhir_kontrak')
                                        ->label('Tanggal Berakhir Kontrak (jika PKWT)')
                                        ->prefixIcon('heroicon-o-exclamation-triangle')
                                        ->native(false)
                                        ->visible(fn (Get $get) => $get('status_karyawan') === 'Kontrak/PKWT'),
                                    Select::make('region_id')
                                        ->label('Kantor/Wilayah')
                                        ->prefixIcon('heroicon-o-map')
                                        ->relationship('region', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('no_hp')
                                        ->label('No. HP Aktif')
                                        ->prefixIcon('heroicon-o-phone')
                                        ->tel()
                                        ->required(),
                                ]),
                        ]),

                    Tabs\Tab::make('Finansial & Legal')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Section::make('Informasi Pajak & Jaminan Sosial')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('npwp')->label('Nomor NPWP')
                                        ->prefixIcon('heroicon-o-document-text')
                                        ->unique(ignoreRecord: true)
                                        ->nullable(),
                                    TextInput::make('bpjs_ketenagakerjaan')->label('No. BPJS Ketenagakerjaan')
                                        ->prefixIcon('heroicon-o-shield-check')
                                        ->unique(ignoreRecord: true)
                                        ->nullable(),
                                    TextInput::make('bpjs_kesehatan')->label('No. BPJS Kesehatan')
                                        ->prefixIcon('heroicon-o-heart')
                                        ->unique(ignoreRecord: true)
                                        ->nullable(),
                                ]),
                            Section::make('Informasi Rekening Bank')
                                ->description('Digunakan untuk keperluan penggajian.')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('nama_bank')->label('Nama Bank')->prefixIcon('heroicon-o-building-library')->nullable(),
                                    TextInput::make('nomor_rekening')->label('Nomor Rekening')->prefixIcon('heroicon-o-credit-card')->nullable(),
                                    TextInput::make('nama_pemilik_rekening')->label('Atas Nama')->prefixIcon('heroicon-o-user')->nullable(),
                                ]),
                        ]),

                    Tabs\Tab::make('Kontak Darurat')
                        ->icon('heroicon-o-phone-arrow-up-right')
                        ->schema([
                           Section::make('Detail Kontak Darurat')
                                ->description('Informasikan siapa yang dapat dihubungi dalam keadaan darurat.')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_kontak_darurat')
                                        ->prefixIcon('heroicon-o-user')
                                        ->required(),
                                    TextInput::make('no_hp_kontak_darurat')
                                        ->prefixIcon('heroicon-o-phone')
                                        ->tel()
                                        ->required(),
                                    Select::make('hubungan_kontak_darurat')
                                        ->label('Hubungan')
                                        ->prefixIcon('heroicon-o-users')
                                        ->options(['Orang Tua' => 'Orang Tua', 'Pasangan' => 'Pasangan', 'Saudara' => 'Saudara', 'Anak' => 'Anak', 'Lainnya' => 'Lainnya'])
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tabs\Tab::make('Dokumen Digital')
                        ->icon('heroicon-o-document-arrow-up')
                        ->schema([
                            Section::make('Unggah Dokumen Penting')
                                ->description('Pastikan file yang diunggah dapat terbaca dengan jelas.')
                                ->schema([
                                    FileUpload::make('file_ktp')->label('Upload KTP (PDF/Gambar)')
                                        ->directory('karyawan/ktp')
                                        ->acceptedFileTypes(['application/pdf', 'image/*']),
                                    FileUpload::make('file_npwp')->label('Upload NPWP (PDF/Gambar)')
                                        ->directory('karyawan/npwp')
                                        ->acceptedFileTypes(['application/pdf', 'image/*']),
                                    FileUpload::make('file_perjanjian_kerja')->label('Upload Surat Perjanjian Kerja (PDF)')
                                        ->directory('karyawan/kontrak')
                                        ->acceptedFileTypes(['application/pdf']),
                                ])->columns(1),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('pas_foto')->label('Foto')->circular()->size(50),
                Tables\Columns\TextColumn::make('nama_lengkap')->label('Nama & Jabatan')->description(fn (Karyawan $record): string => $record->jabatan)->searchable(['nama_lengkap', 'jabatan'])->sortable(),
                Tables\Columns\TextColumn::make('status_karyawan')->label('Status')->badge()->colors(['success' => 'Tetap/PKWTT', 'warning' => 'Kontrak/PKWT', 'info' => 'Magang', 'gray' => 'Harian'])->searchable(),
                Tables\Columns\TextColumn::make('region.name')->label('Kantor')->badge()->color('warning')->icon('heroicon-o-building-office')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP')->icon('heroicon-o-phone')->searchable()->copyable()->copyMessage('No. HP disalin!')->url(fn (Karyawan $record): ?string => $record->no_hp ? "tel:{$record->no_hp}" : null)->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')->label('Tanggal Dihapus')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_karyawan')->label('Filter Status')->options(['Tetap/PKWTT' => 'Tetap/PKWTT', 'Kontrak/PKWT' => 'Kontrak/PKWT', 'Magang' => 'Magang', 'Harian' => 'Harian']),
                Tables\Filters\SelectFilter::make('region_id')->label('Filter Kantor')->relationship('region', 'name')->searchable()->preload(),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('gray'),
                    Tables\Actions\EditAction::make()->color('warning'),
                    Action::make('downloadPdf')
                        ->label('Download Data Karyawan')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Karyawan $record) {
                            $pdf = Pdf::loadView('pdf.karyawan-detail', ['karyawan' => $record]);
                            $filename = "detail-karyawan-{$record->nama_lengkap}.pdf";
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                $filename
                            );
                        }),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
            // ->infinite();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
        
        return static::applyRegionScope($query);
    }

    public static function getNavigationBadge(): ?string
    {
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
        
        return $query;
    }
}
 