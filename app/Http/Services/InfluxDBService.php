<?php


namespace App\Http\Services;


use DateTime;
use Exception as ExceptionAlias;
use Illuminate\Http\Request;
use InfluxDB\Client;

class InfluxDBService
{
    public function doFullQuery(Request $request)
    {
        $client = $this->getClient();

        $select = '"' . implode('","', $request["columns"]) . '"';
        $from = $request["name"];
        $where = $this->getWhereInDateRange($request);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $client->query(env("INFLUXDB_DBNAME"), 'SELECT ' . $select . 'FROM ' . $from . ' ' . $where)->getPoints();
        $result = $this->doTimeFilter($request, $result);

        return $result;
    }

    public function getClient()
    {
        $host = env("INFLUXDB_HOST");
        $port = env("INFLUXDB_PORT");
        $client = new Client($host, $port);
        return $client;
    }

    private function getWhereInDateRange(Request $request)
    {
        $where = "";
        $startDate = $request->get("start_date");
        $endDate = $request->get("end_date");
        if ($startDate and $endDate) {
            $where = "WHERE (time > '" . explode("+", $startDate)[0] . "Z' and 
            time < '" . explode("+", $endDate)[0] . "Z')";
        }
        return $where;
    }

    private function doTimeFilter(Request $request, Array $result)
    {
        $weekdays = $request->get("weekdays");
        if ($weekdays) {
            $newResult = [];
            foreach ($result as $element) {
                try {
                    $d = new DateTime($element["time"]);
                } catch (ExceptionAlias $e) {
                    $d = null;
                    abort(400);
                }
                $weekday = date('w', $d->getTimestamp());
                if (in_array($weekday, $weekdays)) {
                    array_push($newResult, $element);
                }
            }
            $result = $newResult;
        }

        $start_minute = $request->get("start_minute");
        $end_minute = $request->get("end_minute");
        if ($start_minute != null and $end_minute != null) {
            $newResult = [];
            foreach ($result as $element) {
                try {
                    $d = new DateTime($element["time"]);
                } catch (ExceptionAlias $e) {
                    $d = null;
                    abort(400);
                }
                $minutes = (date('H', $d->getTimestamp()) * 60) + date('i', $d->getTimestamp());
                if ($minutes > $start_minute and ($minutes < $end_minute)) {
                    array_push($newResult, $element);
                }
            }
            $result = $newResult;
        }
        return $result;
    }
}
