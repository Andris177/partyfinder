<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return response()->json(
        Location::with(['city.country'])->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'city_id' => 'required|exists:cities,id',
            'country_id' => 'required|exists:countries,id',
            'address' => 'required|string|min:3',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Helyszín létrehozva',
            'data' => $location
        ], 201);
    }



    public function show($id)
    {
        $location = Location::with(['city.country'])->find($id);
        if (!$location) return response()->json(['message' => 'Helyszín nem található'], 404);

        return response()->json($location);
    }

    public function update(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) return response()->json(['message' => 'Helyszín nem található'], 404);

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|min:3'
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Helyszín módosítva',
            'data' => $location
        ]);
    }


    public function destroy($id)
    {
        $location = Location::find($id);
        if (!$location) return response()->json(['message' => 'Helyszín nem található'], 404);

        $location->delete();

        return response()->json(['message' => 'Helyszín törölve']);
    }
}
