<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
        CountrySeeder::class,
        // LocationSeeder::class, // Ha van külön, ha nincs, akkor itt hozzunk létre egyet:
        ]);

        // Gyorsan gyártunk egy várost és helyszínt, hogy az EventSeeder ne szálljon el
        $country = \App\Models\Country::first();
        $city = \App\Models\City::create(['name' => 'Budapest', 'country_id' => $country->id]);
        \App\Models\Location::create([
            'name' => 'Akvárium Klub',
            'address' => 'Erzsébet tér 12.',
            'city_id' => $city->id,
            'country_id' => $country->id,
            'lat' => 47.498,
            'lng' => 19.055
        ]);

    // Most már mehet az EventSeeder
    $this->call([
        EventSeeder::class,
    ]);

        // ✅ Csak akkor szúrja be, ha még nem létezik
        if (!DB::table('users')->where('email', 'test@example.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->call(LocationSeeder::class);
    }
}
