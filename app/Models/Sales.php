<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $table = 'sales';
    protected $primaryKey = 'id_sales';

    protected $fillable = [
        'id_area',
        'user_id',
        'komisi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELASI LAMA: satu sales punya satu area (single)
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    // RELASI BARU: sales bisa punya banyak area (multi)
    public function areas()
    {
        return $this->belongsToMany(
            Area::class,
            'area_sales', // nama tabel pivot
            'id_sales',   // fk pivot ke sales
            'id_area'     // fk pivot ke area
        );
    }

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_sales');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_sales');
    }
        public function transaksiKomisi()
    {
        return $this->hasMany(\App\Models\TransaksiKomisi::class, 'id_sales', 'id_sales');
    }
}

