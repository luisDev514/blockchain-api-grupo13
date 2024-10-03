<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\Http\Controllers\Controller;
use App\Imports\PersonasImport;
use App\Models\Bitacora;
use App\Models\Persona;
use App\Models\PersonaCentroCosto;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $empresa_id = $request->input('empresaId');
            $unidad_negocio_id = $request->input('unidadNegocioId');
            $personas = Persona::leftJoin('cargos', 'persona.cargos_id', 'cargos.id')
                ->when($empresa_id, function ($query, $empresa_id) {
                    $query->where('persona.empresa_id', '=', $empresa_id);
                })
                ->when($unidad_negocio_id, function ($query, $unidad_negocio_id) {
                    $query->where('persona.unidad_negocio_id', '=', $unidad_negocio_id);
                })
                ->with(['user', 'unidadNegocio', 'empresa', 'cargos'])
                ->get();
            $personas_count = count($personas);
            return response()->json([
                "success" => true,
                "message" => "($personas_count) Personas obtenidas exitosamente.",
                "data" => $personas
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function indexV2(Request $request)
    {
        try {
            // $unidad_negocio_id = $request->input('unidadNegocioId');
            $superior_id = $request->input('superiorId');
            $personas = Persona::leftJoin('cargos', 'persona.cargos_id', 'cargos.id')
                ->leftJoin('cargos as cargos_superior', 'cargos.superior_id', 'cargos_superior.id')
                ->leftJoin('unidad_negocio', 'persona.unidad_negocio_id', 'unidad_negocio.id')
                ->leftJoin('division', 'unidad_negocio.division_id', 'division.division_id')
                ->join('empresa', 'persona.empresa_id', 'empresa.empresa_id')
                // ->when($unidad_negocio_id, function ($query, $unidad_negocio_id) {
                //     $query->where('persona.unidad_negocio_id', '=', $unidad_negocio_id);
                // })
                ->when($superior_id, function ($query, $superior_id) {
                    $query->where('cargos.superior_id', '=', $superior_id);
                })
                ->get([
                    'persona.persona_id',
                    'persona.nombre_completo',
                    'persona.unidad_negocio_id',
                    'unidad_negocio.nombre as unidad_negocio_nombre',
                    'unidad_negocio.division_id',
                    'division.nombre as division_nombre',
                    'persona.empresa_id',
                    'persona.cargos_id as cargo_id',
                    'cargos.nombre as cargo_nombre',
                    'cargos.superior_id as cargo_superior_id',
                    'cargos_superior.nombre as cargo_superior_nombre',
                ]);
            $personas_count = count($personas);
            return response()->json([
                "success" => true,
                "message" => "($personas_count) Personas obtenidas exitosamente.",
                "data" => $personas
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            $persona = new Persona();
            $request->validate([
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'habilitado'
            ]);

            $persona->codigo = $request->codigo;
            $persona->empresa_id = $request->empresa_id;
            $persona->nombre = $request->nombre;
            $persona->apellido_paterno = $request->apellido_paterno;
            if ($request->apellido_materno) {
                $persona->nombre_completo = $request->nombre . ' ' .  $request->apellido_paterno;
            } else {
                $persona->nombre_completo = $request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno;
            }
            if ($request->unidad_negocio_id) $persona->unidad_negocio_id = $request->unidad_negocio_id;
            if ($request->apellido_materno) $persona->apellido_materno = $request->apellido_materno;
            if ($request->ubicacion) $persona->ubicacion = $request->ubicacion;
            if ($request->ci) $persona->ci = $request->ci;
            if ($request->ci_origen) $persona->ci_origen = $request->ci_origen;
            if ($request->ci_extension) $persona->ci_extension = $request->ci_extension;
            if ($request->fecha_nacimiento) $persona->fecha_nacimiento = $request->fecha_nacimiento;
            if ($request->correo) $persona->correo = $request->correo;
            if ($request->telefono) $persona->telefono = $request->telefono;
            if ($request->cargo) $persona->cargo = $request->cargo;
            if ($request->foto) $persona->foto = $request->foto;
            if ($request->cargos_id) $persona->cargos_id = $request->cargos_id;
            $persona->ubicacion = $request->ubicacion;
            $persona->habilitado = $request->habilitado;
            $persona->save();

            // encargado
            // persona_id
            // centro_costo_id

            if ($request->centro_costo_id) {
                $persona_cc = new PersonaCentroCosto();
                $persona_cc->centro_costo_id = $request->centro_costo_id;
                $persona_cc->persona_id = $persona->persona_id;
                if ($request->encargado) $persona_cc->encargado = $request->encargado == true ? 1 : 0;
                $persona_cc->save();
            }
            return response()->json([
                "success" => true,
                "message" => "Persona registrada exitosamente.",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->input('user');
            $persona_cc = $request->input('personaCC');
            $persona = Persona::leftJoin('cargos', 'cargos.id', 'persona.cargos_id')
                ->leftJoin('unidad_organizativa', 'cargos.unidad_organizativa_id', 'unidad_organizativa.unidad_organizativa_id')
                ->when($persona_cc, function ($query) {
                    // $query->with(['centrosCosto', 'personaCentrosCostos']);
                    $query->with('personaCentrosCostos.centroCostos');
                })
                ->when($user, function ($query) {
                    $query->with('user');
                })
                ->where('persona.persona_id', '=', $id)
                ->get([
                    "persona.persona_id",
                    "persona.nombre",
                    "persona.apellido_paterno",
                    "persona.apellido_materno",
                    "persona.nombre_completo",
                    "persona.ci",
                    "persona.ci_origen",
                    "persona.ci_extension",
                    "persona.codigo",
                    "persona.fecha_nacimiento",
                    "persona.correo",
                    "persona.telefono",
                    "persona.empresa_id",
                    "persona.cargo",
                    "persona.foto",
                    "persona.ubicacion",
                    "persona.habilitado",
                    "persona.created_at",
                    "persona.updated_at",
                    "persona.unidad_negocio_id",
                    "persona.cargos_id",
                    "cargos.unidad_organizativa_id",
                    "unidad_organizativa.division_id",
                ])
                ->first();
            if (!$persona) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Persona con id: $id",
                    "data" => null
                ]);
            }

            return response()->json([
                "success" => true,
                "message" => "Persona listada correctamente",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            $persona = Persona::find($id);
            if (!$persona) {
                $message = "No se pudo encontrar la persona con id: $id";

                return response()->json([
                    "success" => false,
                    "message" => $message,
                    "data" => null
                ]);
            }

            $request->validate([
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'habilitado'
            ]);

            $persona->codigo = $request->codigo;
            $persona->empresa_id = $request->empresa_id;
            $persona->nombre = $request->nombre;
            $persona->apellido_paterno = $request->apellido_paterno;
            if ($request->apellido_materno) {
                $persona->nombre_completo = $request->nombre . ' ' .  $request->apellido_paterno;
            } else {
                $persona->nombre_completo = $request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno;
            }
            if ($request->apellido_materno) $persona->apellido_materno = $request->apellido_materno;
            if ($request->unidad_negocio_id) $persona->unidad_negocio_id = $request->unidad_negocio_id;
            if ($request->ubicacion) $persona->ubicacion = $request->ubicacion;
            if ($request->ci) $persona->ci = $request->ci;
            if ($request->ci_origen) $persona->ci_origen = $request->ci_origen;
            if ($request->ci_extension) $persona->ci_extension = $request->ci_extension;
            if ($request->fecha_nacimiento) $persona->fecha_nacimiento = $request->fecha_nacimiento;
            if ($request->correo) $persona->correo = $request->correo;
            if ($request->telefono) $persona->telefono = $request->telefono;
            if ($request->cargo) $persona->cargo = $request->cargo;
            if ($request->foto) $persona->foto = $request->foto;
            if ($request->cargos_id) $persona->cargos_id = $request->cargos_id;
            $persona->ubicacion = $request->ubicacion;
            $persona->habilitado = $request->habilitado;
            $persona->save();
            return response()->json([
                "success" => true,
                "message" => "Persona modificada exitosamente.",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $persona = Persona::find($id);
            $persona->habilitado = 0;
            $persona->save();
            return $persona;
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo registrar la categoria",
                "data" => $message
            ]);
        }
    }

    public function habilitar(Request $request, $id)
    {
        try {
            $request->validate([
                'user',
                'codigo_app',
            ]);
            $persona = Persona::find($id);
            if (!$persona) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe la Persona con id: $id",
                    "data" => null
                ]);
            }

            $persona->habilitado = $persona->habilitado === 0 ? 1 : 0;
            $persona->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $persona->habilitado === 1 ? OperacionesBitacora::Habilitar : OperacionesBitacora::Deshabilitar;
            $log->tabla = TablasBitacora::Persona;
            $log->tabla_identificador = $persona->persona_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function upload(Request $request)
    {
        try {
            $path = $request->file('uploadFile')->store('temp');
            error_log("path $path");
            Excel::import(new PersonasImport, $path);
            return response()->json([
                "success" => true,
                "message" => "Personas importadas correctamente",
                "data" => null
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function peopleByUnidadNegocio($un)
    {
        try {
            $users = Persona::where('unidad_negocio_id', '=', $un)->get();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "$users_count Personas obtenidas exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function peopleByUnidadNegocio2(Request $request)
    {
        try {
            $un = $request->query('un');
            $user = $request->query('user');
            $habilitado = $request->query('habilitado');

            $users = Persona::when($user, function ($query, $user) {
                $query->with('user');
            })->when($habilitado, function ($query, $habilitado) {
                $query->where('habilitado', '=', 1);
            })->where('unidad_negocio_id', '=', $un)->get();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "$users_count Personas obtenidas exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function peopleByDivision($division_id)
    {
        try {
            $personas = Persona::leftjoin('unidad_negocio', 'persona.unidad_negocio_id', '=', 'unidad_negocio.unidad_negocio_id')
                ->leftjoin('division', 'unidad_negocio.division_id', '=', 'division.division_id')
                ->where('division.division_id', '=', $division_id)->get();
            $personas_count = $personas->count();
            return response()->json([
                "success" => true,
                "message" => "$personas_count Personas obtenidas exitosamente.",
                "data" => $personas
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function filtrar(Request $request)
    {
        try {
            $id = $request->persona_id;
            $user = $request->user;
            // $persona = Persona::find($request->persona_id);
            $persona = Persona::when($user, function ($query, $user) {
                $query->with('user');
            })->find($id);
            if (!$persona) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Persona con id: $id",
                    "data" => null
                ]);
            }

            return response()->json([
                "success" => true,
                "message" => "Persona listada correctamente",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function addCC(Request $request, $id)
    {
        try {
            $persona = Persona::find($id);
            if (!$persona) {
                $message = "No se pudo encontrar la persona con id: $id";

                return response()->json([
                    "success" => false,
                    "message" => $message,
                    "data" => null
                ]);
            }
            $persona->centrosCosto()->sync($request->centros);
            $persona->save();

            if ($request->centro_costo_id) {
                $persona_cc = new PersonaCentroCosto();
                $persona_cc->centro_costo_id = $request->centro_costo_id;
                $persona_cc->persona_id = $persona->persona_id;
                if ($request->encargado) $persona_cc->encargado = $request->encargado == true ? 1 : 0;
                $persona_cc->save();
            }
            return response()->json([
                "success" => true,
                "message" => "Persona modificada exitosamente.",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function updateCargo(Request $request)
    {
        try {
            $persona_id = $request->input('persona_id');
            $persona = Persona::find($persona_id);
            if (!$persona) {
                $message = "No se pudo encontrar la persona con id: $persona_id";

                return response()->json([
                    "success" => false,
                    "message" => $message,
                    "data" => null
                ]);
            }

            $request->validate([
                'cargos_id',
            ]);

            $persona->cargos_id = $request->cargos_id;
            $persona->save();
            return response()->json([
                "success" => true,
                "message" => "Cargo asignado exitosamente.",
                "data" => $persona
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }
}
