<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventReaction extends Model
{
    use HasFactory;

    // Engedélyezzük ezeknek a mezőknek a kitöltését
    protected $fillable = ['user_id', 'event_id', 'type'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}