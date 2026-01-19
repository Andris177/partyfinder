<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EventEventType extends Model
{
    protected $table = 'event_event_types';
    protected $fillable = ['event_id','type_id'];
    public $timestamps = false;
}
