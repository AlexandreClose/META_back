<?php


namespace App\Http\Services;


use App\dataset;
use App\Http\Controllers\DatasetController;
use DateTime;
use Exception as ExceptionAlias;
use Illuminate\Http\Request;
use Elasticsearch;

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
                $datasetId = $data->id;
                $canAccess = true;
                break;
            }
        }
        if (!$canAccess) {
            return (false);
        }
        return $datasetId;
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

    public function do_stats(array $columns, array $data)
    {
        $stats = [];
        foreach ($columns["data"] as $column) {
            $occurrences = [];
            foreach ($data as $element) {
                $pathData = $element;
                $tmp = [];
                foreach (explode("+", $columns["pivot"]) as $col) {
                    $pathPivot = $element;
                    foreach (explode(".", $col) as $field) {
                        $pathPivot = $pathPivot[$field];
                    }
                    array_push($tmp, $pathPivot);
                }
                $pathPivot = implode("+", $tmp);

                if ($columns["isDate"]) {
                    try {
                        $d = new DateTime($pathPivot);
                        $pathPivot = date('Y-m-d\TH:i:s.Z\Z', floor($d->getTimestamp() / ($columns["step"] * 3600)) * ($columns["step"] * 3600));
                    } catch (ExceptionAlias $e) {
                    }
                }

                foreach (explode(".", $column) as $field) {
                    $pathData = $pathData[$field];
                }
                $pathData = (float)$pathData;
                if (!array_key_exists($pathPivot, $stats) or !array_key_exists($column, $stats[$pathPivot]["stats"])) {
                    if (array_key_exists($pathPivot, $stats)) {
                        $element = $stats[$pathPivot];
                    }
                    $occurrences[$pathPivot] = [];
                    $result = $this->diff_occurrences($occurrences[$pathPivot], $pathData, 0);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $element["stats"][$column] = [
                        "min" => $pathData,
                        "max" => $pathData,
                        "avg" => $pathData,
                        "sum" => $pathData,
                        "count" => 1,
                        "DiffOcc" => $result["Count"]];
                    $stats[$pathPivot] = $element;

                } else {
                    $s = $stats[$pathPivot];
                    $oldStats = $s["stats"][$column];
                    array_merge_recursive($stats[$pathPivot], $element);

                    $result = $this->diff_occurrences($occurrences[$pathPivot], $pathData, $oldStats["DiffOcc"]);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $stats[$pathPivot]["stats"][$column] = [
                        "min" => min($pathData, $oldStats["min"]),
                        "max" => max($pathData, $oldStats["max"]),
                        "avg" => ($pathData + $oldStats["avg"]) / 2,
                        "sum" => ($pathData + $oldStats["sum"]),
                        "count" => ($oldStats["count"] + 1),
                        "DiffOcc" => ($result["Count"])];
                }
            }
        }
        return $stats;
    }

    private function diff_occurrences(array $occurrences, $element, $i)
    {
        if (!in_array($element, $occurrences)) {
            array_push($occurrences, $element);
            $i++;
        }
        return ["Count" => $i, "Occurrences" => $occurrences];
    }

    public function do_join(int $i, array $data, array $columns)
    {
        $newData = [];
        foreach ($data[$i - 1] as $entry) {
            $path = $entry;
            foreach (explode(".", $columns[0]) as $field) {
                $path = $path[$field];
            }
            foreach ($data[$i] as $newEntry) {
                $newPath = $newEntry;
                foreach (explode(".", $columns[1]) as $field) {
                    $newPath = $newPath[$field];
                }
                if ($path == $newPath) {
                    array_push($newData, array_replace($entry, $newEntry));
                }
            }
        }
        $data[$i] = $newData;
        return $data;
    }

    public function liteIndexService(Request $request){
        $checkRights = (new IndexService)->checkRights($request, false);
        if ($checkRights == false) {
            $columnFilter = null;
            abort(403);
        } else {
            $columnFilter = $checkRights;
        }

        $name = $request->get("name");
        if ((bool)dataset::select('realtime')->where('databaseName', $name)->first()["realtime"]) {
            $request["columns"] = $columnFilter;
            $data = $this->getLiteIndexInflux($request);

        }else{
            $request["columns"] = $columnFilter;
            $data = $this->getLiteIndexElastic($request);
        }
        return response($data, 200);
    }

    private function getLiteIndexElastic(Request $request){

        $elasticSearchService = new ElasticSearchService($request);
        $minuteQuery = $elasticSearchService->getMinuteFilter();
        $fullDayQuery = $elasticSearchService->getWeekdayFilter();

        $body = $elasticSearchService->getTimeFilter([], $minuteQuery, $fullDayQuery);

        $data = Elasticsearch::search(['index' => $request->get("name"), '_source' => $request->get("columns"),
            'size' => $request->get('size'),
            "from" => $request->get('offset'),
            "body" => $body]);

        return $data;
    }

    private function getLiteIndexInflux(Request $request)
    {
        $result = (new influxDBService)->doFullQuery($request);
        $hits = [];
        foreach ($result as $element) {
            $hit = ["_index" => $request["name"], "_source" => $element];
            array_push($hits, $hit);
        }
        $result = ["hits" => ["total" => sizeof($result), "hits" => $hits]];
        return $result;
    }

    public function getLast(Request $request)
    {
        if ($this->checkRights($request, false) == false) {
            abort(403);
        }

        $client = (new InfluxDBService)->getClient();

        $select = 'last("' . implode('"), last("', $request["columns"]) . '")';
        $from = $request["name"];
        $groupBy = '"' . implode('", "', explode("+", $request["groupby"])) . '"';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $client->query(env("INFLUXDB_DBNAME"), 'SELECT ' . $select . ' FROM ' . $from . ' GROUP BY ' . $groupBy)->getPoints();

        return response($result, 200);
    }
}
