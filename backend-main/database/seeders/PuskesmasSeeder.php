<?php

namespace Database\Seeders;

use App\Models\Puskesmas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PuskesmasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Puskesmas::create([
            'nama_puskesmas' => 'JATIPURO',
            'id_kecamatan' => 1,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JATIYOSO',
            'id_kecamatan' => 2,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JUMAPOLO',
            'id_kecamatan' => 3,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JUMANTONO',
            'id_kecamatan' => 4,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'MATESIH',
            'id_kecamatan' => 5,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'TAWANGMANGU',
            'id_kecamatan' => 6,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'NGARGOYOSO',
            'id_kecamatan' => 7,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'KARANGPANDAN',
            'id_kecamatan' => 8,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'KARANGANYAR',
            'id_kecamatan' => 9,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'TASIKMADU',
            'id_kecamatan' => 10,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JATEN I',
            'id_kecamatan' => 11,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JATEN II',
            'id_kecamatan' => 11,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'COLOMADU I',
            'id_kecamatan' => 12,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'COLOMADU II',
            'id_kecamatan' => 12,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'GONDANGREJO',
            'id_kecamatan' => 13,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'KEBAKKRAMAT I',
            'id_kecamatan' => 14,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'KEBAKKRAMAT II',
            'id_kecamatan' => 14,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'MOJOGEDANG I',
            'id_kecamatan' => 15,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'MOJOGEDANG II',
            'id_kecamatan' => 15,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'KERJO',
            'id_kecamatan' => 16,
        ]);

        Puskesmas::create([
            'nama_puskesmas' => 'JENAWI',
            'id_kecamatan' => 17,
        ]);
    }
}
