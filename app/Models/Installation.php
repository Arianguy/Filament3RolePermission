<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_id',
        'software_id',
        'license_id',
    ];

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    public function software()
    {
        return $this->belongsTo(Software::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}