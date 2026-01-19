<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(Country::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|unique:countries,name'
        ], [
            'name.unique' => 'Ez az ország már létezik!',
            'name.required' => 'Az ország neve kötelező!'
        ]);

        $country = Country::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Ország létrehozva',
            'data' => $country
        ], 201);
    }

    public function show($id)
    {
        $country = Country::find($id);
        if (!$country) return response()->json(['message' => 'Ország nem található'], 404);

        return response()->json($country);
    }

    public function update(Request $request, $id)
    {
        $country = Country::find($id);
        if (!$country) return response()->json(['message' => 'Ország nem található'], 404);

        $request->validate([
            'name' => 'required|string|min:2|unique:countries,name,' . $id
        ]);

        $country->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Ország módosítva',
            'data' => $country
        ]);
    }

    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) return response()->json(['message' => 'Ország nem található'], 404);

        $country->delete();

        return response()->json(['message' => 'Ország törölve']);
    }
}
