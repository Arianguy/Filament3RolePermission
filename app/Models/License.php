<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'software_id',
        'license_type',
        'valid_from',
        'valid_to',
        'license_key',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function software()
    {
        return $this->belongsTo(Software::class);
    }

    public function installations()
    {
        return $this->hasMany(Installation::class);
    }
}
