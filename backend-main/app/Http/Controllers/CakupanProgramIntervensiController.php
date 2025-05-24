<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CakupanProgramIntervensiImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CakupanProgramIntervensi;
use Illuminate\Support\Facades\Auth;

class CakupanProgramIntervensiController extends Controller
{
    public function uploadData(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:xlsx,xls',
            'tahun' => 'required|integer'
        ]);

        $tahun = $request->tahun;

        $import = new CakupanProgramIntervensiImport($tahun);
        Excel::import($import, $request->file('file_import'));

        $errors = $import->getErrors();

        if (!empty($errors)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Sebagian kolom indikator kosong.',
                'errors' => $errors,
            ], 207);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diimpor sepenuhnya.'
        ], 200);
    }

    public function index(Request $request)
    {
        $tahun      = $request->input('tahun');
        $kecamatan  = $request->input('kecamatan');
        $puskesmas  = $request->input('puskesmas');
        $desa       = $request->input('desa');

        $listTahun = CakupanProgramIntervensi::select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $tahun = $tahun ?: $listTahun->first();

        $query = CakupanProgramIntervensi::with([
            'desa.puskesmas.kecamatan',
            'indikatorCakupan'
        ])->where('tahun', $tahun); 

        if (!empty($kecamatan)) {
            $query->whereHas('desa.puskesmas.kecamatan', function ($q) use ($kecamatan) {
                $q->where('nama_kecamatan', $kecamatan);
            });
        }

        if (!empty($puskesmas)) {
            $query->whereHas('desa.puskesmas', function ($q) use ($puskesmas) {
                $q->where('nama_puskesmas', $puskesmas);
            });
        }

        if (!empty($desa)) {
            $query->whereHas('desa', function ($q) use ($desa) {
                $q->where('nama_desa', $desa);
            });
        }

        $data = $query->get()
            ->groupBy(['desa.puskesmas.kecamatan.nama_kecamatan', 'desa.puskesmas.nama_puskesmas', 'desa.nama_desa'])
            ->map(function ($desaData) use ($tahun) {
                return $desaData->map(function ($puskesmasData) use ($tahun) {
                    return $puskesmasData->map(function ($items) use ($tahun) {
                        $row = [
                            'tahun'      => $tahun,
                            'desa'       => $items->first()->desa->nama_desa,
                            'puskesmas'  => $items->first()->desa->puskesmas->nama_puskesmas,
                            'kecamatan'  => $items->first()->desa->puskesmas->kecamatan->nama_kecamatan,
                        ];
                        foreach ($items as $item) {
                            $row[$item->indikatorCakupan->nama_indikator] = $item->nilai;
                        }
                        return $row;
                    });
                });
            })
            ->values()
            ->flatten(2);
            $lastUpdated = $query->max('updated_at');

        return response()->json([
            'data' => $data,
            'list_tahun' => $listTahun,
            'last_updated' => $lastUpdated,
        ]);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_desa' => 'required|exists:desa,id_desa',
            'id_indikator_cakupan' => 'required|exists:indikator_cakupan,id_indikator_cakupan',
            'tahun' => 'required|numeric|min:2000|max:' . date('Y'),
            'nilai' => 'required|numeric|min:0',
        ]);

        $data = CakupanProgramIntervensi::create([
            'id_desa' => $validatedData['id_desa'],
            'id_indikator_cakupan' => $validatedData['id_indikator_cakupan'],
            'tahun' => $validatedData['tahun'],
            'nilai' => $validatedData['nilai'],
            'id_user' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data cakupan berhasil ditambahkan!',
            'data' => $data
        ], 201);
    }

    public function show($id)
    {
        $data = CakupanProgramIntervensi::with(['desa', 'indikatorCakupan'])->find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail data cakupan',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $data = CakupanProgramIntervensi::where('id_cakupan_program_intervensi', $id)
            ->where('id_user', $userId) 
            ->first();

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan atau Anda tidak memiliki izin untuk mengedit data ini'
            ], 403);
        }

        $validatedData = $request->validate([
            'nilai' => 'required|numeric|min:0',
        ]);

        $data->update([
            'nilai' => $validatedData['nilai']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data cakupan berhasil diperbarui!',
            'data' => $data
        ]);
    }



    public function destroy($id)
    {
        $data = CakupanProgramIntervensi::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data cakupan berhasil dihapus!'
        ]);
    }

    public function getData()
    {
        $latestYear = CakupanProgramIntervensi::max('tahun'); 

        $data = CakupanProgramIntervensi::with([
                'desa.puskesmas.kecamatan',
                'indikatorCakupan'
            ])
            ->where('tahun', $latestYear) 
            ->get()
            ->groupBy(['desa.puskesmas.kecamatan.nama_kecamatan', 'desa.puskesmas.nama_puskesmas', 'desa.nama_desa'])
            ->map(function ($desaData) {
                return $desaData->map(function ($puskesmasData) {
                    return $puskesmasData->map(function ($items) {
                        $row = [
                            'desa'       => $items->first()->desa->nama_desa,
                            'puskesmas'  => $items->first()->desa->puskesmas->nama_puskesmas,
                            'kecamatan'  => $items->first()->desa->puskesmas->kecamatan->nama_kecamatan,
                        ];
                        foreach ($items as $item) {
                            $row[$item->indikatorCakupan->nama_indikator] = $item->nilai;
                        }
                        return $row;
                    });
                });
            })
            ->values()
            ->flatten(2);

        return response()->json($data);
    }

    public function getTahun()
    {
        $years = CakupanProgramIntervensi::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return response()->json([
            'status' => 'success',
            'count' => $years->count(),
            'tahun' => $years
        ]);
    }

    public function getDashboard(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $kecamatan = $request->input('kecamatan');
        $puskesmas = $request->input('puskesmas');
        $desa = $request->input('desa');

        $query = CakupanProgramIntervensi::with(['indikatorCakupan', 'desa.puskesmas.kecamatan'])
            ->where('tahun', $tahun);

        if (!empty($kecamatan)) {
            $query->whereHas('desa.puskesmas.kecamatan', function ($q) use ($kecamatan) {
                $q->where('nama_kecamatan', $kecamatan);
            });
        }

        if (!empty($puskesmas)) {
            $query->whereHas('desa.puskesmas', function ($q) use ($puskesmas) {
                $q->where('nama_puskesmas', $puskesmas);
            });
        }

        if (!empty($desa)) {
            $query->whereHas('desa', function ($q) use ($desa) {
                $q->where('nama_desa', $desa);
            });
        }

        $data = $query->get();

        $summary = $data->groupBy('indikatorCakupan.nama_indikator')->map(function ($items, $indikator) {
            $totalNilai = $items->sum('nilai');
            $jumlahData = $items->count();

            return [
                'indikator' => $indikator,
                'total_nilai' => $totalNilai,
                'jumlah_data' => $jumlahData,
                'rata_rata' => $jumlahData > 0 ? round($totalNilai / $jumlahData) : 0,
            ];
        })->values();

        return response()->json([
            'data' => $summary
        ]);
    }

    public function getTotalAnakPerKecamatan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = CakupanProgramIntervensi::with(['indikatorCakupan', 'desa.puskesmas.kecamatan'])
            ->where('tahun', $tahun)
            ->whereHas('indikatorCakupan', function ($q) {
                $q->where('nama_indikator', 'Total Populasi Anak');
            })
            ->get();

        $summary = $data->groupBy(function ($item) {
            return optional($item->desa->puskesmas->kecamatan)->nama_kecamatan;
        })->map(function ($items, $kecamatan) {
            $totalAnak = $items->sum('nilai'); 

            return [
                'kecamatan' => $kecamatan,
                'total_anak' => $totalAnak
            ];
        })->values();

        return response()->json([
            'data' => $summary
        ]);
    }

    public function getTotalKeluargaPerKecamatan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = CakupanProgramIntervensi::with(['indikatorCakupan', 'desa.puskesmas.kecamatan'])
            ->where('tahun', $tahun)
            ->whereHas('indikatorCakupan', function ($q) {
                $q->where('nama_indikator', 'Total Populasi Keluarga');
            })
            ->get();

        $summary = $data->groupBy(function ($item) {
            return optional($item->desa->puskesmas->kecamatan)->nama_kecamatan;
        })->map(function ($items, $kecamatan) {
            $totalAnak = $items->sum('nilai'); 

            return [
                'kecamatan' => $kecamatan,
                'total_keluarga' => $totalAnak
            ];
        })->values();

        return response()->json([
            'data' => $summary
        ]);
    }


}
