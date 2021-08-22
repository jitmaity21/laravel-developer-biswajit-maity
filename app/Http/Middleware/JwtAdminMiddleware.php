<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtAdminMiddleware extends BaseMiddleware
{
    
    public function handle($request, Closure $next){

        try {

            $user = JWTAuth::parseToken()->authenticate();

            if( !$user ) throw new Exception('User Not Found');
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
            return response()->json([
                
                'status' => false,
                'err_' => [
                    'message' => 'Token Invalid',
                    'code' => 401
                ]
            ] );

            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                return response()->json([
                    
                    'status' => false,
                    'err_' => [
                        'message' => 'Token Expired',
                        'code' =>103
                    ]
                ]);
            } 
            else{
                if( $e->getMessage() === 'User Not Found') {
                    return response()->json([
                    "status" => false,
                    "err_" => [
                        "message" => "User Not Found",
                        "code" => 404
                        ]
                    ]); 
                }

                return response()->json([
                    
                    'status' => false,
                    'err_' => [
                        'message' => 'Authorization Token not found',
                        'code' =>404
                    ]
                ]);
            }
        }
        if($user['user_role'] == 'admin'){
            return $next($request);
        }else{
            return response()->json([
                'status' => false,
                'err_' => [
                    'message' => 'Hi '.$user['email'].', You do not have admin  permission for this',
                    'code' =>404
                ]
            ]);
        }
    }
}
