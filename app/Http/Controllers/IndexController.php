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
        foreach($datasets as $dataset){
            if($name === $dataset->name){
                $canAccess = true;
            }
        }

        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        foreach($datasets as $dataset){
            if($name === $dataset->name){
                $canAccess = true;
            }
        }
        if(!$canAccess){
            abort(401);
        }
        $data = Elasticsearch::search(['index' => $name, 'size' => $quantity,"from"=>$offset]);
        $data = Functions::parseIndexJson($data);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
