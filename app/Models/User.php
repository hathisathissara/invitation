<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    protected $guarded = [];
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'payment_slip',
        'package',
        'has_guest_gallery',
        'upgrade_slip',
        'pending_upgrade_plan',
        'refund_status',
        'refund_requested_at',
        'refund_bank_details',
        'refund_reason',
        'deletion_notice_sent_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'refund_requested_at' => 'datetime',
        'deletion_notice_sent_at' => 'datetime',
    ];

    public function wedding()
    {
        return $this->hasOne(Wedding::class);
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
