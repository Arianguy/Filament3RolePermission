<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'code',
        'name',
        'area',
        'region',
        'phone',
        'email',
        'status',
        'country_id',
        'region_id',
    ];
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    // Correctly define the relationship with Country
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }
}
