<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingSystem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];

    // Relationships
    public function computers()
    {
        return $this->belongsTo(OperatingSystem::class, 'os_id');
    }
}
