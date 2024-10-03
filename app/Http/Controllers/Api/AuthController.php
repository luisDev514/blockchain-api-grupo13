<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use App\Models\Modulo;
use App\Models\Rol_Acceso;
use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use stdClass;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'success' => true,
            'message' => 'Usuario registrado correctamente',
        ]);
    }

    // Login an existing user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        error_log("Request: $request");

        $email = $request->input('email');
        $password = $request->input('email');
        $auth_attempth = Auth::attempt([
            'email' => $email,
            'password' => $password,
        ]);
        error_log("Auth: $auth_attempth");
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = Auth::user();
        $persona = $user->persona()->first();
        error_log("User: $user");
        $token = $user->createToken('auth_token')->plainTextToken;

        // Aplicacion con codigo = $codigo_app
        $aplicacion = Aplicacion::where('codigo', '=', $request->codigo_app)->first();
        if (!$aplicacion) {
            return response(
                [
                    "success" => false,
                    "message" => "No se encontró la Aplicación con Codigo_App $request->codigo_app",
                    "data" => null
                ],
                404
            );
        }
        $rol = $user->roles()->where('aplicacion_id', '=', $aplicacion->aplicacion_id)->first();
        error_log("Login->Roles: $rol");

        if (!$rol) {
            return response(
                [
                    "success" => false,
                    "message" => "El usuario $request->username no tiene un rol asignado para la Aplicación con Codigo_App $request->codigo_app",
                    "data" => null
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        // PARA MANEJAR LOS PERMISOS DENTRO DE LA APLICACION
        $accesos = Rol_Acceso::join('rol', 'rol_acceso.rol_id', 'rol.rol_id')
            ->join('modulo', 'rol_acceso.modulo', 'modulo.modulo_id')
            ->where('rol_acceso.rol_id', '=', $rol->rol_id)
            ->where('modulo.aplicacion_id', '=', $aplicacion->aplicacion_id)
            ->get(['rol_acceso.acceso_id', 'rol.rol_id', 'rol.nombre', 'modulo.modulo_id', 'modulo.modulo_padre',  'modulo.menu', 'modulo.titulo', 'modulo.nombre', 'modulo.url']);

        $rol_accesos_id = $accesos->map(function ($acceso) {
            return $acceso->modulo_id;
        });

        // Modulos a los que puede acceder este usuario -> PARA EL MENU
        $modulos = Modulo::whereIn('modulo_id', $rol_accesos_id)->where('habilitado', '=', 1)->get();

        $data = new stdClass();
        $data->aplicacion = $aplicacion;
        $data->accesos = $accesos;
        $data->modulos = $modulos;
        $data->rol = $rol;
        $data->user = $user;
        $data->token = $token;
        $data->persona = $persona;

        return response()->json([
            'data' => $data,
            'success' => true,
        ]);
    }

    // Logout the user (invalidate token)
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function destroy($id)
    {
        // Buscar al usuario por su ID
        $user = User::find($id);
        // $customer = Customer::findOrFail($id);

        // Verificar si el usuario existe
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Eliminar el usuario
        $user->delete();

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function index()
    {
        try {
            $users = User::all();
            $users_count = count($users);
            return response()->json([
                'data' => $users,
                'message' => "($users_count) Users obtenidos correctamente",
                'success' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }
}
