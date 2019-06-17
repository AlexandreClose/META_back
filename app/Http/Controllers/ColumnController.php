<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\column;
use App\dataset;

class ColumnController extends Controller
{
    function createColumn(Request $request){

        $postbody = "";

        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        }
        else{
            abort(400);
        }



        $columns = [];


        foreach($postbody as $element){
            $dataset = dataset::where('id', '=', $element["datasetId"])->first();
            if($dataset === null){
                abort(404);
            }

            if($element["name"] == null || $element["datatype"] == null || $element["visibility"] == null || $element["datasetId"] == null){
                abort(400);
            }

            $verif = column::where('dataset_id', '=', $element["datasetId"])->where('name','=',$element['name'])->get();
            if(count($verif) >0){
                abort(409);
            }
            $column = new column();
            $column->name = $element["name"];
            $column->main = $element["main"];
            $column->data_type_name = $element["datatype"];
            $column->visibility = $element["visibility"];
            $column->dataset_id = $element["datasetId"];

            array_push($columns,$column);


        }
        foreach($columns as $item){
            $item->save();
        }



    }
}
