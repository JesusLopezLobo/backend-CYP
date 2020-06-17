<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Comment;
use App\Helpers\JwtAuth;

class ComentController extends Controller
{

    public function index(){
        $comment = Comment::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'comment' => $comment
        ]);
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
                'content' => 'required'
            ]);

            // Guardar articulos.
            if($validate->fails()){

                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el comentario, faltan datos.'
                ];

            }else {

                $comment = new Comment();
                $comment->user_id = $user->sub;
                $comment->post_id = $params->post_id;
                $comment->content = $params->content;

                $comment->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'comment' => $comment
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
}
