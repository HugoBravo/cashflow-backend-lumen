<?php

namespace App\Http\Controllers;

use App\Models\CashCategory;
use App\Models\CashConcepts;
use Illuminate\Http\Request;

class CashCategoryController extends Controller
{

    public function getCategoriesByType( $type ){

        try {

            $categories = CashCategory::where('type', $type)->get();

            foreach ($categories as $c) {
                $hasCashFlow = false;
                $hasCashFlow = $this->hasCashConcepts( $c->id );
                $c->has_concepts = $hasCashFlow;
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
            'data' => $categories
        ]);

    }

    public function addCategory(Request $request) {

        try {

            $category = new CashCategory();

            $category->category = $request->input('category');
            $category->type = $request->input('type');
            $category->status = $request->input('status');
            $category->save();

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
            'data' => $category         
        ]);

    }

    public function update( Request $request ){

        try {

            $status = null;
            $category = null;

            $id = $request->input('id');
            $status = $request->input('status');
            $category = $request->input('category');        

            $updateData = array('id' => $id);

            if (($status >= 0) ){
                $updateData['status'] = $status;
            }

            if ( strlen($category) > 0 ){
                $updateData['category'] = $category;
            }

            $affected = CashCategory::where('id', $id)
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

            if ( !$this->hasCashConcepts( $id )){
                CashCategory::destroy( $id );
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Categoria tiene conceptos asociados'
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

    private function hasCashConcepts( $id ){

        $hasConcepts = CashConcepts::where('category_id', $id)->exists();

        return $hasConcepts;

    }
}