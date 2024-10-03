<?php

namespace App\Imports;

use App\Models\Persona;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PersonasImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        foreach ($row as $r) {
            error_log("row: $r");
        }
        return new Persona([
            "codigo" => $row['codigo'],
            "apellido_paterno" => $row['apellido_paterno'],
            "apellido_materno" => $row['apellido_materno'],
            "nombre_completo" => $row['nombre_completo'],
            "nombre" => $row['nombres'],
            "cargo" => $row['cargo'],
            "ubicacion" => $row['ciudad'],
            "area" => $row['area'],
            "unidad_negocio_id" => $row['unidad_negocio_id'],
            "centro_costo_id" => $row['centro_costo_id'],
            "empresa_id" => $row['empresa_id'],
        ]);
    }
}
