<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Langganan;
use App\Models\Ppn;

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

    public function langganan()
    {
        return $this->hasMany(Langganan::class, 'id_paket', 'id_paket');
    }

    /**
     * Hitung PPN Nominal berdasarkan harga_dasar dan presentase_ppn
     */
    public static function hitungPpnNominal($hargaDasar, $presentasePpn)
    {
        return $hargaDasar * $presentasePpn;  // presentasePpn sudah 0.xx
    }

    /**
     * Hitung Harga Total = harga_dasar + ppn_nominal
     */
    public static function hitungHargaTotal($hargaDasar, $ppnNominal)
    {
        return $hargaDasar + $ppnNominal;
    }

    /**
     * Update harga paket berdasarkan PPN terbaru
     */
    public function updateHargaDenganPpnBaru($presentasePpn)
    {
        $ppnNominal = self::hitungPpnNominal($this->harga_dasar, $presentasePpn);
        $hargaTotal = self::hitungHargaTotal($this->harga_dasar, $ppnNominal);

        $this->update([
            'ppn_nominal' => $ppnNominal,
            'harga_total' => $hargaTotal,
        ]);
    }
}
