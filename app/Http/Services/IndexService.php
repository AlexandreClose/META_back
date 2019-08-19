<?php


namespace App\Http\Services;


use App\dataset;
use App\Http\Controllers\DatasetController;
use Illuminate\Http\Request;

class IndexService
{
    public function checkRights(Request $request)
    {
        $name = $request->get('name');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false);
        $canAccess = false;
        $datasetId = null;
        $dataset = null;

        foreach ($datasets as $data) {
            if ($name === $data->databaseName) {
                $canAccess = true;
                break;
            }
        }
        if (!$canAccess) {
            abort(false);
        }

        $AccessibleColumns = DatasetController::getAllAccessibleColumnsFromADataset($request, dataset::where('databaseName', $name)->first());

        $columns = [];
        $canAccess = false;
        if ($request->get('columns') != null) {
            foreach ($AccessibleColumns as $column) {
                if (in_array($column->name, $request->get('columns'))) {
                    array_push($columns, $column->name);
                    $canAccess = true;
                }
            }
        }
        if (!$canAccess) {
            abort(false);
        }

        return $columns;
    }
}
