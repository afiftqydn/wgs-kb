<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use Barryvdh\DomPDF\Facade\Pdf; // Import Facade PDF
use Illuminate\Http\Request;

class PdfExportController extends Controller
{
    public function generateLoanDecisionLetter(LoanApplication $loanApplication)
    {
        // Pastikan hanya permohonan yang sudah diputuskan yang bisa dicetak suratnya
        if (!in_array($loanApplication->status, ['APPROVED', 'REJECTED'])) {
            // abort(403, 'Surat keputusan hanya bisa dicetak untuk permohonan yang sudah disetujui atau ditolak.');
            // Atau redirect dengan pesan error
            return redirect()->back()->with('error', 'Surat keputusan hanya bisa dicetak untuk status APPROVED atau REJECTED.');
        }

        // Eager load relasi yang dibutuhkan di Blade view
        $loanApplication->load(['customer', 'productType', 'inputRegion', 'processingRegion', 'workflows.processor']);

        // Ambil catatan terakhir yang relevan dengan status approval/rejection
        $workflowNotes = [];
        $decisionMakerName = 'Pejabat Berwenang';
        $decisionMakerRole = 'PT WGS';

        $decisionWorkflow = $loanApplication->workflows()
                            ->where('to_status', $loanApplication->status) // Cari log yang menghasilkan status saat ini
                            ->orderBy('created_at', 'desc')
                            ->first();

        if ($decisionWorkflow) {
            $workflowNotes[$loanApplication->status] = $decisionWorkflow->notes;
            if ($decisionWorkflow->processor) {
                $decisionMakerName = $decisionWorkflow->processor->name;
                // Ambil nama peran pertama dari user tersebut sebagai contoh
                $decisionMakerRole = $decisionWorkflow->processor->roles->first()->name ?? 'PT WGS'; 
            }
        }


        $data = [
            'loanApplication' => $loanApplication,
            'workflowNotes' => $workflowNotes,
            'decisionMakerName' => $decisionMakerName,
            'decisionMakerRole' => $decisionMakerRole,
        ];

        // Buat nama file PDF
        $fileName = 'Surat_Keputusan_' . str_replace('/', '_', $loanApplication->application_number) . '.pdf';

        // Generate PDF
        $pdf = Pdf::loadView('pdf.loan_decision_letter', $data);

        // Bisa langsung di-download atau ditampilkan di browser
        // return $pdf->download($fileName); // Untuk langsung download
        return $pdf->stream($fileName); // Untuk ditampilkan di browser lalu bisa di-save/print
    }
}