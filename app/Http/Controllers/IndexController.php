<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;

class IndexController extends Controller
{
    public function getAllIndex()
    {
        $stats = Elasticsearch::indices()->stats();
        $indexes = $stats['indices'];
        return response($indexes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getIndexByName(Request $request, $name, $quantity = 5,$offset = 0)
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
                $canAccess = true;
            }
        }
        if(!$canAccess){
            abort(403);
        }

        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, dataset::where('id', $datasetId));
        $columnFilter = [];
        foreach($columns as $column){
            array_push($columnFilter, $column->name);
        }
        error_log($columnFilter);
        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter,'size' => $quantity,"from"=>$offset]);
        $data = Functions::parseIndexJson($data);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
