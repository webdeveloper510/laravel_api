<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use App\Models\User;

use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
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
        if ( $this->checkToken( $request ) ) {
            return $next( $request );
        }

        return response()->json( [ 'error' => 'Unauthorized' ], 403 );


    }

    public function checkToken( $request ) {

        //$client = $request->header( 'client' );
        $token  = $request->header( 'token' );
        $checkToken = User::where( 'remember_token', $token)->first();
        //print_r($checkToken);die;
        return $checkToken;
    }

        // try {
        //     $user = JWTAuth::parseToken()->authenticate();
        // } catch (Exception $e) {
        //     if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
        //         return response()->json(['status' => 'Token is Invalid']);
        //     }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
        //         return response()->json(['status' => 'Token is Expired']);
        //     }else{
        //         return response()->json(['status' => 'Authorization Token not found']);
        //     }
        // }
    
}