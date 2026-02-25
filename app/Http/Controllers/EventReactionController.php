<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventReaction;
use Illuminate\Support\Facades\Auth;

class EventReactionController extends Controller
{
    public function react(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:interested,going'
        ]);

        $user = Auth::user();
        $event = Event::findOrFail($id);

        // 1. Megnézzük, van-e már reakciója ennek a usernek
        $existingReaction = EventReaction::where('user_id', $user->id)
                                         ->where('event_id', $event->id)
                                         ->first();

        $myCurrentStatus = null;

        if ($existingReaction) {
            // HA MÁR VAN REAKCIÓ:
            if ($existingReaction->type === $request->type) {
                // A. Ha ugyanarra nyomott rá -> TÖRLÉS (Toggle OFF)
                $existingReaction->delete();
                $myCurrentStatus = null; 
            } else {
                // B. Ha a másikra nyomott -> MÓDOSÍTÁS (Switch)
                $existingReaction->update(['type' => $request->type]);
                $myCurrentStatus = $request->type;
            }
        } else {
            // HA MÉG NINCS REAKCIÓ -> LÉTREHOZÁS
            EventReaction::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'type' => $request->type
            ]);
            $myCurrentStatus = $request->type;
        }

        // 2. Statisztikák frissítése
        $localInterested = $event->reactions()->where('type', 'interested')->count();
        $localAttending = $event->reactions()->where('type', 'going')->count();

        $event->update([
            'interested_count' => $localInterested,
            'attending_count' => $localAttending
        ]);

        return response()->json([
            'status' => 'success',
            'my_reaction' => $myCurrentStatus,
            'interested' => $localInterested + ($event->facebook_interested_count ?? 0),
            'attending'  => $localAttending + ($event->facebook_attending_count ?? 0),
        ]);
    }
}