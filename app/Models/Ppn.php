<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppn extends Model
{
    protected $table = 'setting_ppn';

    protected $primaryKey = 'id_setting';

    protected $fillable = [
        'presentase_ppn',
    ];


    public static function convertPPN($value)
    {
        return $value / 100;
    }

}
