<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\analysis;
use App\representation_type;
use App\theme;
use App\analyse_column;
use App\DatasetController;

class AnalyseController extends Controller
{
    public function saveAnalyse(Request $request) {
        $user = $request->get('user');
        $analyse = new analysis();
        $analyse->name = $request->get('name');
        $representation = representation_type::where('name', $request->get('representation_type'))->first();
        if($representation == null){
            error_log("missing representation");
            abort(400, "bad representation");
        }
        $analyse->$representation;
        $analyse->shared = $request->get('shared');
        $analyse->isStats = $request->get('isStats');
        $analyse->owner_id = $user->uuid;
        $analyse->description = $request->get('description');
        $analyse->body = $request->get('body');
        $analyse->usage = $request->get('usage');
        $analyse->visibility = $request->get('name');
        $theme_name = theme::where('name', $request->get('theme_name'))->first();
        if($theme_name == null){
            error_log("missing theme");
            abort(400, "missing theme or theme don't exist");
        }
        $analyse->save();
        
        $analyse = analysis::where('name')->first();

        createAnalysisColumn($request, $id);

        return response($analyse)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public static function createAnalysisColumn($request, $id){
        $user = $request->get('user');
        $analyse = analysis::where('id', $id);
        $analysis_columns = [];
        $analysis_columns_data = json_decode($request->get('analysis_columns'));
        foreach($analysis_columns as $analysis_column_data){
            $analysis_column = new analyse_column();
            $analysis_column->field = $analysis_column_data['field'];
            $analysis_column->analysis_id = $analysis_column_data['analysis_id'];
            $analysis_column->databaseName = $analysis_column_data['databaseName'];
            $analysis_column->color_code = $analysis_column_data['color_code'];
            $analysis_column->usage = $analysis_column_data['usage'];
            $analysis_column->save();
        }
    }

    public function getAnalysisById(Request $request, $id){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        
        $analysis = analysis::with('fields')->where('id', $id)->first();
        foreach($analysis->fields as $field){
            if(array_search(dataset::where('databaseName', $field->databaseName), $datasets) == null){
                abort(403);
            }
        }
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function deleteAnalysis(Request $request, $id){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        
        $analysis = analysis::where('id', $id)->first();
        if(($analysis->owner_id != $user->uuid && $user->role != "Administrateur")|| $analyse != null){
            abort(403);
        }
        $analysis->delete();
    }

    public function getAllAccessibleAnalysis(Request $request, $shared = false){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $analysis = $user->analysis();

        foreach($analysis as $key=>$analyse){
            foreach($analysis->columns as $column){
                if(!$shared && (!$analyse->shared || !array_search(dataset::where('id', $column->dataset_id), $datasets))){
                    unset($analysis[$key]);
                } else if($shared && !$analyse->shared && !array_search(dataset::where('id', $column->dataset_id), $datasets)){
                    unset($analysis[$key]);
                }
            }
        }
        
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllSavedAnalysis(Request $request){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $analysis = $user->saved_analysis();
        foreach($analysis as $key=>$analyse){
            foreach($analysis->columns as $column){
                if(($user->uuid != $analyse->owner_id && !$analysis->shared) || !array_search(dataset::where('id', $column->dataset_id), $datasets)){
                    unset($analysis[$key]);
                }
            }
        }
        
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
