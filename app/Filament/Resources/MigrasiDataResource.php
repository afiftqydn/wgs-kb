<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MigrasiDataResource\Pages;
use App\Models\MigrasiData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MigrasiDataResource extends Resource
{
    protected static ?string $model = MigrasiData::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Data Migrasi';
    protected static ?string $modelLabel = 'Data Migrasi';
    protected static ?string $pluralModelLabel = 'Data Migrasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Data Nasabah')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Pribadi')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_nasabah')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Masukkan nama lengkap nasabah')
                                            ->columnSpan(2),
                                        
                                        Forms\Components\TextInput::make('nik')
                                            ->label('NIK')
                                            ->numeric()
                                            ->unique(ignoreRecord: true)
                                            ->length(16)
                                            ->placeholder('16 digit NIK')
                                            ->prefixIcon('heroicon-o-identification'),
                                        
                                        Forms\Components\TextInput::make('nama_ibu_kandung')
                                            ->label('Nama Ibu Kandung')
                                            ->maxLength(255)
                                            ->placeholder('Nama ibu kandung'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('jenis_kelamin')
                                            ->options([
                                                'L' => 'Laki-laki',
                                                'P' => 'Perempuan'
                                            ])
                                            ->native(false)
                                            ->placeholder('Pilih jenis kelamin')
                                            ->prefixIcon('heroicon-o-user'),
                                        
                                        Forms\Components\TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->placeholder('Kota tempat lahir'),
                                        
                                        Forms\Components\DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->displayFormat('d F Y')
                                            ->maxDate(now())
                                            ->prefixIcon('heroicon-o-calendar'),
                                        
                                        Forms\Components\Select::make('agama')
                                            ->options([
                                                'ISLAM' => 'ISLAM',
                                                'KRISTEN' => 'KRISTEN', 
                                                'KATOLIK' => 'KATOLIK',
                                                'HINDU' => 'HINDU',
                                                'BUDDHA' => 'BUDDHA',
                                                'KONGHUCU' => 'KONGHUCU'
                                            ])
                                            ->searchable()
                                            ->placeholder('Pilih agama')
                                            ->prefixIcon('heroicon-o-book-open'),
                                    ])
                                    ->columns(4),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Alamat & Kontak')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat Lengkap')
                                    ->rows(3)
                                    ->placeholder('Masukkan alamat lengkap')
                                    ->columnSpanFull()
                                    ->maxLength(500),
                                
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('desa')
                                            ->label('Desa/Kelurahan')
                                            ->placeholder('Nama desa/kelurahan'),
                                        
                                        Forms\Components\TextInput::make('kecamatan')
                                            ->label('Kecamatan')
                                            ->placeholder('Nama kecamatan'),
                                        
                                        Forms\Components\TextInput::make('kota_kabupaten')
                                            ->label('Kota/Kabupaten')
                                            ->placeholder('Nama kota/kabupaten'),
                                        
                                        Forms\Components\TextInput::make('provinsi')
                                            ->label('Provinsi')
                                            ->placeholder('Nama provinsi'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\TextInput::make('no_hp')
                                    ->label('Nomor HP')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->maxLength(15),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Keanggotaan')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_register')
                                            ->label('Tanggal Register')
                                            ->displayFormat('d F Y')
                                            ->maxDate(now())
                                            ->prefixIcon('heroicon-o-calendar-days'),
                                        
                                        Forms\Components\TextInput::make('identitas_nasabah')
                                            ->label('Jenis Identitas')
                                            ->default('KTP')
                                            ->disabled()
                                            ->prefixIcon('heroicon-o-document-text'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('simpok')
                                            ->label('Simpanan Pokok')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                                            ->dehydrateStateUsing(fn ($state) => $state ? str_replace(['.', ','], '', $state) : 0)
                                            ->placeholder('0')
                                            ->prefixIcon('heroicon-o-banknotes'),
                                        
                                        Forms\Components\TextInput::make('simwajib')
                                            ->label('Simpanan Wajib') 
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                                            ->dehydrateStateUsing(fn ($state) => $state ? str_replace(['.', ','], '', $state) : 0)
                                            ->placeholder('0')
                                            ->prefixIcon('heroicon-o-currency-dollar'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->nik ? 'NIK: ' . $record->nik : '')
                    ->weight('semibold')
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state) => $state == 'L' ? 'Laki-laki' : 'Perempuan')
                    ->badge()
                    ->color(fn ($state) => $state == 'L' ? 'info' : 'pink'),
                
                Tables\Columns\TextColumn::make('kota_kabupaten')
                    ->label('Kota/Kab')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->icon('heroicon-o-map-pin'),
                
                Tables\Columns\TextColumn::make('tanggal_register')
                    ->label('Tgl Register')
                    ->date('d M Y')
                    ->sortable()
                    // ->description(fn ($record) => $record->tanggal_lahir ? 'Usia: ' . now()->diffInYears($record->tanggal_lahir) . ' tahun' : '')
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('simpok')
                    ->label('Simpanan Pokok')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-o-banknotes'),
                
                Tables\Columns\TextColumn::make('simwajib')
                    ->label('Simpanan Wajib')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan'
                    ])
                    ->label('Jenis Kelamin')
                    ->placeholder('Semua jenis kelamin'),
                
                Tables\Filters\Filter::make('tanggal_register')
                    ->form([
                        Forms\Components\DatePicker::make('registered_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('registered_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['registered_from'], fn ($q) => $q->whereDate('tanggal_register', '>=', $data['registered_from']))
                            ->when($data['registered_until'], fn ($q) => $q->whereDate('tanggal_register', '<=', $data['registered_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('gray')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->icon('heroicon-o-pencil')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data berhasil diupdate')
                                ->body('Data nasabah telah berhasil diperbarui.')
                        ),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data berhasil dihapus')
                                ->body('Data nasabah telah berhasil dihapus dari sistem.')
                        ),
                ])
                ->label('Aksi')
                ->button()
                ->color('primary')
                ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data berhasil dihapus')
                                ->body('Data nasabah telah berhasil dihapus dari sistem.')
                        ),
                ])->label('Hapus Semua'),
            ])
            ->emptyStateHeading('Belum ada data nasabah')
            ->emptyStateDescription('Mulai dengan membuat data nasabah pertama Anda.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Tambah Nasabah')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ])
            // ->deferLoading()
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMigrasiData::route('/'),
            'create' => Pages\CreateMigrasiData::route('/create'),
            'edit' => Pages\EditMigrasiData::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}