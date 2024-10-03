<?php

namespace App\Imports;

use App\Models\CentroCosto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CentroCostoImport implements ToModel, WithHeadingRow
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
        return new CentroCosto([
            "unidad_negocio_id" => $row['unidad_negocio_id'],
            "centro_costo_id" => $row['centro_costo_id'],
            "nombre" => $row['nombre_centro'],
        ]);
    }
}
