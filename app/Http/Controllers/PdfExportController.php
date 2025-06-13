<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfExportController extends Controller
{
    public function generateCompletePackage(LoanApplication $loanApplication)
    {
        // Validasi status: hanya yang sudah disetujui/ditolak
        if (!in_array($loanApplication->status, ['APPROVED', 'REJECTED'])) {
            return redirect()->back()->with('error', 'Dokumen lengkap hanya bisa dibuat untuk permohonan yang sudah disetujui atau ditolak.');
        }

        // Load semua relasi yang dibutuhkan
        $loanApplication->load([
            'customer.region',
            'productType',
            'documents',
            'inputRegion',
            'processingRegion',
            'workflows.processor',
        ]);

        // Ambil catatan keputusan (workflow terakhir yang sesuai status)
        $decisionWorkflow = $loanApplication->workflows()
            ->where('to_status', $loanApplication->status)
            ->latest()
            ->first();

        $decisionNotes = $decisionWorkflow ? $decisionWorkflow->notes : 'Tidak ada catatan.';
        $decisionMakerName = $decisionWorkflow?->processor->name ?? 'Pejabat Berwenang';
        $decisionMakerRole = $decisionWorkflow?->processor->roles->pluck('name')->first() ?? 'PT WGS';

        // Filter dokumen gambar dan PDF
        $imageDocuments = $loanApplication->documents->filter(fn ($doc) =>
            str_starts_with($doc->mime_type, 'image/')
        );

        $pdfDocuments = $loanApplication->documents->filter(fn ($doc) =>
            $doc->mime_type === 'application/pdf'
        );

        // Enkode dokumen gambar menjadi base64
        foreach ($imageDocuments as $doc) {
            $path = storage_path('app/public/' . $doc->file_path);

            if (file_exists($path)) {
                $doc->base64image = 'data:' . $doc->mime_type . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $doc->base64image = null;
            }
        }

        // Siapkan data untuk dikirim ke Blade
        $data = [
            'loanApplication' => $loanApplication,
            'decisionNotes' => $decisionNotes,
            'decisionMakerName' => $decisionMakerName,
            'decisionMakerRole' => $decisionMakerRole,
            'imageDocuments' => $imageDocuments,
            'pdfDocuments' => $pdfDocuments,
        ];

        // Nama file output PDF
        $fileName = 'Dokumen_Lengkap_' . str_replace('/', '_', $loanApplication->application_number) . '.pdf';

        // Generate dan stream PDF
        $pdf = Pdf::loadView('pdf.loan_application_package', $data)->setPaper('a4', 'portrait');
        return $pdf->stream($fileName);
    }
}
