<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run()
    {
        Event::create([
            'title' => 'Teszt Party',
            'description' => 'Seederrel létrehozott esemény',
            'location_id' => 1,
            'start_time' => now(),
            'facebook_event_id' => 'FB12345',
            'attending_count' => 100,
            'interested_count' => 250
        ]);
    }
}
