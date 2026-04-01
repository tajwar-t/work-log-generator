<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'avatar', 'job_title', 'timezone', 'bio',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            $filename = basename($this->avatar);
            if (file_exists(public_path('avatars/' . $filename))) {
                // Always use /public/avatars/ — confirmed working on this Hostinger setup
                return avatarUrl($filename);
            }
        }

        // Initials avatar — unique color per user from 12-color palette
        $palette = [
            '6c8fff', // blue
            '43c678', // green
            'f5a623', // amber
            'b47eff', // purple
            'ff5f5f', // red
            '22d3ee', // cyan
            'fb7185', // rose
            'a3e635', // lime
            'f97316', // orange
            'e879f9', // fuchsia
            '2dd4bf', // teal
            'facc15', // yellow
            '60a5fa', // light blue
            '34d399', // emerald
            'fbbf24', // gold
            'c084fc', // violet
            'f87171', // light red
            '67e8f9', // sky
            'fda4af', // pink
            'bef264', // light lime
            'fb923c', // light orange
            'd946ef', // magenta
            '5eead4', // aqua
            'fde047', // light yellow
            '818cf8', // indigo
        ];
        $color = $palette[($this->id - 1) % count($palette)];
        $name  = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background={$color}&color=fff&size=128&bold=true&font-size=0.4";
    }
}