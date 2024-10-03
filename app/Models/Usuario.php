<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Model
{
    use HasFactory;
    protected $fillable = [
        'persona_id',
        'aplicacion_id',
        'habilitado',
        'username',
    ];
    protected $table = "usuario";

    protected $primaryKey = "usuario_id";

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function roles()
    {
        // return $this->hasManyThrough(Modulo::class, Rol_Acceso::class, 'modulo', 'rol_id');
        return $this->hasManyThrough(Rol::class, Usuario_Rol::class, 'rol_id', '');
    }
}
