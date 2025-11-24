<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = true;

    protected $dates = ['tanggal_pengajuan','tanggal_approve'];

    // Sales yang mengajukan
    public function sales()
    {
        return $this->belongsTo(\App\Models\Sales::class, 'id_sales', 'id_sales');
    }

    // Admin yang approve/reject
    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'id_admin', 'id_admin');
    }

    // Nama user admin
    public function adminUser()
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            \App\Models\Admin::class,
            'id_admin',   // di admins
            'id',         // di users
            'id_admin',   // di pengeluaran
            'user_id'     // di admins
        );
    }
    
}
