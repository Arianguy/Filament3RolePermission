<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disk extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_id',
        'disk_name',
        'capacity',
        'type',
        'interface',
    ];

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}
