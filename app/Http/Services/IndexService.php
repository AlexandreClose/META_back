<?php
/** @noinspection PhpUnusedAliasInspection */

/** @noinspection PhpUndefinedClassInspection */


namespace App\Http\Services;


use App\dataset;
use App\Http\Controllers\DatasetController;
use DateTime;
use Exception as ExceptionAlias;
use Illuminate\Http\Request;
use /** @noinspection PhpUnused */
    Elasticsearch;

class IndexService
{
    public static function getInPointInPolygonService(Request $request)
    {
        $checkRights = IndexService::checkRights($request, false);
        if ($checkRights == false) {
            $columns = null;
            abort(403);
        } else {
            $columns = $checkRights;
        }

        $nameFilter = $request->get('nameFilter');

        $dataFilters = Elasticsearch::search(['index' => $nameFilter, '_source' => $request->get('filterColumn'),
            'size' => $request->get('size'),
            "from" => $request->get('offset')])["hits"]["hits"];


        $keyId = 1;
        $result = [];
        $doStats = (bool)$request->get('stats')["do_stats"];
        foreach ($dataFilters as $dataFilter) {
            $path = $dataFilter["_source"];
            foreach (explode(".", $request->get('targetColumn')) as $field) {
                $path = $path[$field];
            }
            $polygon = $path[0][0];

            $body = ["query" => ["bool" => [
                "must" => ["match_all" => (object)null],
                "filter" => ["geo_polygon" => ["geometry.coordinates" => ["points" => $polygon]]]]]];


            $name = $request->get('name');
            $data = Elasticsearch::search(['index' => $name, '_source' => $columns,
                'size' => $request->get('size'),
                "from" => $request->get('offset'),
                "body" => $body]);


            if ($doStats) {
                foreach ($data["hits"]["hits"] as $element) {
                    $element = $element["_source"];
                    $element["KeyId"] = $keyId;
                    $element["geometry"]["coordinates"] = $polygon;
                    array_push($result, $element);
                }
                $keyId++;
            } else {
                $NewData = [];
                foreach ($data["hits"]["hits"] as $element) {
                    $element = $element["_source"];
                    array_push($NewData, $element);
                }
                array_push($result, $NewData);
            }
        }

        $doJoin = (bool)$request->get('join')["do_join"];
        if ($doJoin) {
            $subRequest = $request["join"]["request"];
            $subRequest["stats"]["do_stats"] = false;
            $subRequest["user"] = $request->get("user");
            $subRequest = new Request($subRequest);
            $subResult = IndexService::joinService($subRequest)->getOriginalContent();
            $result = IndexService::do_join(1, [$subResult, $result], $request["join"]["joining"])[1];
        }
        if ($doStats) {
            $columns = $request->get("stats")["columns"];
            $columns["pivot"] = "KeyId";
            $columns["isDate"] = false;
            $result = IndexColumnService::do_stats($columns, $result);
        }
        return response($result);
    }

    public static function checkRights(Request $request, bool $validate, String $name = null)
    {
        $dataset = IndexService::checkRightsOnDataset($request, $validate, $name);
        $columns = IndexService::checkRightsOnColumns($request, $name);
        if (!($dataset and $columns)) {
            return false;
        }
        return $columns;
    }

    public static function checkRightsOnDataset(Request $request, bool $validate, String $name = null)
    {
        if ($name == null) {
            $name = $request->get('name');
        }

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

    private static function checkRightsOnColumns(Request $request, String $name = null)
    {
        if ($name == null) {
            $name = $request->get('name');
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
            return (false);
        }
        return $columns;
    }

    public static function joinService(Request $request)
    {
        $data = [];
        $datasets = $request["datasets"];

        for ($i = 0; $i < sizeof($datasets); $i++) {
            $body = $datasets[$i];
            $body["user"] = $request["user"];
            $subRequest = new Request($body);
            $results = IndexService::liteIndexService($subRequest)->getOriginalContent()["hits"]["hits"];
            $temp = [];
            foreach ($results as $result) {
                array_push($temp, $result["_source"]);
            }
            array_push($data, $temp);
            if ($i >= 1) {
                $columns = $request["joining"][$i - 1];
                $data = IndexService::do_join($i, $data, $columns);
            }
        }

        if (!$request["stats"]["do_stats"]) {
            $result = response($data[sizeof($datasets) - 1], 200);
        } else {
            $result = response(IndexColumnService::do_stats($request["stats"]["columns"]
                , $data[sizeof($datasets) - 1]), 200);
        }
        return $result;
    }

    public static function liteIndexService(Request $request)
    {
        $checkRights = IndexService::checkRights($request, false);
        if ($checkRights == false) {
            $columnFilter = null;
            abort(403);
        } else {
            $request["columns"] = $checkRights;
        }

        $name = $request->get("name");
        if ((bool)dataset::select('realtime')->where('databaseName', $name)->first()["realtime"]) {
            $data = IndexService::getLiteIndexInflux($request);
        } else {
            $data = IndexService::getLiteIndexElastic($request);
        }
        return response($data, 200);
    }

    private static function getLiteIndexInflux(Request $request)
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

    private static function getLiteIndexElastic(Request $request)
    {

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

    private static function do_join(int $i, array $data, array $columns)
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
        array_unique($newData, SORT_REGULAR);
        $data[$i] = $newData;
        return $data;
    }

    public static function getLast(Request $request)
    {
        if (IndexService::checkRights($request, false) == false) {
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
