<?php

namespace App\Http\Controllers;

use App\Models\UserRoles;

class UserRolesController extends Controller
{
    //
    public function getRoleById( $id ){

        $role = UserRoles::find( $id );
        
        return $role;
        
    }

    public function getRoles(){
        try {

            $roles = UserRoles::all();

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
            'data' => $roles
        ]);
    }
}
