<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. JAVÍTÁS: Megnézzük, létezik-e már az ország, ha nem, létrehozzuk (CODE NÉLKÜL!)
        $country = DB::table('countries')->where('name', 'Magyarország')->first();

        if ($country) {
            $countryId = $country->id;
        } else {
            $countryId = DB::table('countries')->insertGetId([
                'name' => 'Magyarország',
                // 'code' => 'HU',  <-- EZT KIVETTÜK, MERT NINCS ILYEN OSZLOP
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 2. Városok és Helyszínek
        
        // --- BUDAPEST ---
        // Itt is ellenőrizzük, hogy ne duplikáljuk a várost
        $bp = DB::table('cities')->where('name', 'Budapest')->first();
        if ($bp) {
            $bpId = $bp->id;
        } else {
            $bpId = DB::table('cities')->insertGetId([
                'name' => 'Budapest',
                'country_id' => $countryId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('locations')->insertOrIgnore([
            [
                'city_id' => $bpId,
                'name' => 'Budapest Park',
                'address' => 'Soroksári út 60.',
                'lat' => 47.4682,
                'lng' => 19.0734,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city_id' => $bpId,
                'name' => 'Akvárium Klub',
                'address' => 'Erzsébet tér 12.',
                'lat' => 47.4983,
                'lng' => 19.0543,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city_id' => $bpId,
                'name' => 'A38 Hajó',
                'address' => 'Petőfi híd, Budai hídfő',
                'lat' => 47.4768,
                'lng' => 19.0645,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // --- DEBRECEN ---
        $deb = DB::table('cities')->where('name', 'Debrecen')->first();
        if ($deb) {
            $debId = $deb->id;
        } else {
            $debId = DB::table('cities')->insertGetId([
                'name' => 'Debrecen',
                'country_id' => $countryId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('locations')->insertOrIgnore([
            [
                'city_id' => $debId,
                'name' => 'Roncsbár',
                'address' => 'Csapó u. 27.',
                'lat' => 47.5312,
                'lng' => 21.6264,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'city_id' => $debId,
                'name' => 'Víztorony',
                'address' => 'Pallagi út 7.',
                'lat' => 47.5530,
                'lng' => 21.6380,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // --- SZEGED ---
        $szeged = DB::table('cities')->where('name', 'Szeged')->first();
        if ($szeged) {
            $szegedId = $szeged->id;
        } else {
            $szegedId = DB::table('cities')->insertGetId([
                'name' => 'Szeged',
                'country_id' => $countryId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('locations')->insertOrIgnore([
            [
                'city_id' => $szegedId,
                'name' => 'JATE Klub',
                'address' => 'Dugonics tér 13.',
                'lat' => 46.2505,
                'lng' => 20.1450,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}