<?php

namespace App\enums;

enum TablasBitacora: string
{
    case Aplicacion = 'Aplicacion';
    case Bitacora = 'Bitacora';
    case Cargo = 'Cargo';
    case Componente = 'Componente';
    case Division = 'Division';
    case Empresa = 'Empresa';
    case Modulo = 'Modulo';
    case Persona = 'Persona';
    case Personal_Access_tokens = 'Personal_Access_tokens';
    case Rol = 'Rol';
    case Rol_Acceso = 'Rol_Acceso';
    case Rol_Asignacion = 'Rol_Asignacion';
    case Unidad_Negocio = 'Unidad_Negocio';
    case Unidad_Organizativa = 'Unidad_Organizativa';
    case Users = 'Users';
    case Usuario = 'Usuario';
    case Usuario_Rol = 'Usuario_Rol';
}

$casts = [
    'tablaBitacora' => TablasBitacora::class,
];
