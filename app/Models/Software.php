<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_free'];

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function installations()
    {
        return $this->hasMany(Installation::class);
    }
}
