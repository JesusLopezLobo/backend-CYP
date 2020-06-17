<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index(){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);
        
        if(is_object($category)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => "La categoria no existe."
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        // Recoger los datos por post.
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            // Validar los datos.
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            // Guardar la categoria.
            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se han guardado categorias'
                ];
            }else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'
            ];
        }

        // Devolver el resultado.
        return response()->json($data, $data['code']);

    }

    public function update($id, Request $request){
        // Recoger datos por post.
        $json = $request->input('json', null);
        $paramas_array = json_decode($json, true);

        if(!empty($paramas_array)){
            // Validar los datos.
            $validate = \Validator::make($paramas_array, [
                'name' => 'required'
            ]);

            // Quitar lo que no quiero actualizar.
            unset($paramas_array['id']);
            unset($paramas_array['created_at']);

            // Actualizar los datos.
            $category = Category::where('id', $id)->update($paramas_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $paramas_array
            ];

        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'
            ];
        }

        // Devolver la respuesta.
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){
        
        // Conseguir el registro.
        $category = Category::where('id', $id)->first(); // Hay dos condiciones de where (id = id y user_id = id del usuario), para coger también el id del usuario.

        if(!empty($category)){
            // Borrarlo.
            $category->delete();

            // Devolver respuesta.
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
