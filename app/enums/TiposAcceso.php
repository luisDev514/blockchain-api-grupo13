<?php

namespace App\enums;


enum TiposAcceso: string
{
    case Index = '_index';
    case Insert = '_insert';
    case Update = '_update';
    case Delete = '_delete';
}

$casts = [
    'tiposAcceso' => TiposAcceso::class,
];
