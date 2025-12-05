<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $primaryKey = 'id_tagihan';

    protected $fillable = [
        'id_langganan',
        'bulan',
        'tahun',
        'harga_dasar',
        'ppn_nominal',
        'total_tagihan',
        'status_tagihan',
        'jatuh_tempo',
        'dibuat_otomatis_bayar', // <---
    ];

    protected $casts = [
        'jatuh_tempo'          => 'datetime',
        'dibuat_otomatis_bayar'=> 'boolean', // <---
    ];
        public function langganan()
    {
        return $this->belongsTo(Langganan::class, 'id_langganan', 'id_langganan');
    }

    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class, 'id_tagihan', 'id_tagihan');
    }


    // biar yang lama tetap jalan (nama singular yang kamu pakai sebelumnya)
    public function paymentItem()
    {
        return $this->paymentItems();
    }
}
