<?php

namespace App\Filament\Resources\PomigorDepotResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker; // Untuk transaction_date
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use App\Models\PomigorStockMovement; // Untuk type pada enum jika diperlukan

class PomigorStockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    // Judul untuk Relation Manager ini
    protected static ?string $title = 'Histori & Input Pergerakan Stok';

    // protected static ?string $recordTitleAttribute = 'transaction_date'; // Sesuai yang di-generate

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'REFILL' => 'Pengisian Ulang (Refill)',
                        'SALE_REPORTED' => 'Laporan Penjualan',
                        'ADJUSTMENT_INCREASE' => 'Penyesuaian Penambahan',
                        'ADJUSTMENT_DECREASE' => 'Penyesuaian Pengurangan',
                    ])
                    ->required(),
                TextInput::make('quantity_liters')
                    ->label('Jumlah (Liter)')
                    ->numeric()
                    ->required()
                    ->minValue(0.01) // Jumlah harus lebih dari 0
                    ->helperText('Masukkan jumlah dalam liter.'),
                DateTimePicker::make('transaction_date')
                    ->label('Tanggal & Waktu Transaksi Aktual')
                    ->default(now()) // Default ke waktu saat ini
                    ->maxDate(now()) // Transaksi tidak boleh di masa depan
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan/Keterangan')
                    ->nullable()
                    ->columnSpanFull(),
                // Kolom recorded_by akan diisi otomatis oleh model event PomigorStockMovement
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('transaction_date')
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tgl Transaksi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'REFILL' => 'success',
                        'SALE_REPORTED' => 'danger',
                        'ADJUSTMENT_INCREASE' => 'info',
                        'ADJUSTMENT_DECREASE' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_liters')
                    ->label('Jumlah (Liter)')
                    ->numeric()
                    ->formatStateUsing(function (string $state, PomigorStockMovement $record) {
                        // Beri tanda positif atau negatif berdasarkan tipe transaksi
                        if (in_array($record->transaction_type, ['REFILL', 'ADJUSTMENT_INCREASE'])) {
                            return '+ ' . number_format(floatval($state), 2, ',', '.');
                        } elseif (in_array($record->transaction_type, ['SALE_REPORTED', 'ADJUSTMENT_DECREASE'])) {
                            return '- ' . number_format(floatval($state), 2, ',', '.');
                        }
                        return number_format(floatval($state), 2, ',', '.');
                    })
                    ->alignRight() // Rata kanan untuk angka
                    ->sortable(),
                TextColumn::make('recorder.name') // Relasi 'recorder' di model PomigorStockMovement
                    ->label('Dicatat Oleh')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->wrap()
                    ->limit(50) // Batasi teks di tabel
                    ->toggleable(isToggledHiddenByDefault: true), // Bisa disembunyikan
                TextColumn::make('created_at')
                    ->label('Tgl Dicatat Sistem')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('transaction_date', 'desc') // Urutkan berdasarkan tanggal transaksi terbaru
            // ->filters([
            //     SelectFilter::make('transaction_type')
            //         ->options([
            //             'REFILL' => 'Pengisian Ulang (Refill)',
            //             'SALE_REPORTED' => 'Laporan Penjualan',
            //             'ADJUSTMENT_INCREASE' => 'Penyesuaian Penambahan',
            //             'ADJUSTMENT_DECREASE' => 'Penyesuaian Pengurangan',
            //         ]),
            //     // Anda bisa menambahkan filter berdasarkan tanggal jika diperlukan
            // ])
            ->headerActions([
                Tables\Actions\CreateAction::make() // Tombol untuk menambah data pergerakan stok baru
                    ->label('Catat Pergerakan Stok Baru'), 
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
}