<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Regulasi;

class RegulasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Regulasi::query();

        if ($request->has('search')) {
            $query->where('nama_surat_regulasi', 'like', '%' . $request->search . '%');
        }

        $sortFilter = $request->input('sort_filter', 'A-Z');

        switch (strtoupper($sortFilter)) {
            case 'A-Z':
                $query->orderBy('nama_surat_regulasi', 'asc');
                break;
            case 'Z-A':
                $query->orderBy('nama_surat_regulasi', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('nama_surat_regulasi', 'asc');
                break;
        }

        $perPage = $request->get('per_page', 10);
        $validPerPageOptions = [5, 10, 15, 20];
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }

        $regulasi = $query->paginate($perPage);

        return response()->json($regulasi, 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_surat_regulasi' => 'required|string|max:255',
            'file_surat_regulasi' => 'required|file|mimes:pdf,doc,docx|max:15000',
        ]);

        if ($request->hasFile('file_surat_regulasi')) {
            $file = $request->file('file_surat_regulasi');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/file_regulasi'), $fileName);
            $validatedData['file_surat_regulasi'] = 'uploads/file_regulasi/' . $fileName;
        }

        $regulasi = Regulasi::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Regulasi berhasil ditambahkan',
            'data' => $regulasi,
        ], 201);
    }

    public function show($id)
    {
        $regulasi = Regulasi::findOrFail($id);
        return response()->json($regulasi, 200);
    }

    public function update(Request $request, $id)
    {
        $regulasi = Regulasi::findOrFail($id);
        
        $validatedData = $request->validate([
            'nama_surat_regulasi' => 'required|string|max:255',
            'file_surat_regulasi' => 'nullable|file|mimes:pdf,doc,docx|max:15000',
        ]);

        if ($request->hasFile('file_surat_regulasi')) {
            if (file_exists(public_path($regulasi->file_surat_regulasi))) {
                unlink(public_path($regulasi->file_surat_regulasi));
            }
            $file = $request->file('file_surat_regulasi');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/file_regulasi'), $fileName);
            $validatedData['file_surat_regulasi'] = 'uploads/file_regulasi/' . $fileName;
        }

        $regulasi->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Regulasi berhasil diperbarui', 
            'data' => $regulasi
        ], 200);
    }

    public function destroy($id)
    {
        $regulasi = Regulasi::findOrFail($id);
        
        if ($regulasi->file_surat_regulasi && file_exists(public_path($regulasi->file_surat_regulasi))) {
            unlink(public_path($regulasi->file_surat_regulasi));
        }
        $regulasi->delete();

        return response()->json([
            'message' => 'Regulasi berhasil dihapus'], 204);
    }

    public function preview($filename)
    {
        $filePath = public_path("uploads/file_regulasi/" . $filename);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }

}
