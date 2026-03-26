<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url', 'city_id', 'is_active', 'last_scraped_at', 'events_url', 'scraper_driver'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}