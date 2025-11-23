<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = true; // created_at / updated_at exist

    protected $dates = ['tanggal_pengajuan','tanggal_approve'];

    // relation to sales (sales.id_sales)
    public function sales()
    {
        return $this->belongsTo(\App\Models\Sales::class, 'id_sales', 'id_sales');
    }

    // approvedBy: kita asumsikan admins have users; pengeluaran.id_admin points to admins.id_admin
    public function approvedBy()
    {
        // admins -> user_id -> users
        return $this->belongsToThrough(\App\Models\User::class, \App\Models\Admin::class, 'id_admin', 'id', 'id_admin', 'user_id')
            ?? null;
        // Note: if you don't have belongsToThrough package, simpler: load admin then user in controller
    }
}
