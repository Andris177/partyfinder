<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'image_url' => $this->image_url,
            'ticket_url' => $this->ticket_url,
            'facebook_event_id' => $this->facebook_event_id,
            'attending' => $this->attending_count,
            'interested' => $this->interested_count,
            'location' => [
                'id' => $this->location->id,
                'name' => $this->location->name,
                'address' => $this->location->address,
                'city' => $this->location->city->name,
                'country' => $this->location->city->country->name ?? null,
            ]
        ];
    }
}
