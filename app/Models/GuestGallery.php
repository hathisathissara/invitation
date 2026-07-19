<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestGallery extends Model
{
    use HasFactory;

    // table name එක plural නැති නිසා explicitly define කරමු
    protected $table = 'guest_galleries';

    protected $fillable = [
        'wedding_id',
        'guest_name',
        'image_path',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
