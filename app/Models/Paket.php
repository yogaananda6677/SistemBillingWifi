<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'paket';
    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'nama_paket',
        'kecepatan',
        'harga_dasar',
        'ppn_nominal',
        'harga_total'
    ];

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_paket');
    }
}
