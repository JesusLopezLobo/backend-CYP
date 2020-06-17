<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {
    public $key;

    public function __construct()
    {
        $this->key = 'esta-es-mi-clave-secreta-@*1234123412351246143'; // Clave inventada para descodificar y ser todo mas seguro.    
    }

    public function singup($email, $password, $getToken=null){

        $user = User::where(
            array(
                'email' => $email,
                'password' => $password    
            )
        )->first();

        $signup = false;
        if(is_object($user)){
            $signup = true;
        }

        if($signup){

            // Generar el token y devolverlo (Iniciar sesión).
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'image' => $user->image,
                'surname' => $user->surname,
                'iat' => time(), // Tiempo de creación del token (timespan).
                'exp' => time() + (7 * 24 * 60 * 60) // Tiempo de expiración.
            );

            // En función del parametro getToken pasamos una variable u otra. Una codificada y la otra decodificada.
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, array('HS256'));

            if(is_null($getToken)){
/*                 var_dump("ha entrado en el token.");
                var_dump($getToken); die(); */
                return $jwt;
            }else {
                /* var_dump("ha entrado en el objeto.");
                var_dump($getToken); die(); */
                return $decode;
            }

        }else {
            return array('status' => 'error', 'message' => 'Login ha fallado!!');
        }

    }

    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        

        try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

        }catch(\UnexpectedValueException $e){
            $auth = false;

        }catch(\DomainException $e){
            $auth = false;
            
        }

        if(isset($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else {
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }

        return $auth;
    }
}