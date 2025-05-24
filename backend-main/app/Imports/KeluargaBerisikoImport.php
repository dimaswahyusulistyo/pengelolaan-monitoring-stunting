<?php

namespace App\Imports;

use App\Models\KeluargaBerisiko;
use App\Models\DeterminanKB;
use App\Models\Desa;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Validation\Rule;

HeadingRowFormatter::default('none');

class KeluargaBerisikoImport implements ToCollection, WithHeadingRow, WithValidation
{

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['No. Kartu Keluarga']) || empty($row['NIK Kepala Keluarga']) || 
                empty($row['Nama Kepala Keluarga']) || empty($row['NIK Istri']) || 
                empty($row['Nama Istri'])) {
                continue;
            }

            $noKK = $this->cleanNumber($row['No. Kartu Keluarga']);
            $nikKepala = $this->cleanNumber($row['NIK Kepala Keluarga']);
            $nikIstri = $this->cleanNumber($row['NIK Istri']);

            $idDesa = $this->getDesaId(trim($row['Desa']));
            if (!$idDesa) {
                continue;
            }

            try {
                $determinanKB = DeterminanKB::create([
                    'punya_anak_0_23_bulan' => $row['Punya Anak 0-23 Bulan'],
                    'punya_anak_24_59_bulan' => $row['Punya Anak 24-59 Bulan'],
                    'pus' => $row['PUS'],
                    'pus_hamil' => $row['PUS Hamil'],
                    'sumber_air_minum_tidak_layak' => $row['Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak'],
                    'jamban_tidak_layak' => $row['Keluarga Tidak Mempunyai Jamban yang Layak'],
                    'pus_4_terlalu_muda' => $row['PUS 4 Terlalu Muda (Umur Istri < 20 Tahun)'],
                    'pus_4_terlalu_tua' => $row['PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)'],
                    'pus_4_terlalu_dekat' => $row['PUS 4 Terlalu Dekat (< 2 Tahun)'],
                    'pus_4_terlalu_banyak' => $row['PUS 4 Terlalu Banyak (≥ 3 Anak)'],
                    'bukan_peserta_kb_modern' => $row['Bukan Peserta KB Modern'],
                ]);

                KeluargaBerisiko::create([
                    'no_kartu_keluarga' => $noKK,
                    'nik_kepala_keluarga' => $nikKepala,
                    'nama_kepala_keluarga' => trim($row['Nama Kepala Keluarga']),
                    'nik_istri' => $nikIstri,
                    'nama_istri' => trim($row['Nama Istri']),
                    'kecamatan' => trim($row['Kecamatan']),
                    'id_desa' => $idDesa,
                    'id_determinan_kb' => $determinanKB->id_determinan_kb,
                    'jenis_pendampingan_diterima' => $this->parsePendampingan($row['Jenis Pendampingan yang Diterima']),
                ]);

            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.No. Kartu Keluarga' => [
                'required',
                function($attribute, $value, $fail) {
                    $cleanValue = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleanValue) !== 16) {
                        $fail('No. Kartu Keluarga harus 16 digit angka');
                    }
                },
                Rule::unique('keluarga_berisiko', 'no_kartu_keluarga')
            ],
            '*.NIK Kepala Keluarga' => [
                'required',
                function($attribute, $value, $fail) {
                    $cleanValue = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleanValue) !== 16) {
                        $fail('NIK Kepala Keluarga harus 16 digit angka');
                    }
                },
                Rule::unique('keluarga_berisiko', 'nik_kepala_keluarga')
            ],
            '*.Nama Kepala Keluarga' => ['required', 'string'],
            '*.NIK Istri' => [
                'required',
                function($attribute, $value, $fail) {
                    $cleanValue = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleanValue) !== 16) {
                        $fail('NIK Istri harus 16 digit angka');
                    }
                },
                Rule::unique('keluarga_berisiko', 'nik_istri')
            ],
            '*.Nama Istri' => ['required', 'string'],
            '*.Kecamatan' => ['required', 'string'],
            '*.Desa' => ['required', Rule::exists('desa', 'nama_desa')],
            '*.Punya Anak 0-23 Bulan' => ['required', 'in:1,2'],
            '*.Punya Anak 24-59 Bulan' => ['required', 'in:1,2'],
            '*.PUS' => ['required', 'in:1,2'],
            '*.PUS Hamil' => ['required', 'in:1,2'],
            '*.Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak' => ['required', 'in:1,2,3'],
            '*.Keluarga Tidak Mempunyai Jamban yang Layak' => ['required', 'in:1,2,3'],
            '*.PUS 4 Terlalu Muda (Umur Istri < 20 Tahun)' => ['required', 'in:1,2,3'],
            '*.PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)' => ['required', 'in:1,2,3'],
            '*.PUS 4 Terlalu Dekat (< 2 Tahun)' => ['required', 'in:1,2,3'],
            '*.PUS 4 Terlalu Banyak (≥ 3 Anak)' => ['required', 'in:1,2,3'],
            '*.Bukan Peserta KB Modern' => ['required', 'in:1,2,3'],
            '*.Jenis Pendampingan yang Diterima' => ['required', 'string'],
        ];
    }
}