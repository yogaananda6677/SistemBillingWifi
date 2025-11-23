<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Langganan extends Model
{
    protected $table = 'langganan';
    protected $primaryKey = 'id_langganan';

    protected $fillable = [
        'id_pelanggan',
        'id_paket',
        'tanggal_mulai',
        'status_langganan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }


    public static function statusLanggananOptions($status_pelanggan)
    {
        if($status_pelanggan == 'aktif')
            $status_langganan = 'aktif';
        elseif ($status_pelanggan == 'isolir')
            $status_langganan = 'isolir';
        elseif ($status_pelanggan == 'berhenti')
            $status_langganan = 'berhenti';
        else
            $status_langganan = 'aktif';

        return $status_langganan;
    }
}
