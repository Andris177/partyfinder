<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $fillable = ['name'];

    public function events() {
        return $this->belongsToMany(Event::class, 'event_event_types');
    }
}
