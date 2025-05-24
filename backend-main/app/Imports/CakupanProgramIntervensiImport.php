<?php

namespace App\Imports;

use App\Models\CakupanProgramIntervensi;
use App\Models\Desa;
use App\Models\IndikatorCakupan;
use App\Models\Puskesmas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CakupanProgramIntervensiImport implements ToModel, WithHeadingRow
{
    protected $indikatorList = [];
    protected $tahun;
    protected $errors;

    public function getErrors()
    {
        return $this->errors;
    }

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
        $this->indikatorList = IndikatorCakupan::pluck('nama_indikator', 'id_indikator_cakupan')->toArray();
    }

    public function model(array $row)
    {
        
        $namaDesa = strtolower(trim(preg_replace('/\s+/', ' ', $row['desa'] ?? '')));
        $namaPuskesmas = strtolower(trim(preg_replace('/\s+/', ' ', $row['puskesmas'] ?? '')));

        if (empty($namaDesa) || empty($namaPuskesmas)) {
            Log::warning("Baris kosong dilewati: Desa: {$namaDesa}, Puskesmas: {$namaPuskesmas}");
            return null;
        }

        $puskesmas = Puskesmas::whereRaw('LOWER(TRIM(REPLACE(nama_puskesmas, "  ", " "))) = ?', [$namaPuskesmas])->first();
        if (!$puskesmas) {
            Log::warning("Puskesmas tidak ditemukan: {$namaPuskesmas}");
            return null;
        }

        $desa = Desa::whereRaw('LOWER(TRIM(REPLACE(nama_desa, "  ", " "))) = ?', [$namaDesa])
            ->where('id_puskesmas', $puskesmas->id_puskesmas)
            ->first();

        if (!$desa) {
            Log::warning("Desa tidak ditemukan: Desa: {$namaDesa}, Puskesmas: {$namaPuskesmas}");
            return null;
        }

        foreach ($row as $key => $value) {
            $cleanKey = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $key)));

            if (empty($cleanKey) || in_array($cleanKey, ['desa', 'puskesmas', 'no', 'id', 'tahun'])) {
                continue;
            }

            $indikator = IndikatorCakupan::whereRaw('LOWER(TRIM(nama_indikator)) = ?', [$cleanKey])->first();

            if (!$indikator) {
                $indikator = $this->findClosestMatch($cleanKey);
            }

            if (!$indikator) {
                continue;
            }

            if ($value === null || $value === '') {
                $this->errors[] = "Desa: {$namaDesa} — indikator '{$cleanKey}' kosong.";
                continue;
            }

            CakupanProgramIntervensi::updateOrCreate(
                [
                    'id_desa' => $desa->id_desa,
                    'id_indikator_cakupan' => $indikator->id_indikator_cakupan,
                    'tahun' => $this->tahun,
                ],
                [
                    'id_user' => Auth::id(),
                    'nilai' => $value,
                ]
            );
        }
    }

    private function findClosestMatch($key)
    {
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($this->indikatorList as $id => $nama) {
            similar_text(strtolower($key), strtolower($nama), $percent);
            if ($percent > $highestSimilarity && $percent > 80) {
                $highestSimilarity = $percent;
                $bestMatch = $id;
            }
        }

        return $bestMatch ? IndikatorCakupan::find($bestMatch) : null;
    }
}
