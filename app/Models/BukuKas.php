<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuKas extends Model
{
    protected $table = 'buku_kas';
    protected $primaryKey = 'id_buku_kas';

    protected $fillable = [
        'id_admin',
        'id_sales',
        'id_pembayaran',
        'id_setoran',
        'id_pengeluaran',
        'tipe',
        'sumber',
        'nominal',
    ];

    protected $casts = [
        'tanggal_update' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'id_sales', 'id_sales');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'id_pengeluaran', 'id_pengeluaran');
    }

    public function setoran()
    {
        return $this->belongsTo(Setoran::class, 'id_setoran', 'id_setoran');
    }
}
