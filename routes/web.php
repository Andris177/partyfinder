<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Főoldal átirányítás (Így kérted a kódodban)
Route::get('/', function () {
    return redirect('/events');
});

// Dashboard átirányítás
Route::get('/dashboard', function () {
    return redirect('/events');
})->middleware(['auth', 'verified'])->name('dashboard');

// 🟢 1. KONKRÉT ÚTVONALAK
Route::middleware('auth')->group(function () {
    // Profil kezelés
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Események
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');

    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Saját események
    Route::get('/my-events', [EventController::class, 'myEvents'])->name('events.my');
    
    // ✅ REAKCIÓ ÉS KOMMENT (Az EventController-be irányítjuk, ahogy a te kódodban volt!)
    Route::post('/events/{id}/react', [EventController::class, 'react'])->name('events.react');
    Route::post('/events/{id}/comment', [EventController::class, 'storeComment'])->name('events.comment');
    
    Route::get('/events/{id}/json', [EventController::class, 'apiShow'])->name('events.json');

    // 1. EZEK LEGYENEK ELÖL:
    Route::get('/admin/locations/{id}/edit', [LocationController::class, 'edit'])->name('admin.locations.edit');
    Route::put('/admin/locations/{id}', [LocationController::class, 'update'])->name('admin.locations.update');

    // 2. EZ LEGYEN MÖGÖTTE:
    Route::resource('admin/pages', App\Http\Controllers\FacebookPageController::class)
    ->names('admin.pages');
});

// 🟢 2. PUBLIKUS ÚTVONALAK
Route::get('/events', function () {
    return view('welcome'); // Visszatettem a welcome-ra, hogy a szép térképes nézeted legyen!
})->name('events.feed');

// 🟢 3. RÉSZLETEK (A végén)
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

require __DIR__.'/auth.php';