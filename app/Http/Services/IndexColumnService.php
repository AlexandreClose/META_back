<?php


namespace App\Http\Services;


use DateTime;
use Exception as ExceptionAlias;

class IndexColumnService
{
    private static function multi_implode($array, $glue)
    {
        $ret = '';
        foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= IndexColumnService::multi_implode($item, $glue) . $glue;
            } else {
                $ret .= (string)$item . $glue;
            }
        }
        return $ret;
    }


    public static function do_stats(array $columns, array $data)
    {
        $stats = [];
        foreach ($columns["data"] as $column) {
            $occurrences = [];
            $filterOccurrences = [];
            foreach ($data as $element) {
                $pathData = $element;
                $tmp = [];
                foreach (explode("+", $columns["pivot"]) as $col) {
                    $pathPivot = $element;
                    foreach (explode(".", $col) as $field) {
                        try {
                            $pathPivot = $pathPivot[$field];
                        } catch (ExceptionAlias $e) {
                            continue 3;
                        }
                    }
                    if (is_array($pathPivot)) {
                        $pathPivot = IndexColumnService::multi_implode($pathPivot, "");
                    }
                    array_push($tmp, $pathPivot);
                }
                $pathPivot = implode("+", $tmp);

                if ($columns["isDate"]) {
                    try {
                        $d = new DateTime($pathPivot);
                        $pathPivot = date('Y-m-d\TH:i:s.Z\Z', floor($d->getTimestamp() / ($columns["step"] * 60)) * ($columns["step"] * 60));
                    } catch (ExceptionAlias $e) {
                        continue 1;
                    }
                }

                foreach (explode(".", $column) as $field) {
                    try {
                        $pathData = $pathData[$field];
                    } catch (ExceptionAlias $e) {
                        continue 2;
                    }
                }

                $pathData = (float)$pathData;
                if (!array_key_exists($pathPivot, $stats) or !array_key_exists($column, $stats[$pathPivot]["stats"])) {
                    if (array_key_exists($pathPivot, $stats)) {
                        $element = $stats[$pathPivot];
                    }
                    $occurrences[$pathPivot] = [];
                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, 0);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $element["stats"][$column] = [
                        "min" => round($pathData, 2),
                        "max" => round($pathData, 2),
                        "avg" => round($pathData, 2),
                        "sum" => round($pathData, 2),
                        "count" => 1,
                        "DiffOcc" => 1,
                        "DiffSum" => round($pathData, 2),
                        "DiffAvg" => round($pathData, 2)];

                    if (array_key_exists("filter", $columns)) {
                        $filterData = $element;
                        foreach (explode(".", $columns["filter"]) as $field) {
                            $filterData = $filterData[$field];
                        }
                        $param = [];
                        $filterDataResult = IndexColumnService::is_new_filterData($param, $filterData);
                        $filterOccurrences[$pathPivot] = $filterDataResult[1];
                        if ($filterDataResult[0]) {
                            $mergedArray = array_merge_recursive($element["stats"][$column], [
                                "FilCount" => 1,
                                "FilSum" => round($pathData, 2),
                                "FilAvg" => round($pathData, 2)]);
                            $element["stats"][$column] = $mergedArray;
                        }

                    }
                    $stats[$pathPivot] = $element;
                } else {
                    $s = $stats[$pathPivot];
                    $oldStats = $s["stats"][$column];

                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, $oldStats["DiffOcc"]);

                    $occurrences[$pathPivot] = $result["Occurrences"];

                    if ($result["isSum"]) {
                        $oldStats["DiffSum"] += round($pathData, 2);
                        $oldStats["DiffAvg"] = round(($oldStats["DiffAvg"] + $pathData) / 2, 2);
                    }
                    $stats[$pathPivot]["stats"][$column] = [
                        "min" => round(min($pathData, $oldStats["min"]), 2),
                        "max" => round(max($pathData, $oldStats["max"]), 2),
                        "avg" => round(($pathData + $oldStats["avg"]) / 2, 2),
                        "sum" => round(($pathData + $oldStats["sum"]), 2),
                        "count" => round(($oldStats["count"] + 1), 2),
                        "DiffOcc" => round(($result["Count"]), 2),
                        "DiffSum" => round($oldStats["DiffSum"], 2),
                        "DiffAvg" => round($oldStats["DiffAvg"], 2)];
                    if (array_key_exists("filter", $columns)) {
                        $filterData = $element;
                        foreach (explode(".", $columns["filter"]) as $field) {
                            $filterData = $filterData[$field];
                        }
                        $param = $filterOccurrences[$pathPivot];
                        $filterDataResult = IndexColumnService::is_new_filterData($param, $filterData);
                        $filterOccurrences[$pathPivot] = $filterDataResult[1];
                        if ($filterDataResult[0]) {
                            $newArray = [
                                "FilCount" => round($oldStats["FilCount"] + 1, 2),
                                "FilSum" => round($oldStats["FilSum"] + $pathData, 2),
                                "FilAvg" => round($oldStats["FilAvg"], 2)];
                        } else {
                            $newArray = [
                                "FilCount" => round($oldStats["FilCount"], 2),
                                "FilSum" => round($oldStats["FilSum"], 2),
                                "FilAvg" => round(($pathData + $oldStats["FilAvg"]) / 2, 2)];
                        }
                        $mergedArray = array_merge_recursive($stats[$pathPivot]["stats"][$column], $newArray);
                        $stats[$pathPivot]["stats"][$column] = $mergedArray;
                    }
                }
            }
        }
        return $stats;
    }

    private static function diff_occurrences(array $occurrences, $element, $i)
    {
        $isSum = false;
        if (!in_array($element, $occurrences)) {
            array_push($occurrences, $element);
            $i++;
            $isSum = true;
        }
        return ["Count" => $i, "isSum" => $isSum, "Occurrences" => $occurrences];
    }

    private static function is_new_filterData(Array $filter, $filterData)
    {
        if (!in_array($filterData, $filter)) {
            array_push($filter, $filterData);
            return [true, $filter];
        }
        return [false, $filter];
    }
}


