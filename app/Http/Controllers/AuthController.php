<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6',
            'role'      => 'in:admin,user',                // csak ez a 2 érték érvényes
            'avatar'    => 'nullable|image|max:2048',      // max 2MB, csak kép lehet
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Hibás adatok',
                'errors' => $validator->errors()
            ], 422);
        }

        // Avatar feltöltése (opcionális)
        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $avatarUrl = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'                  => $request->name,
            'email'                 => $request->email,
            'password'              => Hash::make($request->password),
            'role'                  => $request->role ?? 'user',  // ha nincs megadva -> user
            'avatar_url'            => $avatarUrl,                // Mentjük az avatar url-t
            'notification_enabled'  => true
        ]);

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            'message' => 'Sikeres regisztráció',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }
}
