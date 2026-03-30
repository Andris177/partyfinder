<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    // Átirányítás a Google/Apple felé
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // Amikor a Google/Apple visszadobja a felhasználót
    public function callback($provider)
    {
        try {
            // Megkapjuk az adatokat a szolgáltatótól
            $socialUser = Socialite::driver($provider)->user();

            // Megnézzük, van-e már ilyen userünk
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Ha a user létezik, de még nem volt összekötve ezzel a providerrel, frissítjük
                if (!$user->provider_id) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                    ]);
                }
                // Beléptetjük
                Auth::login($user);
            } else {
                // Ha teljesen új felhasználó, regisztráljuk a háttérben
                $user = User::create([
                    'name' => $socialUser->getName() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'password' => Hash::make(Str::random(24)) // Generálunk egy random jelszót biztosítékként
                ]);

                Auth::login($user);
            }

            // Sikeres belépés után irány a főoldal vagy a dashboard!
            return redirect('/'); 

        } catch (\Exception $e) {
            // Ha valami elszáll (pl. elutasította a belépést)
            return redirect('/login')->withErrors(['error' => 'Hiba történt a bejelentkezés során.']);
        }
    }
}