<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
     use HasFactory;

    protected $fillable = [
        'wedding_id',
        'event_name',
        'event_date_time',
        'location_name',
        'google_map_link',
    ];

    // Event එකක් අයිති වෙන්නේ එක Wedding එකකටයි
    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
