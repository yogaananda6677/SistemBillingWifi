<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales;
use App\Models\Langganan;
use App\Models\Area;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';

    protected $fillable = [
        'id_sales',
        'id_area',
        'nama',
        'nik',
        'alamat',
        'nomor_hp',
        'ip_address',
        'status_pelanggan',
        'tanggal_registrasi',
    ];

    // protected $casts = [
    // 'tanggal_registrasi' => 'datetime',
    // ];


    public function sales()
    {
        return $this->belongsTo(Sales::class, 'id_sales');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }


    public function langganan()
    {
        return $this->hasMany(Langganan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
