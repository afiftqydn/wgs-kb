<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Models\Gaji;
use App\Models\Region; // <-- Tambahkan import model Region
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Kepegawaian';
    protected static ?string $pluralModelLabel = 'Penggajian';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        // Bagian form tidak ada perubahan
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
                
                Tables\Columns\TextColumn::make('karyawan.region.name')
                    ->label('Kantor/Wilayah')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode_bulan')
                    ->label('Periode')
                    ->formatStateUsing(fn ($state, Gaji $record) => "{$state} {$record->periode_tahun}")
                    ->searchable(['periode_bulan', 'periode_tahun']),
                Tables\Columns\TextColumn::make('tanggal_bayar')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('total_diterima')
                    ->label('Jumlah Diterima')
                    ->money('IDR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Kita tidak bisa mengurutkan berdasarkan accessor secara langsung.
                        // Kita harus menggunakan query mentah untuk menghitung nilainya dan mengurutkannya.
                        // Pastikan perhitungan ini sesuai dengan accessor getTotalDiterimaAttribute di Model Gaji Anda.
                        return $query->orderByRaw(
                            '(gaji_pokok + transport + tun_kehadiran + tun_komunikasi + lembur) - (bpjs + absen + kas_bon) ' . $direction
                        );
                    }),
                // ===============================

                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->label('Kantor/Wilayah')
                    ->options(fn () => Region::pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value) => $query->whereHas('karyawan', fn($q) => $q->where('region_id', $value))
                        );
                    })
                    ->searchable()
                    ->preload(),

                // === PERUBAHAN: TAMBAHKAN FILTER PERIODE ===
                Tables\Filters\SelectFilter::make('periode_bulan')
                    ->label('Periode Bulan')
                    ->options([
                        'Januari' => 'Januari', 'Februari' => 'Februari', 'Maret' => 'Maret',
                        'April' => 'April', 'Mei' => 'Mei', 'Juni' => 'Juni', 'Juli' => 'Juli',
                        'Agustus' => 'Agustus', 'September' => 'September', 'Oktober' => 'Oktober',
                        'November' => 'November', 'Desember' => 'Desember',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'], fn ($query, $value) => $query->where('periode_bulan', $value))),

                Tables\Filters\SelectFilter::make('periode_tahun')
                    ->label('Tahun')
                    ->options(fn () => Gaji::query()->select('periode_tahun')->distinct()->pluck('periode_tahun', 'periode_tahun')->sortDesc())
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'], fn ($query, $value) => $query->where('periode_tahun', $value))),
                // ============================================

                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Nama Karyawan')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()->color('warning'),
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
            // 'view' => Pages\ViewGaji::route('/{record}'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}


