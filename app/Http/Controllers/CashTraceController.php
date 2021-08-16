<?php

namespace App\Http\Controllers;

use App\Models\CashTrace;

class CashTraceController extends Controller
{

    public function addFlowTrace( $cashFlowId, $username, $comments ) {

        try {

            $trace = new CashTrace();
            $trace->cash_flow_id = $cashFlowId;
            $trace->username = $username;
            $trace->comments = $comments;
            $trace->save();

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);
        }

    }

    public function getCashTrace( $id ){

        try {

            $trace = CashTrace::where( 'cash_flow_id', $id)->get();
        
        } catch (\Illuminate\Database\QueryException $exception)  {
        
            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);

        }

        return response()->json([
            'status' => true,
            'data' => $trace,         
        ]);
    }

}
