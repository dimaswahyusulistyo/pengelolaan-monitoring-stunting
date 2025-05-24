<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->get('type') === 'dropdown') {
            $roles = Role::select('id_role', 'nama_role')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'List role untuk dropdown',
                'data' => $roles,
            ]);
        }

        $query = Role::query();

        if ($request->has('search')) {
            $query->where('nama_role', 'like', '%' . $request->search . '%');
        }

        $sortFilter = strtolower($request->input('sort_filter', 'a-z'));

        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $order = $request->get('order', 'asc');
            $query->orderBy($sortBy, $order);
        } else {
            switch ($sortFilter) {
                case 'newest':
                    $query->orderByDesc('created_at');
                    break;
                case 'oldest':
                    $query->orderBy('created_at');
                    break;
                case 'z-a':
                    $query->orderBy('nama_role', 'desc');
                    break;
                case 'a-z':
                default:
                    $query->orderBy('nama_role', 'asc');
                    break;
            }
        }

        $perPage = (int) $request->get('per_page', 10);
        $validPerPage = [5, 10, 15, 20];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }

        $roles = $query->paginate($perPage);

        return response()->json($roles, 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_role' => 'required|string|max:255',
        ]);

        $role = Role::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Role berhasil ditambahkan',
            'data' => $role,
        ], 201);
    }

    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail role ditemukan',
            'data' => $role,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_role' => 'required|string|max:255',
        ]);

        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $role->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Role berhasil diupdate',
            'data' => $role,
        ]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role berhasil dihapus',
            'data' => null,
        ]);
    }
}
