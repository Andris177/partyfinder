<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // ✅ Kedvenc toggle (hozzáad / töröl)
    public function toggle($id)
    {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => 'Event nem található'], 404);

        $user = auth()->user();

        if ($user->favoriteEvents()->where('event_id', $id)->exists()) {
            $user->favoriteEvents()->detach($id);

            // -1 local interested
            $event->decrement('local_interested_count');

            return response()->json([
                'message' => 'Törölve a kedvencekből',
                'interested' => $event->interested_total
            ]);
        }

        $user->favoriteEvents()->attach($id);

        // +1 local interested
        $event->increment('local_interested_count');

        return response()->json([
            'message' => 'Hozzáadva a kedvencekhez',
            'interested' => $event->interested_total
        ]);
    }

    // ✅ A felhasználó mentett eseményei (minden kapcsolattal)
    public function myFavorites()
    {
        $favorites = auth()->user()
            ->favoriteEvents()
            ->with('location.city.country')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'image_url' => $event->image_url,
                    'facebook_event_id' => $event->facebook_event_id,

                    // ✅ Reaction számok Facebook + App összeadva
                    'attending_total'  => $event->attending_count + $event->facebook_attending_count,
                    'interested_total' => $event->interested_count + $event->facebook_interested_count,

                    // ✅ Helyszín adatok
                    'location' => [
                        'name' => $event->location->name,
                        'address' => $event->location->address,
                        'lat' => $event->location->lat,
                        'lng' => $event->location->lng,
                        'city' => $event->location->city->name ?? null,
                        'country' => $event->location->country->name ?? null
                    ]
                ];
            });

        return response()->json($favorites);
    }
}
