<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTemplate extends Model
{
    use HasFactory;

    protected $table = 'template';
    protected $primaryKey = 'id_template';

    protected $fillable = [
        'nama_template',
        'file_template',
    ];
}
