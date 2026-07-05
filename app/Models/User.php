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

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'share_token',
        'share_can_edit',
        'share_can_complete',
    ];

    protected $appends = ['profile_image_url', 'share_url'];

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
        ];
    }

    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class);
    }

    /**
     * Returns the URL for the user's profile image, falling back to a generated avatar.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image && file_exists(public_path('storage/' . $this->profile_image))) {
            return '/storage/' . $this->profile_image;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff&size=128';
    }

    public function getShareUrlAttribute()
    {
        return $this->share_token ? url('/shared/user/' . $this->share_token) : null;
    }
}
