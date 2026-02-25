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
        'interested_count', 'going_count', 'created_by', 'genre',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Ez küldi a fix dátumokat a frontendnek
    protected $appends = ['fix_day', 'fix_month_name'];

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

    // Dátum javítások
    public function getFixDayAttribute()
    {
        return Carbon::parse($this->start_time)->format('d');
    }

    public function getFixMonthNameAttribute()
    {
        $months = [1=>'JAN', 2=>'FEB', 3=>'MÁR', 4=>'ÁPR', 5=>'MÁJ', 6=>'JÚN', 7=>'JÚL', 8=>'AUG', 9=>'SZEP', 10=>'OKT', 11=>'NOV', 12=>'DEC'];
        $num = Carbon::parse($this->start_time)->format('n');
        return $months[$num] ?? '';
    }
}