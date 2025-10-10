<?php

namespace App\Exports;

use App\Models\MigrasiData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class MigrasiDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return MigrasiData::all();
    }

    /**
     * Map data untuk setiap row
     */
    public function map($data): array
    {
        return [
            $data->nama_nasabah,
            $data->nama_ibu_kandung,
            $data->alamat,
            $data->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
            $data->tempat_lahir,
            $data->tanggal_lahir ? Carbon::parse($data->tanggal_lahir)->format('d-m-Y') : '',
            $data->identitas_nasabah,
            $data->nik,
            $data->agama,
            $data->desa,
            $data->kecamatan,
            $data->kota_kabupaten,
            $data->provinsi,
            $data->no_hp,
            $data->tanggal_register ? Carbon::parse($data->tanggal_register)->format('d-m-Y') : '',
            $data->simpok,
            $data->simwajib,
        ];
    }

    /**
     * Headers untuk Excel
     */
    public function headings(): array
    {
        return [
            'NAMA_NASABAH',
            'NAMA_IBU_KANDUNG', 
            'ALAMAT',
            'JENIS_KELAMIN',
            'TEMPAT_LAHIR',
            'TANGGAL_LAHIR',
            'IDENTITAS_NASABAH',
            'NIK',
            'AGAMA',
            'DESA',
            'KECAMATAN',
            'KOTA_KABUPATEN',
            'PROVINSI',
            'NO_HP',
            'TANGGAL_REGISTER',
            'SIMPOK',
            'SIMWAJIB',
        ];
    }

    /**
     * Format kolom
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_TEXT, // NIK sebagai text
            'P' => '#,##0', // SIMPOK format number
            'Q' => '#,##0', // SIMWAJIB format number
        ];
    }

    /**
     * Styling untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2D5F87'],
            ],
        ]);

        // Auto size columns
        foreach(range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add border to all cells
        $sheet->getStyle('A1:Q' . ($sheet->getHighestRow()))
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}