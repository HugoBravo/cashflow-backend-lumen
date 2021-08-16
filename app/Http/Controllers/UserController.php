<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UserRolesController;

class UserController extends Controller
{
    public function authenticate( Request $request ){

        $credential = $request->only('email', 'password');
        $validator= Validator::make( $credential, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ( !$validator->fails()){
            try {
                if ( !$token= JWTAuth::attempt($credential) ){
                    return response()->json([
                        'status'=> false,
                        'message' => 'Invalid Credentials'
                    ]);
                }
            } catch ( \Tymon\JWTAuth\Exceptions\JWTException $e ) {
                return response()->json([
                    'status'=> false,
                    'error' => $e->getMessage(),
                    'message' => 'Invalid Credentials'
                ]);
            }

            return response()->json([
                'status'=> true,
                'token' => $token
            ]);

        }else {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }
    }

    public function register( Request $request ){
        
        $data = $request->only('email', 'password', 'role', 'name');

        $exist = DB::table('users')->where('email', $data['email'])->first();

        if ($exist){
            return response()->json([
                'status'=> false,
                'message' => 'Email exist'
            ]);
        }


        $validator= Validator::make( $data, [
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',
            'name' => 'required'
        ]);

        if ( $validator->fails()){

            return response()->json([
                'status'=> false,
                'message' => 'Invalid Data'
            ]);
        }

        $hash = Hash::make( $data['password'] );
        $data['password'] = $hash;
        $user = User::create($data);
        
        return response()->json([
            'status'=> true,
            'data'=> $user,
        ]);

        return response()->json([
            'message' => $request->get('user')
        ]); 
    }

    public function users( Request $request ){

        try {

            $users = DB::table('users')
                ->select('name', 'username', 'email')
                ->where('status', '=','1')
                ->orderBy('username', 'desc')
                ->get();

            } catch (\Illuminate\Database\QueryException $exception) {

                $errorInfo = $exception->errorInfo;

                return response()->json([
                    'status' => false,
                    'message' => $errorInfo[2],
                    'sqlerror' =>$errorInfo[1],            
                ]);

            }

            return response()->json([
                'status' => true,
                'data' => $users         
            ]);


    }

    public function getUsers(){
        
        $user = auth()->user();
        $rolesController = new UserRolesController();
        $role = $rolesController->getRoleById( $user->role_id );

        if( $role->name != 'ROOT_ROLE'){
            return response()->json([
                'status' => false,
                'message' => 'Usuario no autorizado'
            ]);
        }

        $users = DB::table('users')
        ->join('user_roles', 'role_id', '=', 'user_roles.id')
        ->select('users.id', 
                 'users.role_id', 
                 'users.name', 
                 'users.email',
                 'users.username',
                 'users.status',
                 'users.image',
                 'user_roles.name AS role')
        ->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);

    }

    public function add( Request $request ){
        
        $user = auth()->user();
        $rolesController = new UserRolesController();
        $role = $rolesController->getRoleById( $user->role_id );

        if( $role->name != 'ROOT_ROLE' ){
            return response()->json([
                'status' => false,
                'message' => 'Usuario no autorizado'
            ]);
        }

        $newUser = new User();

        if ($request->hasFile('file')){
            $path = $request->file('file')->store('public/users');
            $newUser->image = basename( $path );
        }else{
            $newUser->image = 'blank-profile.png';
        }

        $hash = Hash::make( $request->input('password') );
        $newUser->password = $hash;

        $newUser->role_id = $request->input('role_id');
        $newUser->name = $request->input('name');
        $newUser->email = $request->input('email');
        $newUser->username = $request->input('username');
        $newUser->status = $request->input('status');
        $newUser->save();

        return response()->json([
            'status' => true,
            'data' => $newUser
        ]);

    }

    public function update( Request $request ){

        try {

            $id = $request->input('id');

            $user = User::find( $id );

            if ($request->hasFile('file')){
                $path = $request->file('file')->store('public/users');
                $user->image = basename( $path );
            }else{
                
                $fileOld = 'public/users/'. $user->image;

                Storage::delete( $fileOld );

                $user->image = 'blank-profile.png';
            }

            $user->name = $request->input('name');
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->role_id = $request->input('role_id');
            $user->status = $request->input('status');
            
            if ( strlen($request->input('password')) > 0 ){
                $hash = Hash::make( $request->input('password') );
                $user->password = $hash;
            }

            $user->save();

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);
        }
        
        return response()->json([
            'status' => true,
            'data' => ([ 'id' => $id])
        ]);
    }

}
