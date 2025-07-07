<x-filament-panels::page>
    {{-- Hapus <form> dan tombol submit. Filter sekarang bekerja otomatis. --}}
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{ $this->form }}
        </div>
    </div>

    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        {{-- Judul kolom akan berubah sesuai jenis laporan --}}
                        <th scope="col" class="px-6 py-3">
                            @if ($this->data['report_type'] === 'Unit')
                                Nama Unit
                            @else
                                Nama Referral
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Jumlah Pengajuan Cair</th>
                        <th scope="col" class="px-6 py-3 text-right">
                            @if ($this->data['report_type'] === 'Unit')
                                Total Komisi
                            @else
                                Total Fee
                            @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $data)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $data['recipient_name'] }}
                            </td>
                            <td class="px-6 py-4 text-center">{{ $data['total_applications'] }}</td>
                            <td class="px-6 py-4 text-right font-semibold">
                                Rp {{ number_format($data['total_commission'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data untuk periode dan jenis laporan yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
