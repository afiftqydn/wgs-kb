<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\LoanApplication;
use Illuminate\Support\Collection; // Untuk tipe data hasil


class ReportGeneratorPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static string $view = 'filament.pages.report-generator-page';
    protected static ?string $navigationGroup = 'Manajemen Nasabah';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Generator Laporan';
    protected static ?int $navigationSort = 3;
   

    // Anda bisa menambahkan properti untuk filter di sini
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $status = null;
    public ?int $productTypeId = null;
    public ?int $regionId = null;
    public ?Collection $reportData = null; // Untuk menyimpan hasil query
    public bool $showReport = false;
    // ... filter lainnya
    // Method untuk menangani submit form filter dan generate laporan (akan kita kembangkan)
    public function generateReport()
    {
        $this->validate([
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            // Tambahkan validasi untuk filter lain jika perlu
        ]);

        $query = LoanApplication::query()->with(['customer', 'productType', 'inputRegion', 'assignee']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->productTypeId) {
            $query->where('product_type_id', $this->productTypeId);
        }
        // Tambahkan filter untuk regionId jika ada

        $this->reportData = $query->get();
        $this->showReport = true; // Tampilkan bagian hasil laporan

        // Untuk tahap ini, kita hanya menampilkan data mentah.
        // Nantinya, Anda bisa memformat ini ke tabel HTML, atau memicu download CSV/Excel/PDF.
        // Contoh: jika ingin download CSV, Anda bisa buat method terpisah yang dipanggil dari sini.
    }
}
