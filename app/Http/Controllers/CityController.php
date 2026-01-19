<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        return response()->json(City::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'country_id' => 'required|exists:countries,id'
        ]);

        $exists = City::where('name', $request->name)
            ->where('country_id', $request->country_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ez a város már létezik az adott országban!'], 409);
        }

        $city = City::create($request->all());

        return response()->json([
            'message' => 'Város létrehozva',
            'data' => $city
        ], 201);
    }

    public function show($id)
    {
        $city = City::find($id);
        if (!$city) return response()->json(['message' => 'Város nem található'], 404);

        return response()->json($city);
    }

    public function update(Request $request, $id)
    {
        $city = City::find($id);
        if (!$city) return response()->json(['message' => 'Város nem található'], 404);

        $request->validate([
            'name' => 'required|string|min:2',
            'country_id' => 'required|exists:countries,id'
        ]);

        $city->update($request->all());

        return response()->json([
            'message' => 'Város módosítva',
            'data' => $city
        ]);
    }

    public function destroy($id)
    {
        $city = City::find($id);
        if (!$city) return response()->json(['message' => 'Város nem található'], 404);

        $city->delete();

        return response()->json(['message' => 'Város törölve']);
    }
}
