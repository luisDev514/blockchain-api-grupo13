<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario_Rol extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'rol_id',
        'user_id'
    ];

    protected $table = "usuario_rol";
    protected $primaryKey = "usuario_rol_id";

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
