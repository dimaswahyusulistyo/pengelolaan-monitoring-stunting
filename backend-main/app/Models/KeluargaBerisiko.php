<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeluargaBerisiko extends Model
{
    use HasFactory;

    protected $table = 'keluarga_berisiko';
    protected $primaryKey = 'id_keluarga';
    protected $casts = [
        'jenis_pendampingan_diterima' => 'array'
    ];

    protected $fillable = [
        'no_kartu_keluarga',
        'nik_kepala_keluarga',
        'nama_kepala_keluarga',
        'nik_istri',
        'nama_istri',
        'id_desa',
        'id_determinan_kb',
        'jenis_pendampingan_diterima'
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function determinanKB()
    {
        return $this->belongsTo(DeterminanKB::class, 'id_determinan_kb', 'id_determinan_kb');
    }
}
