<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller {

    // GET /api/settings
    public function index(Request $request) {
        return response()->json($request->user());
    }

    // PUT /api/settings
    public function update(Request $request) {
        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'business_name' => 'sometimes|string|max:255',
            'phone'         => 'sometimes|string|max:20',
            'password'      => 'sometimes|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $data = $request->only(['name', 'email', 'business_name', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Pengaturan berhasil disimpan',
            'user'    => $user
        ]);
    }
}