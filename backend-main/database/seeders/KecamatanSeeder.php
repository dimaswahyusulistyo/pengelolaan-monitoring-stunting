<?php

namespace Database\Seeders;

Use App\Models\Kecamatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        Kecamatan::insert([
            ['nama_kecamatan' => 'JATIPURO'],
            ['nama_kecamatan' => 'JATIYOSO'],
            ['nama_kecamatan' => 'JUMAPOLO'],
            ['nama_kecamatan' => 'JUMANTONO'],
            ['nama_kecamatan' => 'MATESIH'],
            ['nama_kecamatan' => 'TAWANGMANGU'],
            ['nama_kecamatan' => 'NGARGOYOSO'],
            ['nama_kecamatan' => 'KARANGPANDAN'],
            ['nama_kecamatan' => 'KARANGANYAR'],
            ['nama_kecamatan' => 'TASIKMADU'],
            ['nama_kecamatan' => 'JATEN'],
            ['nama_kecamatan' => 'COLOMADU'],
            ['nama_kecamatan' => 'GONDANGREJO'],
            ['nama_kecamatan' => 'KEBAKKRAMAT'],
            ['nama_kecamatan' => 'MOJOGEDANG'],
            ['nama_kecamatan' => 'KERJO'],
            ['nama_kecamatan' => 'JENAWI'],
        ]);
    }
}
