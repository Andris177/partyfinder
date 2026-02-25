<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\City;
use App\Models\Country; // <--- EZT NE FELEJTSD EL!
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacebookPageController extends Controller
{
    public function index()
    {
        if (auth()->id() !== 1) { abort(403); } 
        $pages = FacebookPage::with('city.country')->latest()->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        if (auth()->id() !== 1) { abort(403); }
        
        $cities = City::all();
        $countries = Country::all(); // <--- Lekérjük az országokat is a listához
        
        return view('admin.pages.create', compact('cities', 'countries'));
    }

    public function store(Request $request)
    {
        if (auth()->id() !== 1) { abort(403); }

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|unique:facebook_pages,url',
            'city_name' => 'required|string|max:255',
            'country_name' => 'required|string|max:255',
        ]);

        // 1. ORSZÁG KEZELÉSE (Egyszerűsítve: csak a nevet mentjük)
        $country = Country::firstOrCreate(
            ['name' => $request->country_name] 
            // A második tömböt töröltük, mert nincs 'code' oszlopod.
        );

        // 2. VÁROS KEZELÉSE (Összekötjük az országgal)
        $city = City::firstOrCreate(
            ['name' => $request->city_name], 
            [
                'slug' => Str::slug($request->city_name),
                'country_id' => $country->id, // <--- FONTOS: Ez köti össze őket!
                'lat' => 0,
                'lng' => 0 
            ]
        );

        // 3. KLUB MENTÉSE
        FacebookPage::create([
            'name' => $request->name,
            'url' => $request->url,
            'city_id' => $city->id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Siker! Helyszín hozzáadva. (Város: ' . $city->name . ', Ország: ' . $country->name . ')');
    }

    public function destroy($id)
    {
        if (auth()->id() !== 1) { abort(403); }
        FacebookPage::destroy($id);
        return back()->with('success', 'Helyszín törölve.');
    }
}