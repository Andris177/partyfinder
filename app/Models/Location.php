<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    // Itt soroljuk fel, mit szabad menteni. 
    // Ez a biztonságosabb és szebb megoldás, mint a guarded = []
    protected $fillable = [
        'name',
        'city_id',
        'country_id',
        'address',
        'slug', // <--- EZT KERESTE A ROBOT!
        'lat',  // Koordináta
        'lng'   // Koordináta
    ];

    /**
     * Kapcsolat a Várossal (City)
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Kapcsolat az Országgal (Country)
     * (Opcionális, ha közvetlenül akarod elérni, de általában city->country-n keresztül szokás)
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Kapcsolat az Eseményekkel (Events)
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}