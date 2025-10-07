<?php

namespace App\Imports;

use App\Models\MigrasiData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log; // <-- TAMBAH INI

class MigrasiDataImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Debug: Lihat data yang diimpor
        Log::info('Importing row:', $row); // <-- Sekali pakai Log

        // Membersihkan NIK dan mengubahnya menjadi null jika kosong
        $nik = trim($row['nik'] ?? '');
        $nikValue = !empty($nik) ? $nik : null;

        return new MigrasiData([
            'nama_nasabah'      => $row['nama_nasabah'] ?? null,
            'nama_ibu_kandung'  => $row['nama_ibu_kandung'] ?? null,
            'alamat'            => $row['alamat'] ?? null,
            'jenis_kelamin'     => $row['jenis_kelamin'] ?? null,
            'tempat_lahir'      => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'     => $this->transformDate($row['tanggal_lahir'] ?? null),
            'identitas_nasabah' => $row['identitas_nasabah'] ?? 'KTP',
            'nik'               => $nikValue,
            'agama'             => $row['agama'] ?? null,
            'desa'              => $row['desa'] ?? null,
            'kecamatan'         => $row['kecamatan'] ?? null,
            'kota_kabupaten'    => $row['kota_kabupaten'] ?? null,
            'provinsi'          => $row['provinsi'] ?? null,
            'no_hp'             => $row['no_hp'] ?? null,
            'tanggal_register'  => $this->transformDate($row['tanggal_register'] ?? null),
            'simpok'            => $this->cleanCurrency($row['simpok'] ?? 0),
            'simwajib'          => $this->cleanCurrency($row['simwajib'] ?? 0),
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Jika value adalah numeric (Excel date serial number)
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            }

            // Untuk string, bersihkan dulu
            $dateString = trim($value);
            
            // Coba format dd-mm-yyyy (format dari sampel data Anda)
            if (preg_match('/^(\d{1,2})[-\.\/](\d{1,2})[-\.\/](\d{4})$/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                // Validasi tanggal yang masuk akal (tahun antara 1900-2100)
                if ($year >= 1900 && $year <= 2100) {
                    // Pastikan format yang benar untuk Carbon
                    return Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                }
            }
            
            // Jika format tidak sesuai, return null
            Log::warning('Invalid date format: ' . $dateString); // <-- Sekali pakai Log
            return null;
            
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error parsing date: ' . $value . ' - ' . $e->getMessage()); // <-- Sekali pakai Log
            return null;
        }
    }
    
    private function cleanCurrency($value)
    {
        if (empty($value)) return 0;
        
        // Jika value sudah numeric, langsung return
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        $valueString = trim($value);
        
        // Hapus spasi dan karakter non-digit kecuali koma dan titik
        $cleaned = preg_replace('/[^\d,]/', '', $valueString);
        
        // Handle format: "100,000" -> 100000
        if (strpos($cleaned, ',') !== false) {
            $cleaned = str_replace(',', '', $cleaned);
        }
        
        return floatval($cleaned);
    }

    public function headingRow(): int
    {
        return 1; // Baris pertama adalah header
    }
}