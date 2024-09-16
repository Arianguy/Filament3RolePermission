<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vpn extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'pass'];

    // Relationships
    public function computers()
    {
        return $this->hasMany(Computer::class);
    }
}
