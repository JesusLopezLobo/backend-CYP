<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser']]);
    }

    public function index(){
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }
    
    public function show($id){

        $posts = Post::find($id);

        if(is_object($posts)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts
            ];
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => "La entrada no existe."
            ];
        }

        return response()->json($data, $data['code']);

    }

    public function store(Request $request){
        // Recoger datos por Post.
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            // Conseguir usuarios identificados.
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            // Validar los datos.
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            // Guardar articulos.
            if($validate->fails()){

                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos.'
                ];

            }else {

                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }

        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envia los datos correctamente.'
            ];
        }
        
        // Devolver la respuesta.
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){

        // Recoger los datos por post.
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        //var_dump($params_array); die();
        // Devolver la respuesta.
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos',
        );

        if(!empty($params_array)){

            // Validar los datos.
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data,$data['code']);
            }


            // Eliminar lo que no queremos actualizar.
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);
            unset($params_array['user']);

            // Conseguir al usuario identificado.
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            // Buscar el registro a actualizar.
            $post = Post::where('id', $id)->update($params_array); // Hay dos condiciones de where (id = id y user_id = id del usuario), para coger también el id del usuario.
            //$user_update = User::where('id', $user->sub)->update($params_array);

            //if(!empty($post) && is_object($post)){
                // Actualizar el registro en concreto.
                
                //$post->update($params_array);

                // Devolver la respuesta.
                $data = array(
                    'code' => 200,
                    'status' => 'success', 
                    'post' => $post,
                    'changes' => $params_array
                );
                //var_dump($data); die();
            //}
            //$post = Post::where('id', $id)->where('user_id', $user->sub)->update($params_array);

            
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){
        // Conseguir el usuario identificado.
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        
        // Conseguir el registro.
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first(); // Hay dos condiciones de where (id = id y user_id = id del usuario), para coger también el id del usuario.

        if(!empty($post)){
            // Borrarlo.
            $post->delete();

            // Devolver respuesta.
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        }else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
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

        //var_dump($validate); die();

        // Guardar imagen.
        if(!$image || $validate->fails()){
            
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
            );

        }else{

            $image_Name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_Name, \File::get($image));

            $data = array(
                'image' => $image_Name,
                'code' => 200,
                'status' => 'success'
            );

        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        // Comprobar si existe el fichero.
        $isset = \Storage::disk('images')->exists($filename);

        if($isset){
            // Conseguir la imagen.
            $file = \Storage::disk('images')->get($filename);

            // Devolver la imagen.
            return new Response($file, 200);
        }else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            ];
        }

        return response()->json($data, $data['code']);


    }

    public function getPostsByCategory($id) {
        // Post por categorias.
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id){
        // Posts por usuarios.
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

}
