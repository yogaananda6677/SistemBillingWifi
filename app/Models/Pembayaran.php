<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pelanggan',
        'id_sales',
        'id_user', 
        'tanggal_bayar',
        'nominal',
        'no_pembayaran',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }


    public function sales()
    {
        return $this->belongsTo(Sales::class, 'id_sales', 'id_sales');
    }

    // detail pembayaran (tagihan-tagihan yang dibayar)
    public function items()
    {
        return $this->hasMany(PaymentItem::class, 'id_pembayaran', 'id_pembayaran');
    }

    // hubungan ke buku kas (pemasukan)
    public function bukuKas()
    {
        return $this->hasOne(BukuKas::class, 'id_pembayaran', 'id_pembayaran');
    }
        public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class, 'id_pembayaran', 'id_pembayaran');
    }
}
