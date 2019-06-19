<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\column;
use App\dataset;
use App\theme;
use App\data_type;
use App\user;
use App\colauth_user;

class ColumnController extends Controller
{
    function createColumn(Request $request){

        $role = $request->get('user')->role;
        if($role != "Référent-Métier" && $role != "Administrateur"){
            abort(403);
        }
        $postbody = "";

        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        }
        else{
            error_log("no body in requests");
            abort(400);
        }



        $columns = [];
        foreach($postbody as $element){
            $dataset = dataset::where('id', '=', $element["datasetId"])->first();
            if($dataset === null){
                error_log("no dataset with that id");
                abort(404);
            }

            if($element["name"] == null || $element["datatype"] == null || $element["visibility"] == null || $element["datasetId"] == null){
                error_log("missing name, datatype, visibility or datasetId");
                abort(400);
            }

            $verif = column::where('dataset_id', '=', $element["datasetId"])->where('name','=', $element['name'])->get();
            if(count($verif) > 0){
                error_log("column already exists");
                abort(409);
            }
            $column = new column();
            $column->name = $element["name"];
            $column->main = $element["main"];
            $datatype = data_type::where('name', $element['datatype']);
            if($datatype == null){
                error_log($datatype);
                error_log(element['datatype']);
                abort(400);
            }
            $column->data_type_name = $element["datatype"];
            $column->visibility = $element["visibility"];
            $column->dataset_id = $element["datasetId"];
            $theme = theme::where('name', $element["theme"])->first();
            if($theme == null){
                error_log($theme);
                error_log($element["theme"]);
                abort(400);
            }
            $column->themeName = $element["theme"];

            $column->save();
            $users = $element['users'];
            $column = column::where('name', $element["name"])->where('dataset_id', $element["datasetId"]);
            foreach($users as $user_id){
                $auth_user = user::where('uuid',$user)->first();
                if($auth_user == null){
                    continue;
                }
                $auth_users = new colauth_user();
                $auth_users->id = $column->id;
                $auth_users->uuid = $auth_user->uuid;
                $auth_users->save();
            }
            //array_push($columns,$column);


        }
        /*
        foreach($columns as $item){
            $item->save();
        }*/



    }
}
