<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Langganan;
use App\Models\PaymentItem;

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
    ];

    public function langganan()
    {
        return $this->belongsTo(Langganan::class, 'id_langganan', 'id_langganan');
    }

    public function paymentItem()
    {
        return $this->hasMany(PaymentItem::class, 'id_tagihan');
    }
}
