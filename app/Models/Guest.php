<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'whatsapp_number',
        'category',
        'side',
        'seats_reserved',
        'invite_token',
        'is_opened',
        'opened_at',
        'rsvp_status',
        'guest_note',
        'is_sent',
        'sent_at',
    ];

    // Guest කෙනෙක් අයිති වෙන්නේ එක Wedding එකකටයි
    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
