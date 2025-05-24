<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportTPPS;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportTppsController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportTPPS::query();
    
        if ($request->has('search')) {
            $query->where('nama_kegiatan', 'like', '%' . $request->search . '%');
        }
    
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('nama_kegiatan', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('nama_kegiatan', 'desc');
                    break;
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
    
        $perPage = $request->get('per_page', 10);
        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jumlah_anggaran' => 'required|integer|min:0|max:10000000000',
            'kerangka_acuan' => 'required|array|min:1|max:5',
            'kerangka_acuan.*' => 'file|mimes:pdf|max:15360',
            'dokumentasi_kegiatan_tpps' => 'required|array|min:1|max:5',
            'dokumentasi_kegiatan_tpps.*' => 'file|mimes:jpg,jpeg,png,pdf|max:15360',
        ], [
            'kerangka_acuan.required' => 'File kerangka acuan harus diunggah',
            // 'kerangka_acuan.max' => 'Maksimal 5 file kerangka acuan',
            'kerangka_acuan.min' => 'Minimal 1 file kerangka acuan',
            'kerangka_acuan.*.mimes' => 'File kerangka acuan harus berformat PDF',
            'dokumentasi_kegiatan_tpps.required' => 'File dokumentasi harus diunggah',
            'dokumentasi_kegiatan_tpps.min' => 'Minimal 1 file dokumentasi',
            // 'dokumentasi_kegiatan_tpps.max' => 'Maksimal 5 file dokumentasi',
            'dokumentasi_kegiatan_tpps.*.mimes' => 'File dokumentasi harus berformat JPG, JPEG, PNG, atau PDF',
        ]);
    
        try {
            $report = new ReportTPPS();
            $report->nama_kegiatan = $validatedData['nama_kegiatan'];
            $report->jumlah_anggaran = $validatedData['jumlah_anggaran'];
            
            $kakPaths = [];
            if ($request->hasFile('kerangka_acuan')) {
                foreach ($request->file('kerangka_acuan') as $file) {
                    $name = time() . '_' . $file->getClientOriginalName();
                    $path = 'uploads/kerangka_acuan';
                    $file->move(public_path($path), $name);
                    $kakPaths[] = $path . '/' . $name;
                }
                $report->kerangka_acuan = $kakPaths;
            }
            
            $dokumentasiPaths = [];
            if ($request->hasFile('dokumentasi_kegiatan_tpps')) {
                foreach ($request->file('dokumentasi_kegiatan_tpps') as $file) {
                    $name = time() . '_' . $file->getClientOriginalName();
                    $path = 'uploads/dokumentasi_kegiatan_tpps';
                    $file->move(public_path($path), $name);
                    $dokumentasiPaths[] = $path . '/' . $name;
                }
                $report->dokumentasi_kegiatan_tpps = $dokumentasiPaths;
            }
            
            $report->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan kegiatan TPPS berhasil ditambahkan',
                'data' => $report,
            ], 201);
        } catch (\Exception $e) {
            if (!empty($kakPaths)) {
                foreach ($kakPaths as $path) {
                    if (file_exists(public_path($path))) {
                        unlink(public_path($path));
                    }
                }
            }
            if (!empty($dokumentasiPaths)) {
                foreach ($dokumentasiPaths as $path) {
                    if (file_exists(public_path($path))) {
                        unlink(public_path($path));
                    }
                }
            }
            
            Log::error('Error creating report: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan laporan kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $report = ReportTPPS::findOrFail($id);
        return response()->json($report, 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $report = ReportTPPS::findOrFail($id);
            
            $validatedData = $request->validate([
                'nama_kegiatan' => 'required|string|max:255',
                'jumlah_anggaran' => 'required|integer|min:0|max:10000000000',
                'kerangka_acuan' => 'nullable|array|max:5',
                'kerangka_acuan.*' => 'file|mimes:pdf|max:15360',
                'dokumentasi_kegiatan_tpps' => 'nullable|array|max:5',
                'dokumentasi_kegiatan_tpps.*' => 'file|mimes:jpg,jpeg,png,pdf|max:15360',
                // 'delete_kerangka_acuan' => 'nullable|array',
                // 'delete_dokumentasi' => 'nullable|array',
            ], [
                'kerangka_acuan.required' => 'File kerangka acuan harus diunggah',
                // 'kerangka_acuan.max' => 'Maksimal 5 file kerangka acuan',
                'kerangka_acuan.min' => 'Minimal 1 file kerangka acuan',
                'kerangka_acuan.*.mimes' => 'File kerangka acuan harus berformat PDF',
                'dokumentasi_kegiatan_tpps.required' => 'File dokumentasi harus diunggah',
                'dokumentasi_kegiatan_tpps.min' => 'Minimal 1 file dokumentasi',
                // 'dokumentasi_kegiatan_tpps.max' => 'Maksimal 5 file dokumentasi',
                'dokumentasi_kegiatan_tpps.*.mimes' => 'File dokumentasi harus berformat JPG, JPEG, PNG, atau PDF',
            ]);

            $report->nama_kegiatan = $validatedData['nama_kegiatan'];
            $report->jumlah_anggaran = $validatedData['jumlah_anggaran'];

            $currentKakFiles = $report->kerangka_acuan ?? [];
            if (!empty($request->delete_kerangka_acuan)) {
                foreach ($request->delete_kerangka_acuan as $fileToDelete) {
                    if (file_exists(public_path($fileToDelete))) {
                        unlink(public_path($fileToDelete));
                    }
                    $currentKakFiles = array_filter($currentKakFiles, function($file) use ($fileToDelete) {
                        return $file !== $fileToDelete;
                    });
                }
                $currentKakFiles = array_values($currentKakFiles);
            }

            if ($request->hasFile('kerangka_acuan')) {
                foreach ($request->file('kerangka_acuan') as $file) {
                    $name = time() . '_' . $file->getClientOriginalName();
                    $path = 'uploads/kerangka_acuan';
                    $file->move(public_path($path), $name);
                    $currentKakFiles[] = $path . '/' . $name;
                }
            }
            $report->kerangka_acuan = $currentKakFiles; 

            $currentDokumentasiFiles = $report->dokumentasi_kegiatan_tpps ?? [];
            if (!empty($request->delete_dokumentasi)) {
                foreach ($request->delete_dokumentasi as $fileToDelete) {
                    if (file_exists(public_path($fileToDelete))) {
                        unlink(public_path($fileToDelete));
                    }
                    $currentDokumentasiFiles = array_filter($currentDokumentasiFiles, function($file) use ($fileToDelete) {
                        return $file !== $fileToDelete;
                    });
                }
                $currentDokumentasiFiles = array_values($currentDokumentasiFiles);
            }

            if ($request->hasFile('dokumentasi_kegiatan_tpps')) {
                foreach ($request->file('dokumentasi_kegiatan_tpps') as $file) {
                    $name = time() . '_' . $file->getClientOriginalName();
                    $path = 'uploads/dokumentasi_kegiatan_tpps';
                    $file->move(public_path($path), $name);
                    $currentDokumentasiFiles[] = $path . '/' . $name;
                }
            }
            $report->dokumentasi_kegiatan_tpps = $currentDokumentasiFiles;

            $report->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan kegiatan TPPS berhasil diperbarui',
                'data' => $report
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan kegiatan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating report: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui laporan kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $report = ReportTPPS::findOrFail($id);

            $filesToDelete = array_merge(
                (array) $report->kerangka_acuan,
                (array) $report->dokumentasi_kegiatan_tpps
            );

            foreach ($filesToDelete as $file) {
                if (is_string($file)) {
                    if (Storage::exists($file)) {
                        Storage::delete($file);
                    }
                    $publicPath = public_path($file);
                    if (file_exists($publicPath)) {
                        unlink($publicPath);
                    }
                }
            }

            $report->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan kegiatan berhasil dihapus beserta semua file terkait'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus laporan kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function preview($filename)
    {
        $path = public_path('uploads/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}