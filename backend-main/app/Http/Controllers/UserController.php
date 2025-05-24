<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Desa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('role', 'desa');

        if ($request->has('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('nama_role', $request->role);
            });
        }

        if ($request->has('desa')) {
            $query->whereHas('desa', function ($d) use ($request) {
                $d->where('nama_desa', $request->desa);
            });
        }

        if ($request->has('search')) {
            $query->where('username', 'like', '%' . $request->search . '%');
        }

        $sortFilter = $request->input('sort_filter', 'A-Z');

        switch (strtoupper($sortFilter)) {
            case 'A-Z':
                $query->orderBy('username', 'asc');
                break;
            case 'Z-A':
                $query->orderBy('username', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('username', 'asc');
                break;
        }

        $perPage = $request->input('per_page', 10);
        $validPerPageOptions = [5, 10, 15, 20];
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }

        $users = $query->paginate($perPage);

        return response()->json($users);
    }



    public function store(Request $request)
    {
        if ($request->has('id_role') && !Role::where('id_role', $request->id_role)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Role tidak ditemukan'
            ], 400);
        }

        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'id_role' => 'required|exists:roles,id_role',
            'id_desa' => 'nullable|exists:desa,id_desa',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/foto_profil'), $filename);
            $validatedData['foto_profil'] = 'uploads/foto_profil/' . $filename;
        }    

        $user = User::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil ditambahkan',
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::with('role', 'desa')->find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($request->has('id_role') && !Role::where('id_role', $request->id_role)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Role tidak ditemukan'
            ], 400);
        }

        if ($request->has('id_desa') && !Desa::where('id_desa', $request->id_desa)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Desa tidak ditemukan'
            ], 400);
        }

        $validatedData = $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $id . ',id_user',
            'password' => 'sometimes|string|min:8',
            'id_role' => 'sometimes|exists:roles,id_role',
            'id_desa' => 'sometimes|exists:desa,id_desa',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png'
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('foto_profil')) {

            if ($user->foto_profil && File::exists(public_path($user->foto_profil))) {
                File::delete(public_path($user->foto_profil));
            }
            
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/foto_profil'), $filename);
            $validatedData['foto_profil'] = 'uploads/foto_profil/' . $filename;
        }

        $user->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil diperbarui', 
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($user->foto_profil && File::exists(public_path($user->foto_profil))) {
            File::delete(public_path($user->foto_profil));
        }

        $user->delete();
        return response()->json([
            'message' => 'User berhasil dihapus'
        ], 204);
    }
}
