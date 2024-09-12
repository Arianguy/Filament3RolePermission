<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
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
    ];
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
