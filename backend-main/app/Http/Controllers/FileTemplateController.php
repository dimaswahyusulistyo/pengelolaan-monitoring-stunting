<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileTemplate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class FileTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = FileTemplate::query();

        if ($request->has('search')) {
            $query->where('nama_template', 'like', '%' . $request->search . '%');
        }

        $sortFilter = $request->input('sort_filter', 'A-Z');

        switch (strtoupper($sortFilter)) {
            case 'A-Z':
                $query->orderBy('nama_template', 'asc');
                break;
            case 'Z-A':
                $query->orderBy('nama_template', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('nama_template', 'asc');
                break;
        }

        $perPage = $request->get('per_page', 10);
        $validPerPageOptions = [5, 10, 15, 20];
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }

        $fileTemplate = $query->paginate($perPage);

        return response()->json($fileTemplate, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_template' => 'required|string|max:255',
            'file_template' => 'required|file|mimes:xls,xlsx',
        ]);

        if ($request->hasFile('file_template')) {
            $file = $request->file('file_template');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/file_template'), $fileName);
            $validatedData['file_template'] = 'uploads/file_template/' . $fileName;
        }

        $fileTemplate = FileTemplate::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Template berhasil ditambahkan',
            'data' => $fileTemplate,
        ], 201);
    }

    public function show($id)
    {
        $fileTemplate = FileTemplate::findOrFail($id);
        return response()->json($fileTemplate, 200);
    }

    public function update(Request $request, $id)
    {
        $fileTemplate = FileTemplate::findOrFail($id);
        
        $validatedData = $request->validate([
            'nama_template' => 'required|string|max:255',
            'file_template' => 'nullable|file|mimes:xls,xlsx',
        ]);

        if ($request->hasFile('file_template')) {
            if (file_exists(public_path($fileTemplate->file_template))) {
                unlink(public_path($fileTemplate->file_template));
            }
            $file = $request->file('file_template');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/file_template'), $fileName);
            $validatedData['file_template'] = 'uploads/file_template/' . $fileName;
        }

        $fileTemplate->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Template berhasil diperbarui', 
            'data' => $fileTemplate
        ], 200);
    }

    public function destroy($id)
    {
        $fileTemplate = FileTemplate::findOrFail($id);
        
        if ($fileTemplate->file_template && file_exists(public_path($fileTemplate->file_template))) {
            unlink(public_path($fileTemplate->file_template));
        }
        $fileTemplate->delete();

        return response()->json([
            'message' => 'Template berhasil dihapus'], 204);
    }

    public function download($id)
    {
        $fileTemplate = FileTemplate::findOrFail($id);
        $filePath = public_path($fileTemplate->file_template);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        return Response::download($filePath, basename($filePath));
    }

    public function templatesByRole(Request $request)
    {
        $query = FileTemplate::query();

        $userRole = Auth::user()->role->nama_role ?? null;

        if ($userRole !== 'Admin') {
            if ($userRole === 'Kader Desa') {
                $query->where(function ($q) {
                    $q->where('nama_template', 'like', '%anak stunting%')
                    ->orWhere('nama_template', 'like', '%keluarga berisiko%');
                });
            } elseif ($userRole === 'Dinas Kesehatan') {
                $query->where(function ($q) {
                    $q->where('nama_template', 'like', '%Dinas Kesehatan%')
                    ->orWhere('nama_template', 'like', '%anak stunting%');
                });
            } elseif ($userRole === 'Dinas KB') {
                $query->where(function ($q) {
                    $q->where('nama_template', 'like', '%Dinas KB%')
                    ->orWhere('nama_template', 'like', '%keluarga berisiko%');
                });
            } else {
                $query->where('nama_template', 'like', '%' . $userRole . '%');
            }
        }

        if ($request->has('search')) {
            $query->where('nama_template', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $order = $request->get('order', 'asc');
            $query->orderBy($sortBy, $order);
        }

        $perPage = $request->get('per_page', 10);
        $fileTemplate = $query->paginate($perPage);

        return response()->json($fileTemplate, 200);
    }
}
