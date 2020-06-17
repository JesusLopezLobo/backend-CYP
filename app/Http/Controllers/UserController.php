<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function register(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'user';
        $image = 'defecto.jpeg';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;

        if(!is_null($email) && !is_null($password) && !is_null($name)){

            // Crear el usuario.
            $user = new User();

            $user->email = $email;
            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;
            $user->image = $image;
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

    public function update(Request $request){
        

        // Comprobar que el usuario está identificado.
        $hash = $request->header('Authorization', null); // Caebecera de autorización que lleva todo el token del usuario generado al loguearse.
 
        $jwtAuth = new JwtAuth(); // Nuestra librería token creada por nosotros.
        $checkToken = $jwtAuth->checkToken($hash); // Nos permite comprobar si el token es válido o no.

        // Recoger los datos por post.
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array)){



            // Sacar al usuario identificado.
            $user = $jwtAuth->checkToken($hash, true);

            // Validar datos.
            $validate = \Validator::make($params_array,[
                'name' => 'required',
                'surname' => 'required',
                'email' => 'required|email|unique:users'.$user->sub,
            ]);

            // Quitar todos los campos que no quiero actualizar.
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);
            unset($params_array['remember_token']);

            // Actualizar usuario en bbdd. 
            $user_update = User::where('id', $user->sub)->update($params_array);

            // Devolver array con resultado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user, // Datos antiguos antes de modificarlos.
                'changes' => $params_array // Datos ya cambiados.
            );

        }else {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado.'
            );

        }

        return response()->json($data, $data['code']);

    }

    public function upload(Request $request){

        //var_dump("he llegado");die();
        // Recoger datos de la petición.
        $image = $request->file('file0');

        // Validación de imagen.
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen.
        if(!$image || $validate->fails()){
            
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
            );

        }else{

            $image_Name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_Name, \File::get($image));

            $data = array(
                'image' => $image_Name,
                'code' => 200,
                'status' => 'success'
            );

        }

        return response()->json($data, $data['code']);

    }

    public function getImage($filename){

        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else {

            $data = array(
                'code' => 404,
                'message' => 'La imagen no existe.',
                'status' => 'success'
            ); 

            return response()->json($data, $data['code']);
        }

    }

    public function show(){
        $user = User::all();

        if(is_object($user)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user
            ];
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => "No hay usuarios."
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function detail($id){ // Sacar detalles de un usuario en concreto.

        $user = User::find($id);

        if(is_object($user)){

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );

        }else {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
            
        }

        return response()->json($data, $data['code']);
    }
}
