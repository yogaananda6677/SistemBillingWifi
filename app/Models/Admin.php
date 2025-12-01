<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


// opsional
    public function bukuKas()
    {
        return $this->hasMany(BukuKas::class, 'id_admin', 'id_admin');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_admin', 'id_admin');
    }

    public function setoran()
    {
        return $this->hasMany(Setoran::class, 'id_admin', 'id_admin');
    }
}
