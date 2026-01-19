<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ProfileApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventReactionController;
use App\Http\Controllers\{
    CountryController, CityController, LocationController, EventController,
    EventTypeController, FavoriteController, GroupRequestController,
    MessageController, NotificationController
};

// ✅ Public API
Route::apiResource('countries', CountryController::class);
Route::apiResource('cities', CityController::class);
Route::apiResource('locations', LocationController::class);

// ✅ Publikus event lekérések
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/my', [EventController::class, 'myEvents']);
Route::get('/events/filter', [EventController::class, 'filter']);

Route::apiResource('event-types', EventTypeController::class);

// ✅ Auth: login/register
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Hibás belépés'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
});

Route::post('/register', [AuthController::class, 'register']);

// ✅ Bejelentkezett user funkciók
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('group-requests', GroupRequestController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('notifications', NotificationController::class);
    Route::put('/profile', [ProfileApiController::class, 'update']);

    // ⭐ Kedvencek
    Route::post('/events/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/events/favorites', [FavoriteController::class, 'myFavorites']);

    // ⭐ Reakció (interested/going)
    Route::post('/events/{id}/react', [EventReactionController::class, 'react']);
});

// ✅ Admin event kezelés
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::post('/events/{id}/image', [EventController::class, 'uploadImage']);
});

Route::middleware('auth:sanctum')->post('/events/{id}/refresh-facebook', [EventController::class, 'refreshFacebookStats']);

Route::get('/events/{id}/refresh-facebook', [EventController::class, 'refreshFacebookStats']);
