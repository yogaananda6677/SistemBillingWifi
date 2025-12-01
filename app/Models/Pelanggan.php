<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales;
use App\Models\Langganan;
use App\Models\Area;
use Carbon\Carbon;

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

    protected $casts = [
    'tanggal_registrasi' => 'datetime',
    ];
    protected $appends = ['status_pelanggan_efektif'];

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
        // ===============================
    // STATUS PELANGGAN EFEKTIF
    // "baru" hanya untuk bulan & tahun yang sama dengan tanggal_registrasi
    // setelah ganti bulan → dianggap "aktif"
    // ===============================
    public function getStatusPelangganEfektifAttribute()
    {
        // Jika bukan 'baru', kembalikan status asli
        if ($this->status_pelanggan !== 'baru') {
            return $this->status_pelanggan;
        }

        if (!$this->tanggal_registrasi) {
            return $this->status_pelanggan;
        }

        $bulanDaftar   = $this->tanggal_registrasi->format('Y-m');
        $bulanSekarang = now()->format('Y-m');

        if ($bulanDaftar === $bulanSekarang) {
            return 'baru';
        }

        // Kalau status di DB 'baru' tapi sudah beda bulan → dianggap aktif
        return 'aktif';
    }

}
