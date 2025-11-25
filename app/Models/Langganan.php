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
        'tanggal_isolir',
        'tanggal_berhenti',
    ];

    // Cast tanggal jadi instance Carbon (otomatis)
    protected $casts = [
        'tanggal_mulai'     => 'date',
        'tanggal_isolir'    => 'date',
        'tanggal_berhenti'  => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'id_langganan', 'id_langganan');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }

    /**
     * Mapping status_pelanggan -> status_langganan
     */
    public static function statusLanggananOptions($status_pelanggan)
    {
        if ($status_pelanggan == 'aktif') {
            $status_langganan = 'aktif';
        } elseif ($status_pelanggan == 'isolir') {
            $status_langganan = 'isolir';
        } elseif ($status_pelanggan == 'berhenti') {
            $status_langganan = 'berhenti';
        } else {
            // misal status_pelanggan = 'baru' atau lainnya
            $status_langganan = 'aktif';
        }

        return $status_langganan;
    }

    /**
     * Accessor: tanggal_aktif
     *
     * - aktif / baru      -> tanggal_mulai
     * - isolir            -> tanggal_isolir (fallback ke tanggal_mulai)
     * - berhenti          -> tanggal_berhenti (fallback ke tanggal_mulai)
     */
    public function getTanggalAktifAttribute()
    {
        if ($this->status_langganan === 'isolir' && $this->tanggal_isolir) {
            return $this->tanggal_isolir;
        }

        if ($this->status_langganan === 'berhenti' && $this->tanggal_berhenti) {
            return $this->tanggal_berhenti;
        }

        // default aktif / baru / lainnya
        return $this->tanggal_mulai;
    }
}
