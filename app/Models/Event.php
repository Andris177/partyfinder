<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_event_id', 'title', 'description', 'start_time', 'end_time',
        'location_id', 'facebook_url', 'ticket_url', 'image_url',
        'interested_count', 'going_count', 'created_by', 'genre', 'age_limit'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // ✅ JAVÍTVA: Csak a két új, profi angol dátumot küldjük le a weblapnak!
    protected $appends = ['formatted_day', 'formatted_month'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // ✅ EZT KERESTE A RENDSZER (RelationNotFound hiba javítása)
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    // ✅ EZT HASZNÁLJUK A GOMBOKHOZ (users tábla helyett)
    public function reactions()
    {
        return $this->hasMany(EventReaction::class);
    }

    // Segédfüggvény: A jelenlegi felhasználó reakciója
    public function getAuthReactionAttribute()
    {
        if (!Auth::check()) return null;
        $reaction = $this->reactions()->where('user_id', Auth::id())->first();
        return $reaction ? $reaction->type : null;
    }

    // 1. A pontos nap kiszedése (Biztonságos Carbon konverzióval!)
    public function getFormattedDayAttribute()
    {
        if (!$this->start_time) return '';
        // Szövegből Dátumot csinálunk, majd formázzuk
        return Carbon::parse($this->start_time)->format('d');
    }

    // 2. Az Angol hónap rövidítés (Biztonságos Carbon konverzióval!)
    public function getFormattedMonthAttribute()
    {
        if (!$this->start_time) return '';
        
        $months = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR', 
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AUG', 
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC'
        ];
        
        return $months[Carbon::parse($this->start_time)->format('n')];
    }
}