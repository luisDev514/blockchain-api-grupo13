<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
  use HasFactory;
  protected $fillable = [
    'nombre',
    'habilitado',
  ];
  protected $table = "rol";

  protected $primaryKey = "rol_id";

  protected $hidden = ['pivot'];

  public function accesos()
  {
    return $this->hasMany(Rol_Acceso::class, 'rol_id');
  }

  public function modulos()
  {
    return $this->belongsToMany(Modulo::class, Rol_Acceso::class, 'rol_id', 'modulo');
  }

  public function users()
  {
    return $this->belongsToMany(User::class, Usuario_Rol::class, 'rol_id', 'user_id');
  }

  public function aplicacion()
  {
    return $this->belongsTo(Aplicacion::class, 'aplicacion_id');
  }
}
