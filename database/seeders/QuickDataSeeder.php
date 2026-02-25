<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Location;
use App\Models\Event;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class QuickDataSeeder extends Seeder
{
    public function run()
    {
        // 0. Ország létrehozása (JAVÍTVA: Csak névre keresünk, kódra nem)
        $hungary = Country::firstOrCreate(
            ['name' => 'Hungary']
        );

        // 1. Admin User
        if (!User::where('email', 'admin@partify.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@partify.com',
                'password' => Hash::make('password'),
            ]);
        }
        $admin = User::first();

        // 2. Városok
        $bp = City::firstOrCreate(['name' => 'Budapest'], ['country_id' => $hungary->id]);
        $deb = City::firstOrCreate(['name' => 'Debrecen'], ['country_id' => $hungary->id]);

        // 3. Helyszínek
        $akvarium = Location::firstOrCreate(['name' => 'Akvárium Klub'], [
            'city_id' => $bp->id,
            'country_id' => $hungary->id,
            'address' => 'Erzsébet tér 12.',
            'lat' => '47.4983', 
            'lng' => '19.0522',
        ]);

        $morrisons = Location::firstOrCreate(['name' => "Morrison's 2"], [
            'city_id' => $bp->id,
            'country_id' => $hungary->id,
            'address' => 'Szent István krt. 11.',
            'lat' => '47.5126', 
            'lng' => '19.0508',
        ]);

        $park = Location::firstOrCreate(['name' => 'Budapest Park'], [
            'city_id' => $bp->id,
            'country_id' => $hungary->id,
            'address' => 'Soroksári út 60.',
            'lat' => '47.4682', 
            'lng' => '19.0734',
        ]);
        
        $waterTower = Location::firstOrCreate(['name' => 'Nagyerdei Víztorony'], [
            'city_id' => $deb->id,
            'country_id' => $hungary->id,
            'address' => 'Pallagi út 7.',
            'lat' => '47.5516',
            'lng' => '21.6235',
        ]);

        // 4. Bulik létrehozása
        
        // Buli 1: Techno
        Event::firstOrCreate(['title' => 'Neon Nights - Techno Marathon'], [
            'description' => "A város legnagyobb techno bulija! \nGyere és bulizz hajnalig a legjobb DJ-k társaságában. \n\nLineup:\n- DJ Dark\n- CyberPunk\n- BassDrop",
            'start_time' => now()->setTime(22, 0),
            'end_time' => now()->addDay()->setTime(6, 0),
            'location_id' => $akvarium->id,
            'created_by' => $admin->id,
            'image_url' => 'https://images.unsplash.com/photo-1574155376612-bfa5f1d0d505?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80',
            'facebook_interested_count' => 120,
            'facebook_attending_count' => 450,
        ]);

        // Buli 2: Retro
        Event::firstOrCreate(['title' => 'Retro Láz - 90s & 2000s'], [
            'description' => 'Emlékszel még a Spice Girlsre? Az évtized legjobb slágerei egész éjszakán át!',
            'start_time' => now()->addDay()->setTime(21, 0),
            'location_id' => $morrisons->id,
            'created_by' => $admin->id,
            'image_url' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a67?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80',
            'facebook_interested_count' => 50,
            'facebook_attending_count' => 200,
        ]);

        // Buli 3: Koncert
        Event::firstOrCreate(['title' => 'Summer Vibes Open Air'], [
            'description' => 'Szabadtéri szezonnyitó a Parkban!',
            'start_time' => now()->addDays(5)->setTime(19, 0),
            'location_id' => $park->id,
            'created_by' => $admin->id,
            'image_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80',
            'facebook_interested_count' => 800,
            'facebook_attending_count' => 1200,
        ]);
        
        // Buli 4: Debrecen
        Event::firstOrCreate(['title' => 'Campus Warmup Party'], [
            'description' => 'Melegítsünk be a félévre egy hatalmas bulival a Víztoronyban!',
            'start_time' => now()->addDays(2)->setTime(20, 0),
            'location_id' => $waterTower->id,
            'created_by' => $admin->id,
            'image_url' => 'https://images.unsplash.com/photo-1514525253440-b393452e3383?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80',
            'facebook_interested_count' => 300,
            'facebook_attending_count' => 150,
        ]);
    }
}