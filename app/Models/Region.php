<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country_id'];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_regions');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
