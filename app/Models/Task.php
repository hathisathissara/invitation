<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // සේව් කරන්න අවසර දෙන ෆීල්ඩ්ස් (Fillable Properties) මෙතන දාන්න
    protected $fillable = [
        'wedding_id',
        'task_name',
        'is_completed',
    ];

    // Task එකක් අයිති වෙන්නේ එක Wedding එකකටයි (Relationship)
    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
