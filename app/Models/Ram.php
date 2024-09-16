<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ram extends Model
{
    use HasFactory;

    protected $fillable = ['capacity', 'type', 'speed'];

    // Relationships
    public function computers()
    {
        return $this->hasMany(Computer::class);
    }
}
