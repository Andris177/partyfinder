<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\City; // <--- Ez kellett a legördülő listához!
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Helyszínek listázása
     */
    public function index()
    {
        // Ha API hívás (JSON-t vár), akkor JSON-t adunk vissza
        if (request()->wantsJson()) {
            return response()->json(
                Location::with(['city.country'])->orderBy('name')->get()
            );
        }

        // Ha Admin felületen vagyunk, akkor a listázó nézetet adjuk vissza
        // (Feltételezem, van egy index.blade.php-d is, ha nincs, hagyd ki)
        $locations = Location::with(['city.country'])->orderBy('name')->paginate(20);
        return view('admin.locations.index', compact('locations')); 
    }

    /**
     * Új helyszín mentése
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'city_id' => 'required|exists:cities,id',
            'country_id' => 'required|exists:countries,id', // Ez fontos!
            'address' => 'required|string|min:3',
            'lat' => 'nullable|numeric', // Koordináták engedélyezése
            'lng' => 'nullable|numeric',
        ]);

        $location = Location::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Helyszín létrehozva',
                'data' => $location
            ], 201);
        }

        return redirect()->back()->with('success', 'Helyszín sikeresen létrehozva!');
    }

    /**
     * Egy helyszín adatainak lekérése (API)
     */
    public function show($id)
    {
        $location = Location::with(['city.country'])->find($id);
        if (!$location) return response()->json(['message' => 'Helyszín nem található'], 404);

        return response()->json($location);
    }

    // --- ITT VANNAK AZ ÚJ RÉSZEK A SZERKESZTÉSHEZ ---

    /**
     * SZERKESZTŐ NÉZET MEGJELENÍTÉSE (Ez kellett neked!)
     */
    public function edit($id)
    {
        // Megkeressük a helyszínt, vagy hibát dobunk ha nincs
        $location = Location::findOrFail($id);
        
        // Lekérjük a városokat a legördülő menühöz
        $cities = City::orderBy('name')->get();

        // Itt hívjuk meg a Blade nézetet.
        // Ha a mappád neve "admin/pages", akkor: 'admin.pages.locations.edit'
        // Ha a szabványos Laravel structure: 'admin.locations.edit'
        // Állítsd be a saját mappád szerint!
        return view('admin.pages.edit', compact('location', 'cities'));
    }

    /**
     * ADATOK FRISSÍTÉSE (Ez javítja a koordinátát)
     */
    public function update(Request $request, $id)
    {
        $location = Location::find($id);
        
        if (!$location) {
            if ($request->wantsJson()) return response()->json(['message' => 'Helyszín nem található'], 404);
            return redirect()->back()->with('error', 'Helyszín nem található');
        }

        // Validálás (kiegészítve a koordinátákkal!)
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|min:3',
            'lat' => 'nullable|numeric', // Fontos: csak szám lehet!
            'lng' => 'nullable|numeric', // Fontos: csak szám lehet!
        ]);

        // Ha a város változott, frissíthetjük az országot is automatikusan
        if ($request->has('city_id') && $request->city_id != $location->city_id) {
            $city = City::find($request->city_id);
            if ($city) {
                $validated['country_id'] = $city->country_id;
            }
        }

        // Frissítés
        $location->update($validated);

        // Válasz (Ha API hívja, JSON-t küld, ha te a böngészőből, akkor visszairányít)
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Helyszín módosítva',
                'data' => $location
            ]);
        }

        // Visszairányítás a listához üzenettel
        return redirect()->route('admin.pages.index')->with('success', 'Helyszín (és koordináta) sikeresen frissítve!');
    }

    // ------------------------------------------------

    public function destroy($id)
    {
        $location = Location::find($id);
        if (!$location) {
            if (request()->wantsJson()) return response()->json(['message' => 'Helyszín nem található'], 404);
            return redirect()->back()->with('error', 'Nem található');
        }

        $location->delete();

        if (request()->wantsJson()) return response()->json(['message' => 'Helyszín törölve']);
        return redirect()->back()->with('success', 'Helyszín törölve');
    }
}