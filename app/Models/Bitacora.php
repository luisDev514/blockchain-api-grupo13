<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'tabla',
        'tabla_identificador',
        'fecha',
        'codigo_app',
        'operacion',
    ];
    protected $table = "bitacora";

    protected $primaryKey = "bitacora_id";

    public function personaUser()
    {
        return $this->hasOneThrough(Persona::class, User::class, 'persona_id', 'user_id');
    }
}
