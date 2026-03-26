<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Location;
use App\Models\EventReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // --- FELHASZNÁLÓI OLDALAK ---

    // 1. FŐOLDAL
    public function index() {
        return view('welcome');
    }

    // 2. RÉSZLETEK OLDAL (Show) - ✅ JAVÍTVA
    public function show($id)
    {
        // Betöltjük az eseményt a várossal és a kommentekkel
        $event = Event::with(['location.city', 'comments.user'])->findOrFail($id);

        // Átadjuk a nézetnek az $event változót!
        return view('events.show', compact('event'));
    }

    // 3. SAJÁT ESEMÉNYEK (My Events) - ✅ JAVÍTVA
    public function myEvents()
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();

        // Lekérjük azokat, amikre a user reagált
        $reactedEventIds = EventReaction::where('user_id', $user->id)->pluck('event_id');
        
        $events = Event::whereIn('id', $reactedEventIds)
                       ->with('location')
                       ->orderBy('start_time', 'asc')
                       ->get();

        // FONTOS: Ellenőrizd, hogy a fájl a resources/views/events/ mappában van-e!
        return view('events.my_events', compact('events')); 
    }

    // --- API VÉGPONTOK (JS-hez) ---

    // 4. SZŰRŐ API
    public function filter(Request $request)
    {
        // Betöltjük a kapcsolatokat, hogy később országra és városra is lehessen szűrni
        $query = \App\Models\Event::with(['location.city.country']);

        // Keresőmező
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }
        
        // Ország szűrő
        if ($request->filled('country_id')) {
            $query->whereHas('location.city', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        // Város szűrő
        if ($request->filled('city_id')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // Klub / Helyszín szűrő
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // 🔴 ITT A VARÁZSLAT: Csak akkor szűrünk a stílusra, ha a szó NEM az, hogy "all"
        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('genre', $request->genre);
        }

        // 🔴 ITT IS: Csak akkor szűrünk korhatárra, ha a szó NEM az, hogy "all"
        if ($request->filled('age_limit') && $request->age_limit !== 'all') {
             $query->where('age_limit', '>=', (int)$request->age_limit);
        }
        
        // Dátum (Nap) szűrő
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        // Alapból csak a jövőbeli vagy mai bulikat mutassa, a régieket ne!
        $query->whereDate('start_time', '>=', now()->toDateString());

        // Időrendbe tesszük
        $events = $query->orderBy('start_time', 'asc')->get();
        
        return response()->json($events);
    }

    // 5. VISSZAJELZÉS (React)
    // ... (a többi függvény után)

    // ... korábbi kódok ...

    // ✅ REAKCIÓ GOMBOK MŰKÖDÉSE
    public function react(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = auth()->user();
        $type = $request->input('type', 'interested'); // Alapból 'interested'

        // Megnézzük, van-e már ilyen reakciója a felhasználónak
        // (Feltételezem, hogy van egy 'reactions' kapcsolata vagy táblája)
        $existingReaction = $event->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($existingReaction) {
            // --- HA MÁR BEJELÖLTE (Kivétel) ---
        
            // 1. Töröljük a reakciót
            $existingReaction->delete();
        
            // 2. CSÖKKENTJÜK a számot 1-gyel (a meglévő számból vonunk le)
            // Csak akkor vonunk le, ha nagyobb mint 0, nehogy negatív legyen
            if ($event->interested_count > 0) {
                $event->decrement('interested_count');
            }

        } else {
            // --- HA MÉG NEM JELÖLTE BE (Hozzáadás) ---

            // 1. Létrehozzuk a reakciót
            $event->reactions()->create([
                'user_id' => $user->id,
                'type' => $type
            ]);

            // 2. NÖVELJÜK a számot 1-gyel (a meglévő számhoz adunk)
            $event->increment('interested_count');
        }

        return back();
    }

    // ✅ KOMMENT MENTÉSE (Mert a route-od ide mutat: storeComment)
    public function storeComment(Request $request, $id)
    {
        $request->validate(['content' => 'required|max:500']);

        \App\Models\Comment::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'event_id' => $id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Komment elküldve!');
    }

    // --- ADMIN / SZERKESZTŐ FUNKCIÓK ---

    // 6. ÚJ ESEMÉNY (Create)
    public function create() {
        $locations = \App\Models\Location::with('city')->get();
        return view('events.create', compact('locations'));
    }

    // 7. MENTÉS (Store) - 🔴 ITT VOLT A HIBA, JAVÍTVA!
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'start_time' => 'required|date',
            'image' => 'nullable|image|max:4096', // Validálás
        ]);

        // KIVESSZÜK az 'image' mezőt, hogy ne kerüljön az adatbázisba nyersen
        $data = $request->except(['image', '_token']);

        // Fájl feltöltés kezelése
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $data['created_by'] = Auth::id();
        
        // Alapértelmezett értékek, ha üresek
        $data['genre'] = $request->genre ?? 'Egyéb';
        $data['age_limit'] = $request->age_limit ?? 0;

        Event::create($data);

        return redirect()->route('events.feed')->with('status', 'Esemény létrehozva!');
    }

    // 8. SZERKESZTÉS (Edit)
    public function edit(Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
        $cities = \App\Models\City::with('locations')->get();
        return view('events.edit', compact('event', 'cities'));
    }

    // 9. FRISSÍTÉS (Update)
    public function update(Request $request, Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
        
        $data = $request->except(['image', '_token', '_method']);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $data['image_url'] = '/storage/' . $path;
        }
        
        $event->update($data);
        return redirect()->route('events.show', ['id' => $event->id])->with('status', 'Frissítve!');
    }

    // 10. TÖRLÉS (Destroy)
    public function destroy(Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
        $event->delete();
        return redirect()->route('events.feed')->with('status', 'Törölve!');
    }
}