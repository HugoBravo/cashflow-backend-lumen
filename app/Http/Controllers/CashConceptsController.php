<?php

namespace App\Http\Controllers;

use App\Models\CashConcepts;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashConceptsController extends Controller
{

    public function index() {

        try {

            $concepts = CashConcepts::all();

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
            'data' => $concepts       
        ]);

    }

    public function getConceptsByType( $type ) {

        if (!$valid = ( ( $type === '1' || $type === '2' ) )){
            return response()->json([
                'status' => false,
                'message' => 'Invalid argument'       
            ]);

        }

        try {

            $concepts = DB::table('cash_concepts')
                ->join('cash_categories', 'category_id', '=', 'cash_categories.id')
                ->select('cash_concepts.*', 'cash_categories.category AS category')
                ->where('cash_concepts.type', '=', $type)
                ->get();

                foreach ($concepts as $c) {
                    $hasCashFlow = false;
                    $hasCashFlow = $this->hasCashFlows( $c->id );
                    $c->has_flows = $hasCashFlow;
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
            'data' => $concepts
        ]);
    }

    public function addConcept(Request $request) {

        try {

            $concept = new CashConcepts();

            $concept->concept = $request->input('concept');
            $concept->category_id = $request->input('category_id');
            $concept->type = $request->input('type');
            $concept->status = $request->input('status');
            $concept->save();

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1] 
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $concept         
        ]);

    }

    public function update( Request $request ){

        try {

            $status = null;
            $concept = null;
            $category_id = null;
            $type = null;

            $id = $request->input('id');
            $category_id = $request->input('category_id');
            $status = $request->input('status');
            $concept = $request->input('concept');        
            $type = $request->input('type');        

            $updateData = array('id' => $id);

            if ( $status >= 0 ){
                $updateData['status'] = $status;
            }
            if ( $type >= 0 ){
                $updateData['type'] = $type;
            }
            if ( $category_id >= 0 ){
                $updateData['category_id'] = $category_id;
            }
            if ( strlen($concept) > 0 ){
                $updateData['concept'] = $concept;
            }

            $affected = CashConcepts::where('id', $id)
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

    public function delete( $id ){

        try {

            if ( !$this->hasCashFlows( $id )){
                CashConcepts::destroy( $id );
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Concepto tiene flujos asociados'
                ]);
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
            'data' => ([ 'id' => $id])

        ]);
    }

    private function hasCashFlows( $id ){

        $hasFlows = CashFlow::where('cash_concept_id', $id)->exists();

        return $hasFlows;

    }

}
