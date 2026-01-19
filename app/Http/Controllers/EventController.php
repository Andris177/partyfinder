<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FacebookEventService;
use App\Http\Resources\EventResource;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('location.city.country')->get();

        return response()->json(
        $events->map(function ($event) {
            return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'interested' => $event->interested_total,
                    'attending'  => $event->attending_total,
                    'location'   => $event->location,
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:150',
            'description' => 'nullable|string',
            'location_id' => 'required|exists:locations,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'nullable|date|after:start_time',
            'image_url' => 'nullable|url',
            'ticket_url' => 'nullable|url',
            'facebook_event_id' => 'nullable|string|max:100',
        ]);

        $event = Event::create([
            ...$validated,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Esemény létrehozva',
            'data' => $event
        ], 201);
    }

    public function show($id)
    {
        $event = Event::with('location.city.country')->findOrFail($id);

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'interested' => $event->interested_total,
            'attending'  => $event->attending_total,
            'location'   => $event->location,
        ]);
    }


    public function destroy($id)
    {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => 'Esemény nem található'], 404);

        if ($event->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Nincs jogosultságod törölni'], 403);
        }

        $event->delete();
        return response()->json(['message' => 'Esemény törölve']);
    }

    public function myEvents()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['message' => 'Nincs bejelentkezett felhasználó'], 401);
        }

        $events = Event::with('location')
            ->where('created_by', $userId)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($events);
    }

    public function filter(Request $request)
    {  
        $query = Event::with('location.city.country');

        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        if ($request->has('city_id')) {
            $query->whereHas('location.city', function ($q) use ($request) {
                $q->where('id', $request->city_id);
            });
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }     

        if ($request->has('country_id')) {
            $query->whereHas('location.city.country', function ($q) use ($request) {
                $q->where('id', $request->country_id);
            });
        }

        return response()->json($query->get());
    }

    public function addInterested($id)
    {
        $event = Event::findOrFail($id);
        $event->increment('interested_count');

        return response()->json([
            'interested' => $event->interested_total
        ]);
    }


    public function addAttending($id)
    {
        $event = Event::findOrFail($id);
        $event->increment('attending_count');

        return response()->json([
            'attending' => $event->attending_total
        ]);
    }


    public function uploadImage(Request $request, $id)
    {
        $event = Event::find($id);
        if (!$event) {
          return response()->json(['message' => 'Event nem található'], 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $path = $request->file('image')->store('event-images', 'public');

        $event->update([
          'image_url' => asset('storage/' . $path)
        ]);

        return response()->json([
            'message' => 'Kép feltöltve',
            'image_url' => $event->image_url
        ]);
    }

    public function refreshFacebookStats($id, FacebookEventService $fbService)
    {
        $event = Event::findOrFail($id);

        if (!$event->facebook_event_id) {
            return response()->json(['error' => 'Ehhez az eseményhez nincs Facebook ID'], 400);
        }

        $stats = $fbService->getEventStats($event->facebook_event_id);

        $event->update([
            'facebook_interested_count' => $stats['interested'],
            'facebook_attending_count'  => $stats['attending'],
        ]);

        return response()->json([
            'message'      => 'Facebook adatok frissítve',
            'fb_interested' => $event->facebook_interested_count,
            'fb_attending'  => $event->facebook_attending_count,
            'total_interested' => $event->interested_total,
            'total_attending'  => $event->attending_total,
        ]);
    }
}
