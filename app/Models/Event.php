<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location_id',
        'start_time',
        'end_time',
        'image_url',
        'ticket_url',
        'facebook_event_id',
        'attending_count',
        'interested_count',
        'created_by'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function location() {
        return $this->belongsTo(Location::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function reactions()
    {
        return $this->hasMany(EventReaction::class);
    }

    public function getInterestedTotalAttribute()
    {
        return $this->facebook_interested_count + $this->local_interested_count;
    }

    public function getAttendingTotalAttribute()
    {
        return $this->facebook_attending_count + $this->local_attending_count;
    }

}
