<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('user_name', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['البيانات المدخلة غير صحيحة.'],
            ]);
        }

        // Ensure only MOH users can log in via API
        if (! $user->isMinistry()) {
            throw ValidationException::withMessages([
                'username' => ['هذا الحساب غير مصرح له بالدخول كـ وزارة.'],
            ]);
        }

        // Get the associated department name
        $departmentName = $user->mohDepartment?->name ?? 'Unknown Department';

        $token = $user->createToken('moh-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'department' => $departmentName,
        ]);
    }
}
