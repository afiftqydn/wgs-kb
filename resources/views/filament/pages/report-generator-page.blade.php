<x-filament-panels::page>
    <form wire:submit.prevent="generateReport">
        {{-- Untuk menggunakan komponen Form Filament di sini, Anda perlu setup Form object di kelas Page --}}
        {{-- Atau buat form HTML manual dengan binding ke properti Livewire di atas --}}

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {{-- Filter Tanggal Awal --}}
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal
                    Awal</label>
                <input wire:model.defer="startDate" type="date" id="startDate"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
            </div>

            {{-- Filter Tanggal Akhir --}}
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal
                    Akhir</label>
                <input wire:model.defer="endDate" type="date" id="endDate"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
            </div>

            {{-- Filter Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status
                    Permohonan</label>
                <select wire:model.defer="status" id="status"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="SUBMITTED">Submitted</option>
                    <option value="UNDER_REVIEW">Under Review</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                    <option value="ESCALATED">Escalated</option>
                </select>
            </div>

            {{-- Tambahkan filter lain di sini: Jenis Produk, Wilayah, dll. --}}
            {{-- Contoh: Filter Jenis Produk (membutuhkan data ProductType) --}}
            {{-- <div>
                <label for="productTypeId" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis Produk</label>
                <select wire:model.defer="productTypeId" id="productTypeId" class="block w-full mt-1 ...">
                    <option value="">Semua Produk</option>
                    @foreach (App\Models\ProductType::all() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div> --}}
        </div>

        <div class="mt-6">
            <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Generate Laporan
            </button>
        </div>
    </form>

    <div class="mt-8">
        {{-- Tempat untuk menampilkan hasil laporan atau link download --}}
        @if ($showReport)
            <div class="mt-8 overflow-x-auto">
                @if ($reportData && $reportData->count() > 0)
                    <h3 class="mb-4 text-lg font-semibold">Hasil Laporan</h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    No. App</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Nasabah</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Produk</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Jumlah</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Tgl Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @foreach ($reportData as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->application_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->customer->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->productType->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rp
                                        {{ number_format($item->amount_requested, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if ($item->status == 'APPROVED') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                                    @elseif($item->status == 'REJECTED') bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100
                                    @elseif(in_array($item->status, ['SUBMITTED', 'UNDER_REVIEW', 'ESCALATED'])) bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100 @endif">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Tidak ada data yang cocok dengan filter yang Anda pilih.</p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
