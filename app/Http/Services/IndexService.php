<?php


namespace App\Http\Services;


use App\dataset;
use App\Http\Controllers\DatasetController;
use Illuminate\Http\Request;

class IndexService
{
    public function checkRights(Request $request, bool $validate)
    {
        $dataset = $this->checkRightsOnDataset($request, $validate);
        $columns = $this->checkRightsOnColumns($request);
        if (!($dataset and $columns)) {
            return false;
        }
        return $columns;
    }

    public function checkRightsOnDataset(Request $request, bool $validate)
    {
        $name = $request->get('name');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), $validate);
        $canAccess = false;
        $datasetId = null;
        $dataset = null;

        foreach ($datasets as $data) {
            if ($name === $data->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
                break;
            }
        }
        if (!$canAccess) {
            return (false);
        }
        return $dataset;
    }

    public function checkRightsOnColumns(Request $request)
    {
        $name = $request->get('name');
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
            return (false);
        }
        return $columns;
    }
}
