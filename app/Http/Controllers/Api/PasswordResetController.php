<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetRequest;

class PasswordResetController extends Controller
{
    // User minta reset password
    public function requestReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Email tidak ditemukan'], 404);
        }

        // Cek apakah ada request pending
        $existingRequest = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email ini sudah meminta reset password sebelumnya. Silakan tunggu persetujuan admin.',
                'request_id' => $existingRequest->id
            ], 409);
        }

        $resetRequest = PasswordResetRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'used' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request reset berhasil dikirim',
            'request_id' => $resetRequest->id
        ], 201);
    }

    // Admin menyetujui dan reset password ke default
    public function approveReset($id)
    {
        $resetRequest = PasswordResetRequest::findOrFail($id);
        if ($resetRequest->used) {
            return response()->json(['message' => 'Request sudah dipakai'], 400);
        }

        $user = User::findOrFail($resetRequest->user_id);

        // Reset password ke default 123456
        $user->password = Hash::make('123456');
        $user->save();

        $resetRequest->status = 'done';
        $resetRequest->save();

        return response()->json([
            'message' => 'Password berhasil direset',
            'user_email' => $user->email,
            'default_password' => '123456'
        ]);
    }

    // Cek status reset password
    public function checkStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $resetRequest = PasswordResetRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'done'])
            ->where('used', false)
            ->latest()
            ->first();

        if (!$resetRequest) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request reset password sudah tidak berlaku atau sudah digunakan'
            ], 404);
        }

        return response()->json([
            'status' => $resetRequest->status,
            'message' => $resetRequest->status === 'done'
                ? 'Reset sudah disetujui, silakan login dengan password default 123456 lalu ubah password.'
                : 'Request masih pending, tunggu admin menyetujui.'
        ]);
    }

    // User ganti password setelah direset
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Tandai request terakhir yang done sebagai used
        $lastRequest = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'done')
            ->where('used', false)
            ->latest()
            ->first();

        if ($lastRequest) {
            $lastRequest->update(['used' => true]);
        }

        return response()->json([
            'message' => 'Password berhasil diganti',
        ]);
    }
}
