<?php

namespace App\Filament\Resources\LoanApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // <-- PASTIKAN INI DI-IMPORT

class WorkflowsRelationManager extends RelationManager
{
    protected static string $relationship = 'workflows';
    protected static ?string $title = 'Histori Alur Kerja';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('to_status')->disabled(),
                Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Waktu')->dateTime('d M Y, H:i:s')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('from_status')
                    ->label('Dari Status')->badge()->placeholder('AWAL')
                    ->color(fn (?string $state): string => match ($state) {
                        null => 'gray', 'DRAFT' => 'gray', 'SUBMITTED' => 'info',
                        'UNDER_REVIEW' => 'warning', 'ESCALATED' => 'primary',
                        'APPROVED' => 'success', 'REJECTED' => 'danger',
                        default => 'secondary',
                    })->searchable(),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('Ke Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT' => 'gray', 'SUBMITTED' => 'info', 'UNDER_REVIEW' => 'warning',
                        'ESCALATED' => 'primary', 'APPROVED' => 'success', 'REJECTED' => 'danger',
                        default => 'secondary',
                    })->searchable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Diproses Oleh')->searchable()->placeholder('Sistem'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')->wrap()->searchable()->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public function canCreate(): bool { return false; }

    // Jika Anda meng-override canEdit, pastikan type-hintnya juga benar:
    // public function canEdit(Model $record): bool 
    // { 
    //     return false; 
    // }

    public function canDelete(Model $record): bool // <-- PERBAIKI TYPE-HINT DI SINI
    { 
        return false; 
    }

    public function canDeleteAny(): bool 
    { 
        return false; 
    }
}