<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComputerModel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'brand_id'];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function computers()
    {
        return $this->hasMany(Computer::class, 'model_id');
    }
}
