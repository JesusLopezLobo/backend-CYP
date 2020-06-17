<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Nota;

class NotaController extends Controller
{
    public function index( Request $request) { // Método para mostrar todos los registros.
        $notas = Nota::all()->load('user');
        return response()->json(array(
            'notas' => $notas,
            'status' => 'success'
        ), 200);      
    }

    public function show($id) { // Método para mostrar solo un registro.

        $notas = Nota::find($id);

        if(is_object($notas)){
            $notas = Nota::find($id)->load('user');
            return response()->json(array(
                'notas' => $nota,
                'status' => 'success'
            ),200);
        }else {
            return response()->json(array(
                'message' => 'La poesía no existe.',
                'status' => 'error'
            ),200);
        }

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
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);
            unset($params_array['user']);
            $notas = Nota::where('id', $id)->update($params_array);

            $data = array(
                'nota' => $params,
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
            $nota = Nota::find($id);

            // Borrarlo.

            $nota->delete();
            //var_dump("llego"); die();
            // Devolverlo.
            $data = array(
                'car' => $nota,
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
                    'title' => 'required|min:2'
                ]);

                if($validate->fails()){
                    return response()->json($validate->errors(), 400);
                }

            

            // Guardar el coche.

            $nota = new Nota();
            $nota->user_id = $user->sub;
            $nota->title = $params->title;
            $nota->description = $params->description;
            $nota->status = $params->status;


            $nota->save();

            $data = array(
                'nota' => $nota,
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
