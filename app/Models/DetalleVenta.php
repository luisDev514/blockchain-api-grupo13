<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'producto_id',
        'quantity',
        'price',
        'total',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
