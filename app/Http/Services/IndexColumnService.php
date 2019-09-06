<?php


namespace App\Http\Services;


use DateTime;
use Exception as ExceptionAlias;

class IndexColumnService
{
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
                        $pathPivot = $pathPivot[$field];
                    }
                    array_push($tmp, $pathPivot);
                }
                $pathPivot = implode("+", $tmp);

                if ($columns["isDate"]) {
                    try {
                        $d = new DateTime($pathPivot);
                        $pathPivot = date('Y-m-d\TH:i:s.Z\Z', floor($d->getTimestamp() / ($columns["step"] * 60)) * ($columns["step"] * 60));
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
                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, 0);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $element["stats"][$column] = [
                        "min" => $pathData,
                        "max" => $pathData,
                        "avg" => $pathData,
                        "sum" => $pathData,
                        "count" => 1,
                        "DiffOcc" => 1,
                        "DiffSum" => $pathData,
                        "DiffAvg" => $pathData];

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
                                "FilSum" => $pathData,
                                "FilAvg" => $pathData]);
                            $element["stats"][$column] = $mergedArray;
                        }

                    }

                    $stats[$pathPivot] = $element;
                } else {
                    $s = $stats[$pathPivot];
                    $oldStats = $s["stats"][$column];
                    array_merge_recursive($stats[$pathPivot], $element);

                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, $oldStats["DiffOcc"]);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    if ($result["isSum"]) {
                        $oldStats["DiffSum"] += $pathData;
                        $oldStats["DiffAvg"] = ($oldStats["DiffAvg"] + $pathData) / 2;
                    }
                    $stats[$pathPivot]["stats"][$column] = [
                        "min" => min($pathData, $oldStats["min"]),
                        "max" => max($pathData, $oldStats["max"]),
                        "avg" => ($pathData + $oldStats["avg"]) / 2,
                        "sum" => ($pathData + $oldStats["sum"]),
                        "count" => ($oldStats["count"] + 1),
                        "DiffOcc" => ($result["Count"]),
                        "DiffSum" => $oldStats["DiffSum"],
                        "DiffAvg" => $oldStats["DiffAvg"]];
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
                                "FilCount" => $oldStats["FilCount"] + 1,
                                "FilSum" => $oldStats["FilSum"] + $pathData,
                                "FilAvg" => $oldStats["FilAvg"]];
                        } else {
                            $newArray = [
                                "FilCount" => $oldStats["FilCount"],
                                "FilSum" => $oldStats["FilSum"],
                                "FilAvg" => ($pathData + $oldStats["FilAvg"]) / 2];
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
