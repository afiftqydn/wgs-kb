<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model; 
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ActivityLogResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Spatie\Activitylog\Models\Activity; // Model Activity dari Spatie
use App\Models\User; // Untuk filter by causer atau menampilkan nama causer

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administrasi Sistem';
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')
                    ->label('Nama Log (Kategori)')
                    ->disabled(),
                Forms\Components\TextInput::make('event') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('Event (Kejadian)')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subject_type')
                    ->label('Tipe Objek Terkait')
                    ->disabled(),
                Forms\Components\TextInput::make('subject_id')
                    ->label('ID Objek Terkait')
                    ->disabled(),
                Forms\Components\TextInput::make('causer_type')
                    ->label('Tipe Pelaku Aksi')
                    ->disabled(),
                Forms\Components\TextInput::make('causer_id')
                    ->label('ID Pelaku Aksi')
                    ->disabled(),
                Forms\Components\TextInput::make('batch_uuid') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('UUID Batch')
                    ->disabled()
                    ->helperText('ID unik jika aktivitas ini bagian dari sebuah batch.'),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Waktu Dibuat')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('updated_at') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('Waktu Diperbarui')
                    ->disabled(),
                Forms\Components\KeyValue::make('properties.old')
                    ->label('Data Lama (Sebelum Perubahan)')
                    ->disabled()
                    ->columnSpanFull()
                    ->helperText('Menampilkan atribut yang berubah (jika ada).'),
                Forms\Components\KeyValue::make('properties.attributes')
                    ->label('Data Baru (Setelah Perubahan/Dibuat)')
                    ->disabled()
                    ->columnSpanFull()
                    ->helperText('Menampilkan atribut yang diubah/dibuat (jika ada).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Log')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Kategori Log')->sortable()->searchable()->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi Aktivitas')->searchable()->wrap()->limit(100),
                Tables\Columns\TextColumn::make('event') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('Event')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower($state ?? '')) {
                        'created' => 'success',
                        'updated' => 'primary',
                        'deleted' => 'danger',
                        'login' => 'info', // Contoh untuk event login kustom
                        default => 'gray',
                    })
                    ->sortable()->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Tipe Objek')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID Objek')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pelaku Aksi')->placeholder('Sistem/Tidak Diketahui')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHasMorph('causer', [User::class], function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Dibuat')->dateTime('d M Y, H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('updated_at') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('Waktu Diperbarui')->dateTime('d M Y, H:i:s')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('batch_uuid') // <-- KOLOM BARU DITAMBAHKAN
                    ->label('UUID Batch')->searchable()->toggleable(isToggledHiddenByDefault: true)->limit(15),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->options(fn (): array => Activity::query()->select('log_name')->distinct()->orderBy('log_name')->pluck('log_name', 'log_name')->all())
                    ->label('Kategori Log'),
                SelectFilter::make('event')
                    ->options(fn (): array => Activity::query()->select('event')->distinct()->orderBy('event')->whereNotNull('event')->pluck('event', 'event')->all())
                    ->label('Event'),
                SelectFilter::make('causer_id') // <-- INI KEMUNGKINAN PENYEBABNYA
                    ->label('Pelaku Aksi')
                    ->relationship('causer', 'name') // Mencoba mengambil 'name' dari relasi 'causer'
                    ->searchable(), // Preload bisa jadi mencoba query yang tidak tepat dalam beberapa kasus
                DateRangeFilter::make('created_at')
                    ->label('Rentang Waktu Kejadian'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Tim IT'); 
    }

    public static function canCreate(): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}