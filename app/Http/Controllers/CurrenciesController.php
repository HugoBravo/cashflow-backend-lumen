<?php

namespace App\Http\Controllers;

use App\Models\Currencies;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{

    public function index( $showNulls ) {

        try {

            if ( $showNulls == 0 )
                $currencies = Currencies::where('status', true)->get();
            else{
                $currencies = Currencies::all();
            }

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
            'showNulls' => $showNulls,
            'data' => $currencies         
        ]);
    }

    public function update( Request $request ){

        try {

            $status = null;
            $name = null;
            $symbol = null;
            $image = null;

            $id = $request->input('id');
            $status = $request->input('status');
            $name = $request->input('name');
            $symbol = $request->input('symbol');
            $image = $request->input('image');        

            $updateData = array('id' => $id);

            if (($status >= 0) ){
                $updateData['status'] = $status;
            }

            if ( strlen($name) > 0 ){
                $updateData['name'] = $name;
            }

            if ( strlen($symbol) > 0 ){
                $updateData['symbol'] = $symbol;
            }
            
            if ( strlen($image) > 0 ){
                $updateData['image'] = $image;
            }

            $affected = Currencies::where('id', $id)
            ->update( $updateData );

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



    //TODO: NOT IN USE
    public function create(Request $request) {

        try {

            $currency = Currencies::create($request->all());

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
            'data' => $currency         
        ]);
    }


    
}
