<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventReaction;

class EventReactionController extends Controller
{
    public function react(Request $request, $eventId)
    {
        $request->validate([
            'type' => 'required|in:interested,going'
        ]);

        $user = $request->user();
        $event = Event::findOrFail($eventId);

        // Reakció mentése vagy frissítése
        EventReaction::updateOrCreate(
            ['user_id' => $user->id, 'event_id' => $event->id],
            ['type' => $request->type]
        );

        // Újraszámoljuk az appon belüli értékeket
        $event->interested_count = $event->reactions()->where('type', 'interested')->count();
        $event->attending_count  = $event->reactions()->where('type', 'going')->count();
        $event->save();

        return response()->json([
            'message' => 'Reaction saved',
            'interested' => $event->interested_total, // ✅ facebook + app egyben
            'attending'  => $event->attending_total,  // ✅ facebook + app egyben
        ]);
    }
}
