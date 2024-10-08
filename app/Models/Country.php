<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_countries');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
