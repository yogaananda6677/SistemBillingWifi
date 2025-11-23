<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Langganan;

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
        return $this->hasMany(Langganan::class, 'id_paket');
    }


    // public static function formatAngka($number)
    // {
    //     return number_format($number, 0, ',', '.');
    // }

    public static function hitungPPN($hargaDasar, $ppnNominal)
    {
        $hitung = ($ppnNominal / 100) * $hargaDasar;

        return $hitung;
    }

    public static function hitungHargaTotal($hargaDasar, $ppnNominal)
    {

        $hitungTotal = $hargaDasar + $ppnNominal;
        return $hitungTotal;
    }

}
