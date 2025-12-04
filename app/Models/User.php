<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'username',
        'email',
        'password',
        'isactive',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'isactive' => 'boolean',
        'role' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username) && !empty($user->fullname)) {
                $user->username = strtolower(str_replace(' ', '', $user->fullname));
            }
        });
    }

    /**
     * Set the fullname attribute.
     */
    public function setFullnameAttribute($value)
    {
        $this->attributes['fullname'] = ucwords(strtolower($value));
    }

    /**
     * Set the username attribute.
     */
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower($value);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 1 || $this->role === 9;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 9;
    }

    /**
     * Check if user is regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 0;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    /**
     * Get the user's chat messages.
     */
    public function chatMessages()
    {
        return $this->hasMany(G5ChatMessage::class, 'user_id');
    }

    /**
     * Get the user's received messages.
     */
    public function receivedMessages()
    {
        return $this->hasMany(G5ChatMessage::class, 'receiver_id');
    }
}
