<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operacion extends Model
{
    use HasFactory;
    protected $fillable = [	
        'nombre',
    ];
    protected $table = "operacion";
    protected $primaryKey="operacion_id";

}
