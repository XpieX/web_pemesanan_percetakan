<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        return response()->json([
            'status' => true,
            'message' => "Data ditemukan",
            'data' => UserResource::collection($users),
        ], 200);
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => "Data Ditemukan",
                'data' => new UserResource($user),
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Data Tidak Ditemukan",
            ], 404);
        }
    }

    public function store(Request $request)
    {
        // kamu bisa tambah validasi & logic simpan
    }

    public function update(Request $request, string $id)
    {
        // nanti tambahkan jika perlu
    }

    public function destroy(string $id)
    {
        // nanti tambahkan jika perlu
    }
}
