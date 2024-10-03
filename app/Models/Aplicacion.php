<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aplicacion extends Model
{
  use HasFactory;

  protected $fillable = [
    'codigo',
    'nombre',
    'titulo',
    'icono',
    'url',
    'descripcion',
    'area',
    'base_datos',
    'ip_servidor',
    'version',
    'habilitado'
  ];
  protected $table = "aplicacion";
  protected $primaryKey = "aplicacion_id";

  public function rol_asignacion()
  {
    return $this->hasMany(Rol_Asignacion::class, 'aplicacion_id');
  }

  public function roles()
  {
    return $this->hasMany(Rol::class, 'aplicacion_id');
  }

  public function modulos()
  {
    return $this->hasMany(Modulo::class, 'aplicacion_id');
  }
}
