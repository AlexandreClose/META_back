<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function getAllIndex()
    {
        $stats = Elasticsearch::indices()->stats();
        $indexes = $stats['indices'];
        return response($indexes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getIndexByName(Request $request, $name, $quantity = 5,$offset = 0, $date_col = null, $start_date = null, $end_date = null)
    {
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        $datasetId;
        foreach($datasets as $dataset){
            if($name === $dataset->databaseName){
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }

        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        foreach($datasets as $dataset){
            if($name === $dataset->databaseName){
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }
        if(!$canAccess){
            abort(403);
        }

        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, dataset::where('id', $datasetId)->first());
        $columnFilter = []; 
        
        foreach($columns as $column){
            array_push($columnFilter, $column->name);
        }
        //dd($columnFilter);
        $body = [];
        if($date_col != null && $start_date != null){
            $body = ['query' => ['bool' => ['filter' => ['range ' => [$date_col => ['gte' => $start_date, 'lte' => $start_date]]]]]];
        } elseif ($date_col != null && $start_date != null && $end_date != null) {
            $body = ['query' => ['range ' => [$date_col => ['gte' => $start_date, 'lte' => $end_date]]]];
        }
        //  dd(json_encode($body));
        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter, 'size' => $quantity,"from"=>$offset]);
        //error_log(dd($data));
        //$data = Functions::parseIndexJson($data);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}