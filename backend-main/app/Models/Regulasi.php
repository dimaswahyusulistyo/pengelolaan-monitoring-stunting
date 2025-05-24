<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regulasi extends Model
{
    use HasFactory;

    protected $table = 'regulasi';
    protected $primaryKey = 'id_regulasi';

    protected $fillable = [
        'nama_surat_regulasi',
        'file_surat_regulasi',
    ];
}
