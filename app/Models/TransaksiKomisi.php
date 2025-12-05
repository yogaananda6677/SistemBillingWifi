<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKomisi extends Model
{
    protected $table = 'transaksi_komisi';
    protected $primaryKey = 'id_komisi';

    protected $fillable = [
        'id_pembayaran',
        'id_sales',
        'nominal_komisi',
        'jumlah_komisi',
    ];

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'id_sales', 'id_sales');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }
}
