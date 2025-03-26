<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'is_approved',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_approved'       => 'boolean',
        ];
    }

    /**
     * Get avatar URL or generate default from initials
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return url('storage/' . $this->avatar);
        }

        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if user has completed their profile
     *
     * @return bool
     */
    public function getHasCompletedProfileAttribute()
    {
        return !empty($this->phone) &&
            !empty($this->bio) &&
            !empty($this->address) &&
            !empty($this->city) &&
            !empty($this->state) &&
            !empty($this->zip_code) &&
            !empty($this->country);
    }

    /**
     * Check if user is super admin
     *
     * @return bool
     */
    public function getIsSuperAdminAttribute()
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is volunteer
     *
     * @return bool
     */
    public function getIsVolunteerAttribute()
    {
        return $this->hasRole('volunteer');
    }
}
