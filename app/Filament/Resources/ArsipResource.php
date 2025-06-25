<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipResource\Pages;
use App\Models\Arsip;
use App\Models\Customer;
use App\Models\LoanApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ArsipResource extends Resource
{
    protected static ?string $model = Arsip::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Data Arsip';
    protected static ?string $pluralModelLabel = 'Data Arsip';
    protected static ?string $modelLabel = 'Data Arsip';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Dokumen')
                            ->schema([
                                Forms\Components\TextInput::make('nama_arsip')
                                    ->label('Nama Arsip / Dokumen')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('kategori')
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        'Dokumen Pengajuan' => 'Dokumen Pengajuan',
                                        'Dokumen Perjanjian' => 'Dokumen Perjanjian',
                                        'Dokumen Jaminan' => 'Dokumen Jaminan',
                                        'Dokumen Identitas Nasabah' => 'Dokumen Identitas Nasabah',
                                        'Dokumen Keuangan' => 'Dokumen Keuangan',
                                        'Korespondensi' => 'Korespondensi',
                                        'Dokumen Internal' => 'Dokumen Internal',
                                    ]),
                                Forms\Components\DatePicker::make('tanggal_dokumen')
                                    ->required(),
                                Forms\Components\RichEditor::make('keterangan')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('File Arsip')
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->label('Upload File')
                                    ->required()
                                    ->directory('arsip-dokumen')
                                    ->disk('public')
                                    ->preserveFilenames()
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                                Forms\Components\TextInput::make('lokasi_fisik')
                                    ->label('Lokasi Penyimpanan Fisik')
                                    ->placeholder('Contoh: Lemari C, Rak 2, Map Biru')
                                    ->maxLength(255),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status & Keterkaitan')
                            ->schema([
                                Forms\Components\TextInput::make('kode_arsip')
                                    ->label('Kode Arsip')
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Akan dibuat otomatis'),
                                
                                // *** PERBAIKAN 1 ***
                                Forms\Components\Select::make('customer_id')
                                    ->label('Terkait dengan Nasabah')
                                    ->relationship('customer', 'name') // Menggunakan relasi 'customer' dan kolom 'name'
                                    ->searchable()
                                    ->preload(),

                                // *** PERBAIKAN 2 ***
                                Forms\Components\Select::make('loan_application_id')
                                    ->label('Terkait dengan Pengajuan')
                                    // Menggunakan relasi 'loanApplication' dan kolom 'application_number' (sesuaikan jika perlu)
                                    ->relationship('loanApplication', 'application_number') 
                                    ->searchable()
                                    ->preload(),
                                    
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Aktif' => 'Aktif',
                                        'Tidak Aktif' => 'Tidak Aktif',
                                        'Dalam Peminjaman' => 'Dalam Peminjaman',
                                        'Sudah Dimusnahkan' => 'Sudah Dimusnahkan',
                                    ])
                                    ->default('Aktif')
                                    ->required(),
                                Forms\Components\DatePicker::make('tanggal_retensi')
                                    ->label('Tanggal Akhir Retensi'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_arsip')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_arsip')
                    ->label('Nama Arsip')
                    ->limit(35)
                    ->tooltip(fn (Arsip $record): string => $record->nama_arsip)
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                
                // *** PERBAIKAN 3 ***
                Tables\Columns\TextColumn::make('customer.name') // Menggunakan relasi 'customer' dan kolom 'name'
                    ->label('Nasabah')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada'),

                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'Aktif' => 'heroicon-o-check-circle',
                        'Tidak Aktif' => 'heroicon-o-x-circle',
                        'Dalam Peminjaman' => 'heroicon-o-credit-card',
                        'Sudah Dimusnahkan' => 'heroicon-o-trash',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Tidak Aktif' => 'danger',
                        'Dalam Peminjaman' => 'warning',
                        'Sudah Dimusnahkan' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl. Diarsip')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options([
                        'Dokumen Pengajuan' => 'Dokumen Pengajuan',
                        'Dokumen Perjanjian' => 'Dokumen Perjanjian',
                        'Dokumen Jaminan' => 'Dokumen Jaminan',
                        'Dokumen Identitas Nasabah' => 'Dokumen Identitas Nasabah',
                        'Dokumen Keuangan' => 'Dokumen Keuangan',
                        'Korespondensi' => 'Korespondensi',
                        'Dokumen Internal' => 'Dokumen Internal',
                    ]),
                SelectFilter::make('status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Arsip $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListArsips::route('/'),
            'create' => Pages\CreateArsip::route('/create'),
            'edit' => Pages\EditArsip::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}