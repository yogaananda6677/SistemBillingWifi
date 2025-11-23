<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $table = 'sales';
    protected $primaryKey = 'id_sales';

    protected $fillable = [
        'id_area',
        'user_id',
        'komisi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_sales');
    }
}
