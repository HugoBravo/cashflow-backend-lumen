<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\CashClosure;
use App\Models\CashClosureBalance;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashClosureController extends Controller
{

    public function lastClosure(){
        
        try {

            $lastClosures = CashClosure::select('id', 'datetime', 'status', 'obs', 'username')
                ->where('status', true)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            $closureId = $lastClosures[0]->id;
            $closureDatetime = $lastClosures[0]->datetime;

            $hasPostFlow = CashFlow::where('datetime', '>' ,$closureDatetime)
                ->where('status', '=' , true)
                ->exists();

            $lastClosure = $lastClosures[0];
            $lastClosure->has_post_flow = $hasPostFlow;
            
            $balances = DB::table('cash_closure_balances')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->select('cash_closure_balances.id','cash_closure_balances.currency_id', 'cash_closure_balances.balance', 'currencies.name', 'currencies.symbol', 'currencies.image')
                ->where('cash_closure_id', $closureId)
                ->get();

            foreach ( $balances as $b ) {
                $incomes = CashFlow::where('status', '=' , true)
                    ->where('type', '=' , 1)
                    ->where('currency_id', '=' , $b->currency_id)
                    ->where('datetime', '>' ,$closureDatetime)
                    ->sum('amount');
                
                $b->post_incomes = $incomes;

                $expenses = CashFlow::where('status', '=' , true)
                    ->where('type', '=' , 2)
                    ->where('currency_id', '=' , $b->currency_id)
                    ->where('datetime', '>' ,$closureDatetime)
                    ->sum('amount');

                $b->post_expenses = $expenses;

                $currentBalance = $b->balance + $incomes - $expenses;

                $b->current_balance = $currentBalance;

            }

            $lastClosure->balances = $balances;
            
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
            'closure' => $lastClosure     
        ]);

    }
    
    public function getHistoricClosures( $n, $showNull ) {

        try {

            if ( $showNull == true ){
                $historic = DB::table('cash_closures')
                ->select('id', 'datetime', 'username', 'obs', 'status')
                ->orderBy('id', 'desc')
                ->limit( $n )
                ->get();
            }else{
                $historic = DB::table('cash_closures')
                ->select('id', 'datetime', 'username', 'obs', 'status')
                ->where('status', true)
                ->orderBy('id', 'desc')
                ->limit( $n )
                ->get();
            }
    
            $currencies = DB::table('currencies')
                ->select('currencies.*')
                ->where('status', true)
                ->get();
    
            foreach ( $historic as $h ) {
    
                foreach ( $currencies as $c ) {
                    
                    $balance = DB::table('cash_closure_balances')
                        ->select('cash_closure_balances.*')
                        ->where('cash_closure_id', $h->id)
                        ->where('currency_id', $c->id)
                        ->get(); 
                    
                    $currency_id = $c->symbol;
    
                    if (count($balance) != 0){
                        $h->$currency_id = $balance[0]->balance;
                    }else{
                        $h->$currency_id = 0;
                    }
                }
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
            'closures' => $historic   
        ]);

    }

    public function getGraphHistoricClosures( $n, $showNull) {

        try {

            $currencies = DB::table('currencies')
            ->select('currencies.*')
            ->where('status', true)
            ->get();

            foreach ($currencies as $c ) {

                $historic = DB::table('cash_closures')
                    ->join('cash_closure_balances', 'cash_closures.id', '=', 'cash_closure_id')
                    ->select('cash_closures.id', 'cash_closures.datetime', 'cash_closure_balances.id', 'cash_closure_balances.balance')
                    ->where('cash_closures.status', true)
                    ->where('currency_id', "=" ,$c->id)
                    ->orderby('cash_closures.id','desc')
                    ->limit( $n )
                    ->get(); 

                $legends  = [];
                $balances = [];

                foreach ($historic as $h ) {
                    $date = $h->datetime;
                    $dateFormated = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/M');
                    $legends[] = $dateFormated;
                    $balances[] = $h->balance;
                };

                $response[] = [
                    'label' => $c->symbol,
                    'data' => $balances
                ];
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
            'legends' => $legends,
            'data' => $response   
        ]);

    }

    public function cashClose(Request $request){

        try {

            $username = $request->input('username');

            $closure = new CashClosure;
            $closure->datetime = Carbon::now();
            $closure->status = true;
            $closure->username = $username;
            $closure->obs = $request->input('obs');
            $closure->save();
    
            foreach ($request->input('balances') as $d) {
                
                $balance = new CashClosureBalance;
                $balance->cash_closure_id = $closure->id;
                $balance->currency_id = $d['currency_id'];
                $balance->balance = $d['balance'];
                $balance->save();
                $balances[] = $balance; // debug
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
            'id' => $closure->id,
            'master' => $closure,
            'detail' => $balances
        ]);

    }

    public function statusClosure(Request $request){

        try {

            $id = $request->input('id');
            $status = $request->input('status');
    
            $affected = DB::table('cash_closures')
                ->where('id', $id )
                ->update(['status' => $status]);

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
            'id' => $id,
        ]);

    }

}
