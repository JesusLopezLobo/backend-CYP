<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Comprobar que el usuario está identificado.
        $hash = $request->header('Authorization', null); // Caebecera de autorización que lleva todo el token del usuario generado al loguearse.
 
        $jwtAuth = new \JwtAuth(); // Nuestra librería token creada por nosotros.
        $checkToken = $jwtAuth->checkToken($hash); // Nos permite comprobar si el token es válido o no.

        if($checkToken){
            return $next($request);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado.'
            );
            return response()->json($data, $data['code']);
        }
        
    }
}
