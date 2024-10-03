<?php

namespace App\enums;


enum DefaultModulos: string
{
    case Icono = 'Home';
    case Nombre = 'inicio';
    case Titulo = 'Inicio';
    case Url = '/inicio';
}

$casts = [
    'defaultModulos' => DefaultModulos::class,
];
