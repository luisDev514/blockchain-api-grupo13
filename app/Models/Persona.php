<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $primaryKey = "persona_id";
    protected $fillable = [
        'apellido_materno',
        'apellido_paterno',
        'ci_extension',
        'ci_origen',
        'ci',
        'correo',
        'fecha_nacimiento',
        'foto',
        'habilitado',
        'nombre_completo',
        'nombre',
        'telefono',
    ];
    protected $table = "persona";

    public function user()
    {
        return $this->hasOne(User::class, 'persona_id');
    }
}
