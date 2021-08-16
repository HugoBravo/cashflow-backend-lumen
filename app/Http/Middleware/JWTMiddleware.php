<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Try{

            $user = JWTAuth::parseToken()->authenticate();

        }catch ( \Tymon\JWTAuth\Exceptions\JWTException $e ) {

            if ( $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(["message", "token_expired"], 401);
            }else{
                if ( $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid token'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Token is required'
                    ]);
                }
            }

        }

        return $next($request->merge(['user'=> $user]));
    }
}
