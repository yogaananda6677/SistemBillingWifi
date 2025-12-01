<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;
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

    // RELASI LAMA: area punya banyak sales via kolom id_area di tabel sales
    public function sales()
    {
        return $this->hasMany(Sales::class, 'id_area', 'id_area');
    }

    // RELASI BARU: area punya banyak sales via pivot area_sales
    public function salesMulti()
    {
        return $this->belongsToMany(
            Sales::class,
            'area_sales',
            'id_area',
            'id_sales'
        );
    }
}
