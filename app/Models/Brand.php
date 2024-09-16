<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'website'];

    // Relationships
    public function computers()
    {
        return $this->hasMany(Computer::class);
    }

    public function computerModels()
    {
        return $this->hasMany(ComputerModel::class);
    }
}
