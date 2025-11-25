<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;;
use App\Models\Sales;

class Area extends Model
{
    protected $table = 'area';
    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nama_area'
    ];

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_area', 'id_area');
    }

    public function sales()
    {
        return $this->hasMany(Sales::class, 'id_area', 'id_area');
    }
    
}
