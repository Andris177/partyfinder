<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Controllerek importálása
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\EventReactionController; // 👈 EZT KERESTE!
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GroupRequestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\ProfileApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// ✅ 1. PUBLIKUS ÚTVONALAK (Bárki elérheti)
// ==========================================

// Alapadatok
Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
Route::apiResource('cities', CityController::class)->only(['index', 'show']);
Route::apiResource('locations', LocationController::class)->only(['index', 'show']);
Route::apiResource('event-types', EventTypeController::class)->only(['index', 'show']);

// Események (FONTOS: a 'filter' legyen az '{id}' előtt!)
Route::get('/events/filter', [EventController::class, 'filter']); 
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

Route::get('/locations', function (Illuminate\Http\Request $request) {
    // Ha van city_id, akkor szűrünk rá
    if ($request->has('city_id')) {
        return \App\Models\Location::where('city_id', $request->city_id)->get();
    }
    // Ha nincs, visszaadjuk mindet (vagy üreset, ahogy tetszik)
    return \App\Models\Location::all();
});

// Regisztráció és Login (JSON válaszokkal)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Hibás adatok'], 401);
    }
    return response()->json([
        'user' => $user,
        'token' => $user->createToken('auth_token')->plainTextToken
    ]);
});

// ==========================================
// 🔒 2. VÉDETT ÚTVONALAK (Csak bejelentkezve)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::put('/profile', [ProfileApiController::class, 'update']);

    // Saját események (Ez védett kell legyen!)
    Route::get('/events/my', [EventController::class, 'myEvents']);
    
    // ⭐ Kedvencek
    Route::post('/events/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/events/favorites', [FavoriteController::class, 'myFavorites']);

    // Egyéb funkciók
    Route::apiResource('group-requests', GroupRequestController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('notifications', NotificationController::class);

    // Facebook frissítés (User is hívhatja?)
    Route::post('/events/{id}/refresh-facebook', [EventController::class, 'refreshFacebookStats']);
});

// ==========================================
// 👑 3. ADMIN ÚTVONALAK (Csak Admin)
// ==========================================
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::post('/events/{id}/image', [EventController::class, 'uploadImage']);
    
    // Teljes CRUD az erőforrásokhoz (létrehozás/törlés)
    Route::apiResource('countries', CountryController::class)->except(['index', 'show']);
    Route::apiResource('cities', CityController::class)->except(['index', 'show']);
    Route::apiResource('locations', LocationController::class)->except(['index', 'show']);
});