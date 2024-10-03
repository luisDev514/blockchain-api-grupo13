<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol_Acceso extends Model
{
    use HasFactory;
    protected $fillable = [
        'rol_id',
        'modulo',
    ];
    protected $table = "rol_acceso";
    protected $primaryKey = "acceso_id";

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo');
    }
}
