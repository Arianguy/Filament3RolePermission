<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpu extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'core', 'speed', 'company'];

    // Relationships
    public function computers()
    {
        return $this->hasMany(Computer::class);
    }
}
