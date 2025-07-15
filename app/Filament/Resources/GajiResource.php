<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Models\Gaji;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Impor untuk Action PDF
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Finance'; // Atau sesuaikan grupnya

    protected static ?string $pluralModelLabel = 'Data Gaji Karyawan';

    protected static ?int $navigationSort = 10;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Karyawan & Periode')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->relationship('karyawan', 'nama_lengkap')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_bayar')
                            ->required(),
                        Forms\Components\Select::make('periode_bulan')
                            ->options([
                                'Januari' => 'Januari', 'Februari' => 'Februari', 'Maret' => 'Maret',
                                'April' => 'April', 'Mei' => 'Mei', 'Juni' => 'Juni', 'Juli' => 'Juli',
                                'Agustus' => 'Agustus', 'September' => 'September', 'Oktober' => 'Oktober',
                                'November' => 'November', 'Desember' => 'Desember',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('periode_tahun')
                            ->numeric()
                            ->required()
                            ->default(date('Y')),
                    ]),

                Forms\Components\Section::make('Rincian Pendapatan')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('gaji_pokok')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('transport')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('tun_kehadiran')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('tun_komunikasi')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('lembur')->numeric()->prefix('Rp')->required()->default(0),
                    ]),

                Forms\Components\Section::make('Rincian Potongan')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('bpjs')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('absen')->numeric()->prefix('Rp')->required()->default(0),
                        Forms\Components\TextInput::make('kas_bon')->numeric()->prefix('Rp')->required()->default(0),
                    ]),

                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama_lengkap')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_bulan')
                    ->label('Periode')
                    ->formatStateUsing(fn ($state, Gaji $record) => "{$state} {$record->periode_tahun}")
                    ->searchable(['periode_bulan', 'periode_tahun']),
                Tables\Columns\TextColumn::make('tanggal_bayar')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('total_diterima')
                    ->label('Jumlah Diterima')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Filter Karyawan')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    // Aksi untuk download PDF
                    Action::make('downloadSlipGaji')
                        ->label('Slip Gaji')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Gaji $record) {
                            $pdf = Pdf::loadView('pdf.slip-gaji', ['gaji' => $record]);
                            $filename = "slip-gaji-{$record->karyawan->nama_lengkap}-{$record->periode_bulan}-{$record->periode_tahun}.pdf";
                            return response()->streamDownload(fn () => print($pdf->output()), $filename);
                        }),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGajis::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'view' => Pages\ViewGaji::route('/{record}'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}