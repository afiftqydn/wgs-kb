@php
    use App\Models\ProductType;

    // Default values
    $monthlyInstallment = 0;
    $totalLoan = $loanAmount;
    $totalInterest = 0;
    $totalPayment = 0;
    $productName = 'N/A';
    $rules = [];

    if ($productTypeId && $loanAmount > 0 && $tenor > 0) {
        $productType = ProductType::with('productTypeRules')->find($productTypeId);
        if ($productType) {
            $productName = $productType->name;
            $rules = $productType->productTypeRules;

            // --- LOGIKA KALKULATOR BARU (BUNGA FLAT) ---

            // 1. Ambil bunga langsung dari kolomnya
            $bungaTahunan = $productType->interest_rate;

            $biayaLain = 0;
            // 2. Hitung total biaya lain dari aturan yang ada
            foreach ($rules as $rule) {
                if ($rule->type === 'percentage') {
                    $biayaLain += ($rule->value / 100) * $loanAmount;
                } else {
                    $biayaLain += $rule->value;
                }
            }

            // Perhitungan Bunga Flat
            $bungaPerBulan = $bungaTahunan / 12 / 100;
            $totalInterest = $loanAmount * $bungaPerBulan * $tenor;
            $totalPayment = $loanAmount + $totalInterest + $biayaLain;
            $monthlyInstallment = $totalPayment / $tenor;
        }
    }
@endphp

<div>
    <h2 class="text-xl font-bold mb-4">Hasil Simulasi Pembayaran</h2>
    <p class="text-sm text-gray-500 mb-4">Perhitungan menggunakan metode Bunga Flat.</p>

    <div class="space-y-2">
        <div class="flex justify-between">
            <span class="font-semibold">Produk:</span>
            <span>{{ $productName }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">Jumlah Pinjaman:</span>
            <span>Rp {{ number_format($loanAmount, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">Jangka Waktu (Tenor):</span>
            <span>{{ $tenor }} bulan</span>
        </div>
        <hr class="my-2">
        <div class="flex justify-between text-lg font-bold text-primary-600">
            <span>Angsuran per Bulan:</span>
            <span>Rp {{ number_format($monthlyInstallment, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
