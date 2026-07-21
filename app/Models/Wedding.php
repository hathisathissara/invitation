<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wedding extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function guestGalleries()
    {
        return $this->hasMany(GuestGallery::class);
    }
}
