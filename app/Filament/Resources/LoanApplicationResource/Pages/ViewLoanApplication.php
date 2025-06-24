<?php

namespace App\Filament\Resources\LoanApplicationResource\Pages;

use App\Filament\Resources\LoanApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanApplication;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;

class ViewLoanApplication extends ViewRecord
{
    protected static string $resource = LoanApplicationResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $record = $this->record;

        return [
            Actions\EditAction::make()->visible(fn (): bool => $record->status === 'DRAFT'),

            // --- AKSI ALUR KERJA UNTUK ADMIN UNIT ---
            Actions\Action::make('verifyByAdminUnit')
                ->label('Verifikasi Data')->icon('heroicon-o-document-check')->color('primary')
                ->requiresConfirmation()->modalDescription('Pastikan Anda telah melakukan cross-check data pengajuan dengan dokumen fisik.')
                ->visible(fn (): bool => $record->status === 'SUBMITTED' && $record->assigned_to === $user->id && is_null($record->admin_unit_verified_at) && $user->hasRole('Admin Unit'))
                ->action(function () {
                    $this->record->update(['admin_unit_verified_at' => now()]);
                    FilamentNotification::make()->title('Data berhasil diverifikasi')->success()->send();
                    $this->refreshFormData(['admin_unit_verified_at']);
                }),
            Actions\Action::make('processToAnalyst')
                ->label('Proses & Teruskan ke Analis')->icon('heroicon-o-arrow-right-circle')->color('info')
                ->visible(fn (): bool => $record->status === 'SUBMITTED' && $record->assigned_to === $user->id && !is_null($record->admin_unit_verified_at) && $user->hasRole('Admin Unit'))
                ->form([
                    Select::make('assigned_to_analyst')->label('Tugaskan ke Analis Unit')
                        ->options(fn () => User::where('region_id', $this->record->processing_region_id)->whereHas('roles', fn ($q) => $q->where('name', 'Analis Unit'))->pluck('name', 'id'))
                        ->required()->searchable(),
                    Textarea::make('workflow_notes_admin_unit')->label('Catatan dari Admin Unit')->nullable(),
                ])->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_admin_unit'] ?? 'Data telah diverifikasi dan diteruskan.');
                    $this->record->status = 'UNDER_REVIEW';
                    $this->record->assigned_to = $data['assigned_to_analyst'];
                    $this->record->save();
                    FilamentNotification::make()->title('Permohonan Diproses')->success()->send();
                }),

            // --- AKSI ALUR KERJA UNTUK ANALIS UNIT ---
            Actions\Action::make('verifyByAnalis')
                ->label('Selesaikan & Verifikasi Analisis')->icon('heroicon-o-document-check')->color('primary')
                ->requiresConfirmation()->modalDescription('Pastikan Anda telah menganalisis permohonan ini dengan lengkap.')
                ->visible(fn (): bool => $record->status === 'UNDER_REVIEW' && $record->assigned_to === $user->id && is_null($record->analis_verified_at) && $user->hasRole('Analis Unit'))
                ->action(function () {
                    $this->record->update(['analis_verified_at' => now()]);
                    FilamentNotification::make()->title('Analisis telah diverifikasi')->success()->send();
                    $this->refreshFormData(['analis_verified_at']);
                }),
            Actions\Action::make('approveNormallyByAnalyst')
                ->label('Setujui (Nominal Normal)')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(function () use ($record, $user): bool {
                    $product = $record->productType;
                    $amountCheck = ($product && $product->escalation_threshold !== null) ? $record->amount_requested <= $product->escalation_threshold : true;
                    return $record->status === 'UNDER_REVIEW' && $record->assigned_to === $user->id && !is_null($record->analis_verified_at) && $amountCheck && $user->hasRole('Analis Unit');
                })->form([
                    Textarea::make('workflow_notes_analis_approve_normal')->label('Catatan Persetujuan Analis (Opsional)')->nullable(),
                    Select::make('assigned_to_kepala_unit_for_review')->label('Teruskan ke Kepala Unit untuk Review')
                        ->options(fn () => User::where('region_id', $this->record->processing_region_id)->whereHas('roles', fn ($q) => $q->where('name', 'Kepala Unit'))->pluck('name', 'id'))
                        ->required()->searchable(),
                ])->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_analis_approve_normal'] ?? 'Disetujui oleh Analis Unit.');
                    $this->record->status = 'APPROVED';
                    $this->record->assigned_to = $data['assigned_to_kepala_unit_for_review'];
                    $this->record->save();
                    FilamentNotification::make()->title('Permohonan Disetujui (Analis)')->success()->send();
                }),
            Actions\Action::make('escalateToCabangByAnalyst')
                ->label('Eskalasi ke Cabang')->icon('heroicon-o-arrow-up-circle')->color('warning')
                ->visible(function () use ($record, $user): bool {
                    // Hapus pengecekan nominal, biarkan analis yang memutuskan
                    return $record->status === 'UNDER_REVIEW' && 
                          $record->assigned_to === $user->id && 
                          !is_null($record->analis_verified_at) && 
                          $user->hasRole('Analis Unit');
                })->form([
                    Textarea::make('workflow_notes_analis_escalate')->label('Rekomendasi Eskalasi Analis (Wajib)')->required()->minLength(10),
                    Select::make('assigned_to_kepala_cabang')->label('Tugaskan ke Kepala Cabang')
                        ->options(fn () => User::whereHas('roles', fn ($q) => $q->where('name', 'Kepala Cabang'))->pluck('name', 'id'))
                        ->required()->searchable(),
                ])->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_analis_escalate']);
                    $this->record->status = 'ESCALATED';
                    $this->record->assigned_to = $data['assigned_to_kepala_cabang'];
                    $this->record->save();
                    FilamentNotification::make()->title('Permohonan Diekskalasi')->info()->send();
                }),
            Actions\Action::make('rejectApplicationByAnalyst')
                ->label('Tolak Permohonan (Analis)')->icon('heroicon-o-hand-thumb-down')->color('danger')
                ->visible(fn (): bool => $record->status === 'UNDER_REVIEW' && $record->assigned_to === $user->id && !is_null($record->analis_verified_at) && $user->hasRole('Analis Unit'))
                ->form([
                    Textarea::make('workflow_notes_analis_reject')->label('Alasan Penolakan Analis (Wajib)')->required()->minLength(10),
                    Select::make('assigned_to_kepala_unit_for_review')->label('Teruskan ke Kepala Unit untuk Review')
                        ->options(fn () => User::where('region_id', $this->record->processing_region_id)->whereHas('roles', fn ($q) => $q->where('name', 'Kepala Unit'))->pluck('name', 'id'))
                        ->required()->searchable(),
                ])->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_analis_reject']);
                    $this->record->status = 'REJECTED';
                    $this->record->assigned_to = $data['assigned_to_kepala_unit_for_review'];
                    $this->record->save();
                    FilamentNotification::make()->title('Permohonan Ditolak (Analis)')->danger()->send();
                }),

                // --- AKSI EKSPOR PDF GABUNGAN ---
                Actions\Action::make('exportCompletePackage')
                    ->label('Cetak Dokumen Lengkap')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (): string => route('loanApplication.completePackagePdf', $this->record))
                    ->openUrlInNewTab()
                    ->visible(function () use ($user, $record): bool {
                        // Tampil jika status sudah final DAN peran adalah Admin Unit atau Admin Cabang
                        $isFinalStatus = in_array($record->status, ['APPROVED', 'REJECTED']);
                        $hasAccessRole = $user->hasAnyRole(['Admin Unit', 'Admin Cabang', 'Tim IT']); // Tim IT tetap bisa

                        // Logika "setelah direview kepala masing-masing" bisa diterjemahkan sebagai
                        // sudah tidak ada lagi yang ditugaskan (assigned_to is NULL).
                        $isProcessFinished = is_null($record->assigned_to);

                        return $isFinalStatus && $hasAccessRole && $isProcessFinished;
                    }),



            // --- AKSI UNTUK KEPALA UNIT & KEPALA CABANG ---
            Actions\Action::make('markAsReviewedByKepalaUnit')
                ->label('Tandai Sudah Direview')->icon('heroicon-o-check-circle')->color('info')
                ->visible(fn (): bool => in_array($record->status, ['APPROVED', 'REJECTED']) && $record->assigned_to === $user->id && $user->hasRole('Kepala Unit'))
                ->requiresConfirmation()->modalButton('Ya, Tandai Sudah Direview')
                ->form([ Textarea::make('workflow_notes_kepala_unit_review')->label('Catatan Review (Opsional)')->nullable() ])
                ->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_kepala_unit_review'] ?? 'Telah direview oleh Kepala Unit.');
                    $this->record->assigned_to = null; 
                    $this->record->save(); 
                    FilamentNotification::make()->title('Permohonan Direview')->info()->send();
                }),
            Actions\Action::make('approveEscalatedApplication')
                ->label('Setujui Eskalasi')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(fn (): bool => $record->status === 'ESCALATED' && $record->assigned_to === $user->id && $user->hasRole('Kepala Cabang'))
                ->form([ Textarea::make('workflow_notes_kacab_approve')->label('Catatan Persetujuan (Opsional)')->nullable() ])
                ->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_kacab_approve'] ?? 'Eskalasi disetujui oleh Kepala Cabang.');
                    $this->record->status = 'APPROVED';
                    $this->record->assigned_to = null; 
                    $this->record->save();
                    FilamentNotification::make()->title('Eskalasi Disetujui')->success()->send();
                }),
            Actions\Action::make('rejectEscalatedApplication')
                ->label('Tolak Eskalasi')->icon('heroicon-o-hand-thumb-down')->color('danger')
                ->visible(fn (): bool => $record->status === 'ESCALATED' && $record->assigned_to === $user->id && $user->hasRole('Kepala Cabang'))
                ->form([ Textarea::make('workflow_notes_kacab_reject')->label('Alasan Penolakan (Wajib)')->required()->minLength(10) ])
                ->action(function (array $data) {
                    $this->record->setWorkflowNotes($data['workflow_notes_kacab_reject']);
                    $this->record->status = 'REJECTED';
                    $this->record->assigned_to = null;
                    $this->record->save();
                    FilamentNotification::make()->title('Eskalasi Ditolak')->danger()->send();
                }),
        ];
        
    }
}