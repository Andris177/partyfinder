<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
    'name',
    'city_id',
    'country_id',
    'address',
    'latitude',
    'longitude',
    'facebook_page'
    ];

    public function city() {
        return $this->belongsTo(City::class);
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}
