<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'password',
        'is_admin',
        'cash_balance',
        'kyc_status','sanctions_status','locked_at','lock_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_admin' => 'boolean',
            'cash_balance' => 'decimal:2',
            'locked_at' => 'datetime',
        ];
    }

    public function trades() { return $this->hasMany(Trade::class); }
    public function investments() { return $this->hasMany(Investment::class); }
    public function conversations() { return $this->hasMany(Conversation::class); }
    public function fundingRequests() { return $this->hasMany(FundingRequest::class); }
    public function kycProfile() { return $this->hasOne(KycProfile::class); }
    public function isLocked(): bool { return $this->locked_at !== null; }
}
