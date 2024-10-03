<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadNegocio extends Model
{
  use HasFactory;
  protected $fillable = [
    'nombre',
    'codigo',
    'empresa_id',
    'division_id',

  ];
  protected $table = "unidad_negocio";

  public function empresa()
  {
    return $this->belongsTo(Empresa::class, 'empresa_id');
  }

  public function division()
  {
    return $this->belongsTo(Division::class, 'division_id');
  }
}
