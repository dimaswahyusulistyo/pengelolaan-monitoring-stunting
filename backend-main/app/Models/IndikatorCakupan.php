<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorCakupan extends Model
{
    use HasFactory;

    protected $table = 'indikator_cakupan';
    protected $primaryKey = 'id_indikator_cakupan';

    protected $fillable = [
        'nama_indikator',
        'kategori',
    ];

    public function cakupanProgramIntervensi()
    {
        return $this->hasMany(CakupanProgramIntervensi::class, 'id_indikator_cakupan');
    }
}
