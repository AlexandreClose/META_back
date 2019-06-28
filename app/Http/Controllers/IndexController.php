<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;
use App\user;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function getAllIndex()
    {
        $stats = Elasticsearch::indices()->stats();
        $indexes = $stats['indices'];
        return response($indexes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllDateFieldsFromAnIndexFromItsName(Request $request, $name)
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

        $data = [
            'index' => $name
        ];
        $return = Elasticsearch::indices()->getMapping($data);

        //dd($return);
        $date_fields = [];
        foreach ($return[$name]['mappings']['doc']['properties'] as $field => $field_data) {
            //dd($field_data['type']);
            if(array_key_exists('type', $field_data) && $field_data['type'] == "date"){
                array_push($date_fields, $field);
            }
            if($field == "properties"){
                foreach ($field_data["properties"] as $inner_field => $inner_field_data) {
                    if(array_key_exists('type', $inner_field_data) && $inner_field_data['type'] == "date"){
                        array_push($date_fields, "properties".".".$inner_field);
                    }
                }
            }
        }
        dd($date_fields);
        return $return;
    }

    public static function getIndexByNameQuantityAndOffset(Request $request, $name, $quantity = 5,$offset = 0, $date_col = null, $start_date = null, $end_date = null)
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
        if($date_col != null && $start_date == null && $end_date == null){
            $body = ['sort' => [[$date_col => ['order' => 'desc']]]];
        } elseif($date_col != null && $start_date != null && $end_date == null){
            $body = ['sort' => [$date_col => ['order' => 'desc']],'query' => ['range' => [$date_col => ['gte' => $start_date, 'lte' => $start_date]]]];
        } elseif ($date_col != null && $start_date != null && $end_date != null) {
            $body = ['sort' => [$date_col => ['order' => 'desc']],'query' => ['range' => [$date_col => ['gte' => $start_date, 'lte' => $end_date]]]];
        }
        //dd(json_encode([[$date_col => ['order' => 'desc']]]));
        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter, 'size' => $quantity,"from"=>$offset,"body"=>$body]);
        //error_log(dd($data));
        //$data = Functions::parseIndexJson($data);
        return $data;
    }

    public function getIndexByName(Request $request, $name, $quantity = 5,$offset = 0, $date_col = null, $start_date = null, $end_date = null)
    {
        $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, $quantity, $offset, $date_col, $start_date, $end_date);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getIndexFile(Request $request, $name){
        $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, 1);
        $lineCnt = $data['hits']['total'];
        $file = fopen($name.".json", "w");
        $iterCount = $lineCnt / 1000;
        for($i = 0; i < $iterCount ; $i++){
            $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, 1000, i*1000);
            fwrite($file, $data);
        }
        fclose($file);
    }
}