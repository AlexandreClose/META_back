<?php

/** @noinspection PhpUndefinedClassInspection */


namespace App\Http\Services;


use App\dataset;
use Illuminate\Http\Request;
use /** @noinspection PhpUnusedAliasInspection */
    Elasticsearch;

class ColumnService
{
    public static function getStatsService(Request $request)
    {
        $checkRights = (new IndexService)->checkRights($request, false);
        if ($checkRights == false) {
            $columns = null;
            abort(403);
        } else {
            $request["columns"] = $checkRights;
        }

        $name = $request->get('name');
        if ((bool)dataset::select('realtime')->where('databaseName', $name)->first()["realtime"]) {
            $data = ColumnService::getStatsInflux($request);
        } else {
            $data = ColumnService::getStatsElastic($request);
        }
        return response($data, 200);
    }

    private static function getStatsInflux(Request $request)
    {
        $result = (new InfluxDBService)->doFullQuery($request);

        $column = ["pivot" => $request->get("groupby"), "isDate" => false, "data" => $request["columns"]];
        $stats = IndexColumnService::do_stats($column, $result);

        $hits = [];
        foreach (array_keys($stats) as $key) {
            $hit = $stats[$key]["stats"];
            $hit["key"] = $key;
            array_push($hits, $hit);
        }

        $result = ["hits" => ["total" => sizeof($result), "hits" => []], "aggregations" => ["codes" => ["buckets" => $hits]]];
        return $result;
    }

    private static function getStatsElastic(Request $request)
    {

        $ElasticSearchService = new ElasticSearchService($request);

        $minuteQuery = $ElasticSearchService->getMinuteFilter();
        $fullDayQuery = $ElasticSearchService->getWeekdayFilter();

        $body = $ElasticSearchService->getTimeFilter([], $minuteQuery, $fullDayQuery);


        $aggs = [];
        foreach ($request["columns"] as $column) {
            $aggs[$column] = ["stats" => ["field" => $column]];
        }

        $group_by_column = $request->get('groupby');
        if ($group_by_column) {
            $body["aggs"] = ["codes" => ["terms" => ["field" => $group_by_column, "size" => 10000], "aggs" => $aggs]];
        } else {
            $body["aggs"] = $aggs;
        }

        $data = Elasticsearch::search(['index' => $request["name"],
            'size' => 0,
            "body" => $body]);
        return $data;
    }
}
