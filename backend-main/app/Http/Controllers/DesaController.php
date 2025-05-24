<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Desa;

class DesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getDesa()
    {
        $desa = Desa::with(['puskesmas.kecamatan'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $desa->map(function ($d) {
                return [
                    'id' => $d->id_desa,
                    'desa' => $d->nama_desa,
                    'puskesmas' => $d->puskesmas->nama_puskesmas ?? null,
                    'kecamatan' => $d->puskesmas->kecamatan->nama_kecamatan ?? null,
                ];
            })
        ]);
    }

    public function getKecamatan()
    {
        $desa = Desa::with('kecamatan')->get();

        return response()->json([
            'status' => 'success',
            'data' => $desa->map(function ($d) {
                return [
                    'id' => $d->id_desa,
                    'desa' => $d->nama_desa,
                    'kecamatan' => $d->kecamatan->nama_kecamatan ?? null,
                ];
            })
        ]);
    }
}
