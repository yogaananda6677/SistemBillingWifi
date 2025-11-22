<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;
use App\Models\User;

class Sales extends Model
{
    protected $primaryKey = 'id_sales';

    protected $fillable = [
        'user_id',
        'komisi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_sales', 'id_sales');
    }
}
