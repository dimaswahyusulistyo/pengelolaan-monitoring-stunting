<?php

namespace Database\Seeders;

Use App\Models\IndikatorCakupan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IndikatorCakupanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Kategori: POPULASI
            ['nama_indikator' => 'Total Populasi Keluarga', 'kategori' => 'POPULASI'],
            ['nama_indikator' => 'Total Populasi Anak', 'kategori' => 'POPULASI'],

            // Kategori: REMAJA
            ['nama_indikator' => 'Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)', 'kategori' => 'REMAJA'],
            ['nama_indikator' => 'Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)', 'kategori' => 'REMAJA'],

            // Kategori: CALON PENGANTIN/PASANGAN USIA SUBUR
            ['nama_indikator' => 'Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],
            ['nama_indikator' => 'Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan', 'kategori' => 'CALON PENGANTIN/PASANGAN USIA SUBUR'],

            // Kategori: IBU HAMIL
            ['nama_indikator' => 'Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi', 'kategori' => 'IBU HAMIL'],
            ['nama_indikator' => 'Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan', 'kategori' => 'IBU HAMIL'],
            ['nama_indikator' => 'Persentase Unmeet Need pelayanan keluarga berencana', 'kategori' => 'IBU HAMIL'],
            ['nama_indikator' => 'Persentase Kehamilan yang tidak diinginkan', 'kategori' => 'IBU HAMIL'],

            // Kategori: ANAK DI BAWAH USIA LIMA TAHUN (BALITA)
            ['nama_indikator' => 'Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif', 'kategori' => 'BALITA'],
            ['nama_indikator' => 'Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)', 'kategori' => 'BALITA'],
            ['nama_indikator' => 'Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk', 'kategori' => 'BALITA'],
            ['nama_indikator' => 'Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya', 'kategori' => 'BALITA'],
            ['nama_indikator' => 'Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi', 'kategori' => 'BALITA'],
            ['nama_indikator' => 'Balita yang memperoleh imunisasi dasar lengkap', 'kategori' => 'BALITA'],

            // Kategori: KELUARGA BERISIKO
            ['nama_indikator' => 'Keluarga yang Stop BABS', 'kategori' => 'KELUARGA BERISIKO'],
            ['nama_indikator' => 'Keluarga yang melaksanakan PHBS', 'kategori' => 'KELUARGA BERISIKO'],
            ['nama_indikator' => 'Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri', 'kategori' => 'KELUARGA BERISIKO'],
            ['nama_indikator' => 'Pelayanan Keluarga Berencana (KB) pascapersalinan', 'kategori' => 'KELUARGA BERISIKO'],
            ['nama_indikator' => 'Keluarga berisiko stunting yang memperoleh pendampingan', 'kategori' => 'KELUARGA BERISIKO'],
            ['nama_indikator' => 'Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi', 'kategori' => 'KELUARGA BERISIKO'],

            // Kategori: AIR MINUM DAN SANITASI
            ['nama_indikator' => 'Rumah tangga yang mendapatkan akses air minum layak', 'kategori' => 'AIR MINUM DAN SANITASI'],
            ['nama_indikator' => 'Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak', 'kategori' => 'AIR MINUM DAN SANITASI'],

            // Kategori: PERLINDUNGAN SOSIAL
            ['nama_indikator' => 'Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi', 'kategori' => 'PERLINDUNGAN SOSIAL'],
            ['nama_indikator' => 'Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur', 'kategori' => 'PERLINDUNGAN SOSIAL'],

            // Kategori: ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA
            ['nama_indikator' => 'DD', 'kategori' => 'ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA'],
            ['nama_indikator' => 'APBDES', 'kategori' => 'ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA'],
            ['nama_indikator' => 'BUMDES', 'kategori' => 'ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA'],
            ['nama_indikator' => 'CSR', 'kategori' => 'ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA'],
        ];

        // Insert ke database
        IndikatorCakupan::insert($data);
    }
}
