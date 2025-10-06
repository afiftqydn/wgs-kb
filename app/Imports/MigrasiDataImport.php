<?php

namespace App\Imports;

use App\Models\MigrasiData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Carbon\Carbon;

class MigrasiDataImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Membersihkan NIK dan mengubahnya menjadi null jika kosong
        $nik = trim($row['nik']);
        $nikValue = !empty($nik) ? $nik : null;

        return new MigrasiData([
            'nama_nasabah'      => $row['nama_nasabah'],
            'nama_ibu_kandung'  => $row['nama_ibu_kandung'],
            'alamat'            => $row['alamat'],
            'jenis_kelamin'     => $row['jenis_kelamin'],
            'tempat_lahir'      => $row['tempat_lahir'],
            'tanggal_lahir'     => $this->transformDate($row['tanggal_lahir']),
            'identitas_nasabah' => $row['identitas_nasabah'],
            'nik'               => $nikValue, // <-- BARIS INI DIPERBARUI
            'agama'             => $row['agama'],
            'desa'              => $row['desa'],
            'kecamatan'         => $row['kecamatan'],
            'kota_kabupaten'    => $row['kota_kabupaten'],
            'provinsi'          => $row['provinsi'],
            'no_hp'             => $row['no_hp'],
            'tanggal_register'  => $this->transformDate($row['tanggal_register']),
            'simpok'            => $this->cleanCurrency($row['simpok']),
            'simwajib'          => $this->cleanCurrency($row['simwajib']),
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    private function transformDate($value, $format = 'd-m-Y')
    {
        if (empty($value)) return null;
        try {
            return Carbon::createFromFormat($format, $value)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function cleanCurrency($value)
    {
        if (empty($value)) return 0;
        return preg_replace('/[^\d]/', '', $value);
    }
}