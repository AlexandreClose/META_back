<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;

class IndexController extends Controller
{
public function getAllIndex(){
    $stats = Elasticsearch::indices()->stats();
    $indexes = $stats['indices'];
    return response($indexes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
}

public function getIndexByName($name,$quantity=5){
    $data = Elasticsearch::search(['index' => $name,'size'=>$quantity]);
    $data = Functions::parseIndexJson($data);
    return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
}

public function getIndexDataByNameAndId($name,$id){
    $data = Elasticsearch::search(['index' => $name,'body'=>['query'=>['match'=>['_id'=>$id]]]]);
    $data = Functions::parseIndexJson($data);
    return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
}

public function getIndexToValidate(){
        $data = [];
        $datasets = dataset::where('validated',false)->orderBy("created_date","desc")->take(5)->get();
        foreach($datasets as $dataset){
            $dataset = json_decode($dataset);
            array_push($data,$dataset);
        }
        dd($data);
    }
}
