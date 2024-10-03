<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    protected $fillable = [
        'aplicacion_id',
        'modulo_padre',
        'url',
        'nombre',
        'icono',
        'menu',
        'habilitado'
    ];
    protected $table = "modulo";
    protected $hidden = ['pivot'];
    protected $primaryKey = "modulo_id";

    public function acceso()
    {
        return $this->hasMany(Rol_Acceso::class, 'modulo');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, Rol_Acceso::class, 'rol_id', 'modulo');
    }

    public function aplicacion()
    {
        return $this->belongsTo(Aplicacion::class, 'aplicacion_id');
    }
}
