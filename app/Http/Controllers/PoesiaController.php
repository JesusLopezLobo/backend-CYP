<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Poesia;

class PoesiaController extends Controller
{
    public function index( Request $request) { // Método para mostrar todos los registros.
        $poesias = Poesia::all()->load('user');
        return response()->json(array(
            'poesias' => $poesias,
            'status' => 'success'
        ), 200);      
    }

    public function show($id) { // Método para mostrar solo un registro.
        $poesias = Poesia::find($id)->load('user');
        return response()->json(array(
            'poesias' => $poesias,
            'status' => 'success'
        ),200);
    }

    public function update($id, Request $request){ 

        $hash = $request->header('Authorization', null); // Caebecera de autorización que lleva todo el token del usuario generado al loguearse.

        $jwtAuth = new JwtAuth(); // Nuestra librería token creada por nosotros.
        $checkToken = $jwtAuth->checkToken($hash); // Nos permite comprobar si el token es válido o no.

        if($checkToken){ // Si es válido nos guarda la poesía.

            // Recoger parametros POST.

            $json = $request->input('json', null); // Recogemos el post.
            $params = json_decode($json); // Lo convertimos para usarlos en php.
            $params_array = json_decode($json, true); // Lo convertimos en array con un true alfinal.
            
            // Validar los datos.

            $validate = \Validator::make($params_array,[
                'title' => 'required|min:5',
                'description' => 'required',
                'status' => 'required'
            ]);

            if($validate->fails()){ // Si dá error aparece esto.
                return response()->json($validate->errors(), 400);
            }

            // Actualizar el registro.
            $poesia = Poesia::where('id', $id)->update($params_array);

            $data = array(
                'poesia' => $params,
                'status' => 'success',
                'code' => 200
            );

        }else {
            // Devolver error.
            $data = array([
                'message' => 'login incorrecto',
                'status' => 'error',
                'code' => 300
            ]);
        }

        return response()->json($data,200);
    }

    public function destroy($id, Request $request){
        $hash = $request->header('Authorization', null); // Caebecera de autorización que lleva todo el token del usuario generado al loguearse.

        $jwtAuth = new JwtAuth(); // Nuestra librería token creada por nosotros.
        $checkToken = $jwtAuth->checkToken($hash); // Nos permite comprobar si el token es válido o no.

        if($checkToken){ // Si es válido nos guarda la poesía.

            // Comprobar que existe el registro.
            $poesia = Poesia::find($id);

            // Borrarlo.

            $poesia->delete();
            //var_dump("llego"); die();
            // Devolverlo.
            $data = array(
                'car' => $poesia,
                'satus' => 'success',
                'code' => 200
            );


        }else {
            // Devolver error.
            $data = array([
                'message' => 'login incorrecto',
                'status' => 'error',
                'code' => 400
            ]);
        }

        return response()->json($data,200);
    }

    public function store(Request $request){ // Método para guardar registro.
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            // Reocger datos por post.
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true); // true en el segundo parametro para que te pase un array.

            $user = $jwtAuth->checkToken($hash, true);

            // Validar el coche.
            
                $validate = \Validator::make($params_array,[
                    'title' => 'required|min:5',
                    'description' => 'required',
                    'status' => 'required'
                ]);

                if($validate->fails()){
                    return response()->json($validate->errors(), 400);
                }

            

            // Guardar el coche.

            $poesia = new Poesia();
            $poesia->user_id = $user->sub;
            $poesia->title = $params->title;
            $poesia->description = $params->description;
            $poesia->status = $params->status;


            $poesia->save();

            $data = array(
                'poesia' => $poesia,
                'status' => 'success',
                'code' => 200
            );  

        }else {
            // Devolver error.
            $data = array([
                'message' => 'login incorrecto',
                'status' => 'error',
                'code' => 300
            ]);
        }

        return response()->json($data, 200); // Devolvemos el array en json.
    }
}
