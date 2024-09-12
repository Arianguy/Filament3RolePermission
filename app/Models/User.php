<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Correct import for BelongsToMany
use App\Models\Region;
use App\Models\Branch;
use App\Models\Country; // Ensure the Country model is correctly imported

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Define the many-to-many relationship between User and Region.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'user_regions');
    }

    /**
     * Define the many-to-many relationship between User and Branch.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches');
    }

    /**
     * Define the many-to-many relationship between User and Country.
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'user_countries');
    }
}
