<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    // Bővítettük a listát, hogy a slug és a koordináták is menthetők legyenek!
    protected $fillable = [
        'country_id', 
        'name', 
        'slug', 
        'lat', 
        'lng'
    ];

    public function country() {
        return $this->belongsTo(Country::class);
    }

    // Ha a FacebookPage a "helyszíned", akkor érdemes ezt is felvenni:
    public function facebookPages() {
        return $this->hasMany(FacebookPage::class);
    }
    
    // Ezt megtarthatod, ha van Location modeled is:
    public function locations() {
        return $this->hasMany(Location::class);
    }
}