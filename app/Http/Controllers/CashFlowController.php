<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class CashFlowController extends Controller
{

    public function flowsNotClosed() {

        try {

            $dateLastClosure = DB::table('cash_closures')
                ->select('cash_closures.datetime')
                ->where('status', '=','1')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            $flows = DB::table('cash_flow')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                ->select('cash_flow.*', 'currencies.name AS currency', 'currencies.symbol', 'currencies.image', 'cash_concepts.concept')
                ->where('cash_flow.datetime', '>', $dateLastClosure[0]->datetime )
                ->orderBy('id', 'desc')
                ->get();

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);

        }

        $response = $this->addColsIncomeExpenseToFlow( $flows );

        return response()->json([
            'status' => true,
            'data' => $response
        ]);
    }
    
    public function flowsNotClosedByCurrency( $currency_id, $showNulls ) {

        try {
            $status = false;

            if ( $showNulls == 0 ){
                $status = true;
            }else{
                $status = false;
            }

            $dateLastClosure = DB::table('cash_closures')
                ->select('cash_closures.datetime')
                ->where('status', '=','1')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            $flows = DB::table('cash_flow')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                ->select('cash_flow.*', 'currencies.name AS currency', 'currencies.symbol', 'currencies.image', 'cash_concepts.concept')
                ->where('cash_flow.datetime', '>', $dateLastClosure[0]->datetime )
                ->where('cash_flow.currency_id', '=', $currency_id )
                    ->when($status, function ($query, $status) {
                        return $query->where('cash_flow.status','=', 1);
                    })
                
                ->orderBy('id', 'desc')
                ->get();

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);

        }

        $response = $this->addColsIncomeExpenseToFlow( $flows );

        return response()->json([
            'status' => true,
            'data' => $response,
            
        ]);
    }

    public function addFlow(Request $request) {

        try {

            $flow = new CashFlow;

            if ($request->hasFile('file')){
    
                $path = $request->file('file')->store('public/attach');
                $flow->image_name = basename( $path );
                $size = Storage::size($path);
                $flow->image_size = $size;
            }

            $flow->datetime = CarbonImmutable::now();
            $flow->cash_concept_id = $request->input('cash_concept_id');
            $flow->currency_id = $request->input('currency_id');
            $flow->username = $request->input('username');
            $flow->type = $request->input('type');
            $flow->status = $request->input('status');
            $flow->room = $request->input('room');

            $flow->pax = $request->input('pax');
            $flow->nullable = $request->input('nullable');
            $flow->amount = $request->input('amount');
            $flow->obs = $request->input('obs');
            $flow->doc = $request->input('doc');
            $flow->save();
            
            $trace = new CashTraceController;
            $trace->addFlowTrace($flow->id, $flow->username, 'ADD');


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
            'data' => $flow         
        ]);

    }

    public function updateFlow(Request $request){
        try {

            $id = $request->id;

            $flow = CashFlow::find($id);

            if ($request->hasFile('file')){
                $path = $request->file('file')->store('public/attach');
                $flow->image_name = basename( $path );
                $size = Storage::size($path);
                $flow->image_size = $size;
            }else{
                
                $fileOld = 'public/attach/'. $flow->image_name;

                Storage::delete( $fileOld );

                $flow->image_name = basename( null );
                $flow->image_size = null;
            }

            $flow->cash_concept_id = $request->input('cash_concept_id');
            $flow->currency_id = $request->input('currency_id');
            $flow->username = $request->input('username');
            $flow->type = $request->input('type');
            $flow->status = $request->input('status');
            $flow->room = $request->input('room');

            $flow->pax = $request->input('pax');
            $flow->nullable = $request->input('nullable');
            $flow->amount = $request->input('amount');
            
            $flow->obs = $request->input('obs');

            $flow->doc = $request->input('doc');
            $flow->save();
            
            $trace = new CashTraceController;
            $trace->addFlowTrace($flow->id, $flow->username, 'EDIT');


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
            'data' => $flow
        ]); 
    }

    public function getFlow( $id ){

        try {
            
            $flow = CashFlow::where('id', $id)->get();

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
            'data' => $flow[0],
        ]);
    }

    public function addAttachToFlow(Request $request){

        if ($request->hasFile('file')){

            $path = $request->file('file')->store('public/attach');

            return response()->json([
                'status' => true,
                'data' => 'tiene',
                'req'=> $request->file('file'),
                'amount'=> $request->input('amount'),
                'path', $path 
            ]);

        }else{
            return response()->json([
                'status' => false,
                'req'=> $request->all(),
                'data' => 'no tiene'         
            ]);
        }

    }

    private function addColsIncomeExpenseToFlow( $flow ) {

        foreach ($flow as $f ) {

            if ($f->type === 1){
                $f->income = $f->amount;
            }else{
                $f->expense = $f->amount;
            }
        
        }

        return $flow;

    }

    public function flowStatus( Request $request ){

        try {

            $id = $request->input('id');
            $status = $request->input('status');
            $username = $request->input('username');        
    
            $affected = DB::table('cash_flow')
                ->where('id', $id)
                ->update(['status' => $status]);
            
            $trace = new CashTraceController;
            $trace->addFlowTrace( $id, $username, 'STATUS ' . $status);

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
            'id' => $id
        ]);
    }

    public function yearsWithFlow(){

        try {
            
            $years = CashFlow::selectRaw('YEAR(cash_flow.datetime) AS year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
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
            'years' => $years
        ]);

    }

    public function searchData( $from, $to, $type, $currency_id, $user ){

        try {
            
            $flows = DB::table('cash_flow')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                ->select('cash_flow.*', 'currencies.name AS currency', 'currencies.symbol', 'currencies.image', 'cash_concepts.concept')
                ->where ('cash_flow.datetime', '>', $from. ' 00:00:01' )
                ->where('cash_flow.datetime', '<', $to . ' 23:59:59' )
                    ->when($currency_id, function ($query, $currency_id) {
                        return $query->where('cash_flow.currency_id','=', $currency_id);
                    })
                    ->when($type, function ($query, $type) {
                        return $query->where('cash_flow.type','=', $type);
                    })
                    ->when($user, function ($query, $user) {
                        return $query->where('cash_flow.username','=', $user);
                    })
                ->orderBy('id', 'desc')
                ->get();

            return $flows;

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);
        }

    }

    public function searchJson( $from, $to, $type, $currency_id, $user ){

        if ($user === 'TODOS') {
            $user = false;
        };

        try {

            $data = $this->searchData( $from, $to, $type, $currency_id, $user );
            // dd($data);
            $response = $this->addColsIncomeExpenseToFlow( $data );

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);
        }
        
            $headers = ['Content-Type: application/json'];

            return response()->json([
                'status' => true,
                'data' => $response,
            ], 200, $headers);

    }

    public function searchXls( $from, $to, $type, $currency_id, $user ){

        if ($user === 'TODOS') {
            $user = false;
        };

        try {

            $data = $this->searchData( $from, $to, $type, $currency_id, $user );

            $response = $this->addColsIncomeExpenseToFlow( $data );

            $path = storage_path('app/public/searches/');
            $file = 'busqueda-' . Str::random(5) . '.xlsx';
            $fileWithPath = $path . $file;

            $this->searchToExcel( $response, $fileWithPath );

            // $headers = ['Content-Type: application/xls'];

            // return response()->download($fileWithPath, $file, $headers); 

            return response()->json([
                'status' => true,
                'data' => $file
            ]);

        } catch (\Illuminate\Database\QueryException $exception) {

            $errorInfo = $exception->errorInfo;

            return response()->json([
                'status' => false,
                'message' => $errorInfo[2],
                'sqlerror' =>$errorInfo[1],            
            ]);
        }

    }

    private function searchToExcel( $flows, $file ){

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Fecha');
        $sheet->setCellValue('C1', 'Usuario');
        $sheet->setCellValue('D1', 'Concepto');
        $sheet->setCellValue('E1', 'Hab');
        $sheet->setCellValue('F1', 'Moneda');
        $sheet->setCellValue('G1', 'Ingreso');
        $sheet->setCellValue('H1', 'Egreso');
        $sheet->setCellValue('I1', 'Adjunto');
        $sheet->setCellValue('J1', 'Observaciones');
        $rows = 2;

        foreach ($flows as $f){
            $sheet->setCellValue('A' . $rows, $f->id);
            $sheet->setCellValue('B' . $rows, $f->datetime);
            $sheet->setCellValue('C' . $rows, $f->username);
            $sheet->setCellValue('D' . $rows, $f->concept);
            $sheet->setCellValue('E' . $rows, $f->room);
            $sheet->setCellValue('F' . $rows, $f->symbol);
            if ( $f->type === 1 ){
                $sheet->setCellValue('G' . $rows, $f->income);
            }
            if ( $f->type === 2 ){
                $sheet->setCellValue('H' . $rows, $f->expense);
            }
            $sheet->setCellValue('J' . $rows, $f->obs);
        $rows++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save( $file );

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

    }

    public function graphFlowByCategoryAndMonth( $currency_id, $type, $year, $initMonth, $nMonths ){

        try {
            $hasFlows = false;
            $initDate = CarbonImmutable::create( $year, $initMonth, 1, 0, 0, 0 );
            $to = $initDate->format('Y-m') . '-' . $initDate->daysInMonth;
            $from = $initDate->subMonths( $nMonths );
            
            for ( $i=1; $i < $nMonths + 1; $i++ ) { 
                if ( $i != 1 ){
                    $m = $initDate->subMonth( $i );
                }else{
                    $m = $initDate;
                }
                $legends[] = $m->format('M/Y');
                $months[] = [
                    'showMonth' =>  $m->format('M/Y'),
                    'from' => $m->format('Y-m-01'),
                    'to' => $m->format('Y-m') . '-' . $m->daysInMonth,
                ];
            }
    
            $categoriesWithFlow = DB::table('cash_flow')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                ->join('cash_categories', 'cash_concepts.category_id', '=', 'cash_categories.id')
                ->select('cash_categories.id', 'cash_categories.category', 'cash_categories.status')
                ->where ('cash_flow.datetime', '>', $from )
                ->where('cash_flow.datetime', '<', $to )
                    ->where('cash_flow.status', '=', 1 )
                    ->when($currency_id, function ($query, $currency_id) {
                        return $query->where('cash_flow.currency_id','=', (int)$currency_id);
                    })
                    ->when($type, function ($query, $type) {
                        return $query->where('cash_flow.type','=', (int)$type);
                    })
                ->groupBy('cash_categories.id', 'cash_categories.category', 'cash_categories.status')
                ->orderBy('category', 'desc')
                ->get();
    
            $data= [];
            foreach ($categoriesWithFlow as $c) {
                $hasFlows = true;
                $category_id = (int)$c->id;
                $category = $c->category;
    
                $total =[];
                foreach ($months as $m) {
    
                    $fromSQL = $m['from'];
                    $toSQL = $m['to'];
    
                    $totalFlow = DB::table('cash_flow')
                    ->join('currencies', 'currency_id', '=', 'currencies.id')
                    ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                    ->join('cash_categories', 'cash_concepts.category_id', '=', 'cash_categories.id')
                    ->selectRaw('SUM(cash_flow.amount) as total')
                    ->where ('cash_flow.datetime', '>', $fromSQL )
                    ->where('cash_flow.datetime', '<', $toSQL )
                        ->where('cash_flow.status', '=', 1 )
                        ->where('cash_categories.id', '=', $category_id )
                        ->when($currency_id, function ($query, $currency_id) {
                            return $query->where('cash_flow.currency_id','=', (int)$currency_id);
                        })
                        ->when($type, function ($query, $type) {
                            return $query->where('cash_flow.type','=', (int)$type);
                        })
                    ->get();
    
                    $total[] = $totalFlow->sum('total');
    
                }
                $data[] = [
                    'label' => $category,
                    'data' => $total
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
            'has_flows' => $hasFlows,
            'legends' => $legends,
            'data' => $data
        ]);

    }

    public function graphFlowsByConceptAndDaysRange( $currency_id, $type, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo ){

        try {
            $hasFlows = false;
            $dateFrom = CarbonImmutable::create( $yearFrom, $monthFrom, $dayFrom, 0, 0, 1 );
            $dateTo = CarbonImmutable::create( $yearTo, $monthTo, $dayTo, 23, 59, 59 );

            $daysDiff = $dateTo->diffInDays($dateFrom);
            
            for ( $i=0; $i < $daysDiff + 1; $i++ ) {

                $d = $dateTo->subDay( $i );

                $legends[] = $d->format('d/M');
                $days[] = [
                    'showMonth' =>  $d->format('d/M'),
                    'from' => $d->format('Y-m-d') . ' 00:00:01',
                    'to' => $d->format('Y-m-d') . ' 23:59:59'
                ];
            }

            $conceptsWithFlow = DB::table('cash_flow')
                ->join('currencies', 'currency_id', '=', 'currencies.id')
                ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                ->select('cash_concepts.id', 'cash_concepts.concept', 'cash_concepts.status')
                ->where ('cash_flow.datetime', '>=', $dateFrom->format('Y-m-d') )
                ->where('cash_flow.datetime', '<=', $dateTo->format('Y-m-d') )
                    ->where('cash_flow.status', '=', 1 )
                    ->when($currency_id, function ($query, $currency_id) {
                        return $query->where('cash_flow.currency_id','=', (int)$currency_id);
                    })
                    ->when($type, function ($query, $type) {
                        return $query->where('cash_flow.type','=', (int)$type);
                    })
                ->groupBy('cash_concepts.id', 'cash_concepts.concept', 'cash_concepts.status')
                ->orderBy('concept', 'desc')
                ->get();
    
            $data= [];
            foreach ($conceptsWithFlow as $c) {
                $hasFlows = true;
                $concept_id = (int)$c->id;
                $concept = $c->concept;
    
                $total =[];
                foreach ($days as $d) {
    
                    $fromSQL = $d['from'];
                    $toSQL = $d['to'];
    
                    $totalFlow = DB::table('cash_flow')
                    ->join('currencies', 'currency_id', '=', 'currencies.id')
                    ->join('cash_concepts', 'cash_concept_id', '=', 'cash_concepts.id')
                    // ->join('cash_categories', 'cash_concepts.category_id', '=', 'cash_categories.id')
                    ->selectRaw('SUM(cash_flow.amount) as total')
                    ->where ('cash_flow.datetime', '>=', $fromSQL )
                    ->where('cash_flow.datetime', '<=', $toSQL )
                        ->where('cash_flow.status', '=', 1 )
                        ->where('cash_concepts.id', '=', $concept_id )
                        ->when($currency_id, function ($query, $currency_id) {
                            return $query->where('cash_flow.currency_id','=', (int)$currency_id);
                        })
                        ->when($type, function ($query, $type) {
                            return $query->where('cash_flow.type','=', (int)$type);
                        })
                    ->get();
    
                    $total[] = $totalFlow->sum('total');
    
                }
                $data[] = [
                    'label' => $concept,
                    'data' => $total
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
            'has_flows' => $hasFlows,
            'legends' => $legends,
            'data' => $data
        ]);

    }

}

