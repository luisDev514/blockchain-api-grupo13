<?php

namespace App\enums;

enum OperacionesBitacora: string
{
    case CerrarSesion = 'Cerrar sesión';
    case Deshabilitar = 'Deshabilitar';
    case Eliminar = 'Eliminar';
    case Habilitar = 'Habilitar';
    case IniciarSesion = 'Inicio sesión';
    case Listar = 'Listar';
    case LoginFallido = 'Login Fallido';
    case Modificar = 'Modificar';
    case Registrar = 'Registrar';
    case Registrar_en_bloque = 'Registrar en bloque';
}


$casts = [
    'operacionBitacora' => OperacionesBitacora::class,
];
