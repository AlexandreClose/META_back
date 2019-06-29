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
        $representation = representation_type::where('name', $request->get('representation'))->first();
        if($representation == null){
            error_log("missing representation");
            abort(409);
        }
        $analyse->$representation;
        $analyse->shared = $request->get('shared');
        $analyse->owner_id = $user->uuid;
        $analyse->description = $request->get('description');
        $analyse->visibility = $request->get('name');
        $theme_name = theme::where('name', $request->get('theme_name'))->first();
        if($representation == null){
            error_log("missing theme");
            abort(409);
        }
        $analyse->save();
        
        $analyse = analysis::where('name')->first();

        return $analyse;
    }

    public function createAnalysisColumn(Request $request, $id){
        $user = $request->get('user');
        $analyse = analysis::where('id', $id);
        $analysis_columns = [];
        $analysis_columns_data = json_decode($request->get('analysis_columns'));
        foreach($analysis_columns as $analysis_column_data){
            $analysis_column = new analyse_column();
            $analysis_column->column_id = $analysis_column_data['column_id'];
            $analysis_column->analysis_id = $analysis_column_data['analysis_id'];
            $analysis_column->color_code = $analysis_column_data['color_code'];
            $analysis_column->usage = $analysis_column_data['usage'];
            $analysis_column->save();
        }
    }

    public function getAnalysisById(Request $request, $id){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        
        $analysis = analysis::where('id', $id);
        foreach($analysis->columns as $column){
            if(!array_search(dataset::where('id', $column->dataset_id), $datasets)){
                abort(403);
            }
        }
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
