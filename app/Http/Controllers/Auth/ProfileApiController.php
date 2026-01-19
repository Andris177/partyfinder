<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user(); // az auth:sanctum alapján kapjuk a usert

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6'
        ]);

        // Adatok frissítése
        if ($request->name)  $user->name = $request->name;
        if ($request->email) $user->email = $request->email;

        // Ha küld jelszót, frissítjük
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil frissítve',
            'user'    => $user
        ]);
    }
}
