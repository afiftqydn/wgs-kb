<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{ $this->form }}
            <div class="flex items-end">
                <x-filament::button type="submit">
                    Terapkan Filter
                </x-filament::button>
            </div>
        </div>
    </form>

    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama Produk</th>
                        <th scope="col" class="px-6 py-3 text-center">Total Pengajuan</th>
                        <th scope="col" class="px-6 py-3 text-center">Disetujui</th>
                        <th scope="col" class="px-6 py-3 text-center">Tingkat Persetujuan</th>
                        <th scope="col" class="px-6 py-3 text-right">Total Nilai Disetujui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $data)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $data['product_name'] }}
                            </td>
                            <td class="px-6 py-4 text-center">{{ $data['total_applications'] }}</td>
                            <td class="px-6 py-4 text-center">{{ $data['approved_count'] }}</td>
                            <td class="px-6 py-4 text-center font-semibold">
                                @if ($data['total_applications'] > 0)
                                    {{ number_format(($data['approved_count'] / $data['total_applications']) * 100, 2) }}%
                                @else
                                    0.00%
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                Rp {{ number_format($data['total_approved_value'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
