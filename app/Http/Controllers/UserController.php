<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'user';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;

        if(!is_null($email) && !is_null($password) && !is_null($name)){

            // Crear el usuario.
            $user = new User();

            $user->email = $email;
            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;
            // Contraseña cifrada.
            $pwd = hash('sha256', $password);
            $user->password = $pwd;

            // Comprobar usuario duplicado.
            $isset_user = User::where('email', '=', $email)->first();

/*             var_dump($isset_user);
            die(); */

            if($isset_user == NULL){
                // Guardar el usuario.
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario creado correctamente.'
                );

            }else {
                // Usuario ya existente.
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Esta cuenta de usuario ya existe.'
                );
            }

        }else {
            // En caso de que fallara y fuera algo nulo.
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no creado'
            );
            

        }

        return response()->json($data, 200);
    }

    public function login(Request $request){
        $jwtAuth = new JwtAuth;

        // Recibir POST
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email) ? $params->email: null);
        $password = (!is_null($json) && isset($params->password) ? $params->password: null);
        $getToken = (!is_null($json) && isset($params->getToken) ? $params->getToken: null);

        // Cifrar la password
        $pwd = hash('sha256', $password);

        if(!is_null($email) && !is_null($password) && ($getToken == null || $getToken == 'false')){
            
            $signup = $jwtAuth->singup($email, $pwd);
            //var_dump($getToken); die();

        }else if($getToken != null){

            $signup = $jwtAuth->singup($email, $pwd, $getToken);
            //var_dump($getToken); die();
        }else {
            
            $signup = array(
                'status' => 'error',
                'message' => 'Envía tus datos por post'
            );

        }

        return response()->json($signup, 200); // Nos devuelve en json el token. codigo 200 para que nos lo devuelva.

    }
}
