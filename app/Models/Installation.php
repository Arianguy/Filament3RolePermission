<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use App\Observers\InstallationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Installation extends Model
{
    use HasFactory;
    protected $fillable = [
        'computer_id',
        'license_id',
        'software_id',
        'key',
        'userid',
        'password',
        'assigned_at',
    ];

    // Cast the 'assigned_at' to a timestamp
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Only keep the debug logging
        static::created(function ($installation) {
            Log::info('Installation Created Event Fired', [
                'installation_id' => $installation->id,
                'license_id' => $installation->license_id
            ]);
        });
    }


    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    // Define the relationship to the License model
    public function license()
    {
        return $this->belongsTo(License::class);
    }

    // Define the relationship to the Software model (if applicable)
    public function software()
    {
        return $this->belongsTo(Software::class);
    }
}
