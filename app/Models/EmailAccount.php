<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $fillable = [
        'name',
        'email_address',
        'status',
        'branch_id',
        'main_password',
        'pc_outlook_password',
        'ios_outlook_password',
        'android_outlook_password',
        'other_password',
        'recovery_email',
        'recovery_mobile',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function computers()
    {
        return $this->belongsToMany(Computer::class, 'computer_email_account')
            ->using(ComputerEmailAccount::class)
            ->withPivot('configured_at'); // Add any other fields
    }
}
