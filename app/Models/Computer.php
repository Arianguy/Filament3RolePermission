<?php

namespace App\Models;

use App\Models\ComputerModel;
use App\Models\OperatingSystem;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Computer extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'pc_code',
        'name',
        'imei',
        'cost',
        'purchase_date',
        'warranty',
        'byod',
        'branch_id',
        'brand_id',
        'category_id',
        'model_id',
        'supplier_id',
        'cpu_id',
        'ram_id',
        'os_id',
        'vpn_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'byod' => 'boolean',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function computermodel()
    {
        return $this->belongsTo(ComputerModel::class, 'model_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cpu()
    {
        return $this->belongsTo(Cpu::class);
    }

    public function ram()
    {
        return $this->belongsTo(Ram::class);
    }

    public function os()
    {
        return $this->belongsTo(OperatingSystem::class, 'os_id');
    }

    public function vpn()
    {
        return $this->belongsTo(Vpn::class);
    }

    public function disks()
    {
        return $this->hasMany(Disk::class);
    }

    public function installations()
    {
        return $this->hasMany(Installation::class);
    }
}
