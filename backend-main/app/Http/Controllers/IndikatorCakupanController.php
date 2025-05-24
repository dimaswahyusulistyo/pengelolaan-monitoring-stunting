<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IndikatorCakupan; 

class IndikatorCakupanController extends Controller
{
    public function index()
    {
        $indikatorCakupan = IndikatorCakupan::select('id_indikator_cakupan', 'nama_indikator')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Indikator Cakupan',
            'data' => $indikatorCakupan
        ]);
    }
}
