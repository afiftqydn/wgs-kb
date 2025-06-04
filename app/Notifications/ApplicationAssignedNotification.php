<?php

namespace App\Notifications;

use App\Models\LoanApplication;
use App\Models\User;
// Hapus: use Illuminate\Bus\Queueable; 
// Hapus: use Illuminate\Contracts\Queue\ShouldQueue; // Jika ada
use Illuminate\Notifications\Notification;
use App\Filament\Resources\LoanApplicationResource;

class ApplicationAssignedNotification extends Notification // Hapus "implements ShouldQueue"
{
    // Hapus: use Queueable;

    public LoanApplication $loanApplication;
    public User $assignedByUser;

    public function __construct(LoanApplication $loanApplication, ?User $assignedByUser = null)
    {
        $this->loanApplication = $loanApplication;
        $this->assignedByUser = $assignedByUser ?? Auth::user();
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Fokus ke database
    }

    // public function toArray(object $notifiable): array
    // {
    //     $assignedByName = $this->assignedByUser ? $this->assignedByUser->name : 'Sistem';
    //     return [
    //         'loan_application_id' => $this->loanApplication->id,
    //         'application_number' => $this->loanApplication->application_number,
    //         'customer_name' => $this->loanApplication->customer->name ?? 'N/A',
    //         'message' => 'Tugas baru: Permohonan ' . $this->loanApplication->application_number . ' a/n ' . ($this->loanApplication->customer->name ?? 'N/A') . ' dari ' . $assignedByName . '.',
    //         'url' => LoanApplicationResource::getUrl('view', ['record' => $this->loanApplication->id]),
    //         'icon' => 'heroicon-o-document-text',
    //     ];
    // }
    public function toArray(object $notifiable): array
{
    return [
        'message' => 'Tes Notifikasi untuk ' . $this->loanApplication->application_number,
        'url' => LoanApplicationResource::getUrl('view', ['record' => $this->loanApplication->id]),
    ];
}

}