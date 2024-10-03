<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id',
        'total',
        'habilitado',
    ];

    public function detalle()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
