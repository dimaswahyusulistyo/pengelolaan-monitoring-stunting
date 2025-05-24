<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Table Roles
        Schema::create('roles', function(Blueprint $table) {
            $table->id('id_role');
            $table->string('nama_role');
            $table->timestamps();
        });

        //Table Kecamatan
        Schema::create('kecamatan', function(Blueprint $table) {
            $table->id('id_kecamatan');
            $table->string('nama_kecamatan');
            $table->timestamps();
        });

        //Table Puskesmas
        Schema::create('puskesmas', function(Blueprint $table) {
            $table->id('id_puskesmas');
            $table->foreignId('id_kecamatan')->references('id_kecamatan')->on('kecamatan')->onDelete('cascade');
            $table->string('nama_puskesmas');
            $table->timestamps();
        });

        //Table Desa
        Schema::create('desa', function(Blueprint $table) {
            $table->id('id_desa');
            $table->foreignId('id_puskesmas')->references('id_puskesmas')->on('puskesmas')->onDelete('cascade');
            $table->string('nama_desa');
            $table->timestamps();
        });

        //Table Users
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->foreignId('id_role')->references('id_role')->on('roles')->onDelete('cascade');
            $table->foreignId('id_desa')->nullable()->references('id_desa')->on('desa')->onDelete('set null');
            $table->string('username');
            $table->string('password');
            $table->string('foto_profil')->nullable();
            $table->boolean('is_online')->default(false); 
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        //Table Determinan
        Schema::create('determinan', function (Blueprint $table) {
            $table->id('id_determinan');
            $table->enum('jkn', [1, 2, 3, 4])->default(2);
            $table->enum('status_ekonomi', [1, 2])->default(2);
            $table->enum('imunisasi', [1, 2])->default(2);
            $table->enum('bpnt', [1, 2])->default(2);
            $table->enum('pkh', [1, 2])->default(2);
            $table->enum('jamban_sehat', [1, 2])->default(2);
            $table->enum('kebiasaan_merokok', [1, 2])->default(2);
            $table->string('penyakit_penyerta')->nullable();
            $table->enum('riwayat_ibu_hamil', [1, 2])->default(2);
            $table->enum('sumber_air_bersih', [1, 2])->default(2);
            $table->enum('kecacingan_menderita', [1, 2])->default(2);
            $table->enum('kecacingan_obat', [1, 2])->default(2);
            $table->timestamps();
        });

        //Table Anak Stunting
        Schema::create('anak_stunting', function (Blueprint $table) {
            $table->id('id_anak');
            $table->string('nik_anak', 16)->unique();
            $table->string('nama_anak');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_lahir');
            $table->foreignId('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->string('posyandu');
            $table->foreignId('id_determinan')->references('id_determinan')->on('determinan')->onDelete('cascade');
            $table->timestamps();
        });

        //Table Status Penanganan
        Schema::create('status_penanganan', function (Blueprint $table) {
            $table->id('id_status_penanganan');
            $table->foreignId('id_anak')->references('id_anak')->on('anak_stunting')->onDelete('cascade');
            $table->foreignId('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->enum('status', ['Belum ada penanganan', 'Sudah ada penanganan'])->default('Belum ada penanganan');
            $table->text('keterangan')->nullable();
            $table->timestamp('tanggal_status')->nullable();
            $table->timestamps();
        });
        
        //Table Determinan Keluarga Berisiko
        Schema::create('determinan_kb', function (Blueprint $table) {
            $table->id('id_determinan_kb');
            $table->enum('punya_anak_0_23_bulan', [1, 2])->default(2);
            $table->enum('punya_anak_24_59_bulan', [1, 2])->default(2);
            $table->enum('pus', [1, 2])->default(2);
            $table->enum('pus_hamil', [1, 2])->default(2);
            $table->enum('sumber_air_minum_tidak_layak', [1, 2, 3])->default(2);
            $table->enum('jamban_tidak_layak', [1, 2, 3])->default(2);
            $table->enum('pus_4_terlalu_muda', [1, 2, 3])->default(2);
            $table->enum('pus_4_terlalu_tua', [1, 2, 3])->default(2);
            $table->enum('pus_4_terlalu_dekat', [1, 2, 3])->default(2);
            $table->enum('pus_4_terlalu_banyak', [1, 2, 3])->default(2);
            $table->enum('pus_4_terlalu', [1, 2, 3])->default(2);
            $table->enum('bukan_peserta_kb_modern', [1, 2, 3])->default(2);
            $table->timestamps();
        });

        //Table Keluarga Berisiko
        Schema::create('keluarga_berisiko', function (Blueprint $table) {
            $table->id('id_keluarga');
            $table->string('no_kartu_keluarga', 16)->unique();
            $table->string('nik_kepala_keluarga', 16)->unique();
            $table->string('nama_kepala_keluarga');
            $table->string('nik_istri', 16)->unique();
            $table->string('nama_istri');
            $table->foreignId('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreignId('id_determinan_kb')->references('id_determinan_kb')->on('determinan_kb')->onDelete('cascade');
            $table->json('jenis_pendampingan_diterima');
            $table->timestamps();
        });
        
        //Table Report TPPS
        Schema::create('report_tpps', function(Blueprint $table) {
            $table->id('id_report_tpps');
            $table->string('nama_kegiatan');
            $table->unsignedBigInteger('jumlah_anggaran');
            $table->json('kerangka_acuan')->nullable();
            $table->json('dokumentasi_kegiatan_tpps')->nullable();
            $table->timestamps();
        });

        //Table RAD
        Schema::create('rad', function (Blueprint $table) {
            $table->id('id_rad');
            $table->text('nama_aksi');
            $table->text('deskripsi_aksi');
            $table->date('bulan_aksi');
            $table->timestamps();
        });

        //Table Template
        Schema::create('template', function(Blueprint $table) {
            $table->id('id_template');
            $table->string('nama_template');
            $table->string('file_template')->nullable();
            $table->timestamps();
        });

        //Table Regulasi
        Schema::create('regulasi', function(Blueprint $table) {
            $table->id('id_regulasi');
            $table->string('nama_surat_regulasi');
            $table->string('file_surat_regulasi')->nullable();
            $table->timestamps();
        });

        //Table Indikator Cakupan
        Schema::create('indikator_cakupan', function(Blueprint $table) {
            $table->id('id_indikator_cakupan');
            $table->string('nama_indikator');
            $table->string('kategori');
            $table->timestamps();
        });

        //Table Cakupan Program Intervensi
        Schema::create('cakupan_program_intervensi', function(Blueprint $table) {
            $table->id('id_cakupan_program_intervensi');
            $table->foreignId('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreignId('id_indikator_cakupan')->references('id_indikator_cakupan')->on('indikator_cakupan')->onDelete('cascade');
            $table->foreignId('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->string('nilai');
            $table->string('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cakupan_program_intervensi');
        Schema::dropIfExists('status_penanganan');
        Schema::dropIfExists('anak_stunting');
        Schema::dropIfExists('determinan');
        Schema::dropIfExists('users');
        Schema::dropIfExists('keluarga_berisiko');
        Schema::dropIfExists('determinan_kb');
        Schema::dropIfExists('desa');
        Schema::dropIfExists('puskesmas');
        Schema::dropIfExists('kecamatan');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('report_tpps');
        Schema::dropIfExists('rad');
        Schema::dropIfExists('template');
        Schema::dropIfExists('regulasi');
        Schema::dropIfExists('indikator_cakupan');
    }
};
