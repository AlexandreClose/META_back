<?php


namespace App\Http\Services;


use Illuminate\Http\Request;

class ElasticSearchService
{
    private $start_date;
    private $date_col;
    private $end_date;
    private $week_day;
    private $start_minute;
    private $end_minute;


    public function __construct(Request $request)
    {
        $this->date_col = $request->get('date_col');
        $this->start_date = $request->get('start_date');
        $this->end_date = $request->get('end_date');
        $this->week_day = $request->get('weekdays');
        $this->start_minute = $request->get('start_minute');
        $this->end_minute = $request->get('end_minute');
    }

    public function getMinuteFilter()
    {
        if ($this->start_minute !== null && $this->end_minute !== null) {
            $minuteQuery = "(doc['" . $this->date_col . "'].date.getMinuteOfDay() >= " . $this->start_minute . " && doc['" . $this->date_col . "'].date.getMinuteOfDay() < " . $this->end_minute . ")";
            return $minuteQuery;
        } else {
            return null;
        }

    }

    public function getWeekdayFilter()
    {
        $emptyDayQuery = "doc['" . $this->date_col . "'].date.dayOfWeek == ";
        $fullDayQuery = "";
        if ($this->week_day != null) {
            foreach ($this->week_day as $day) {
                $fullDayQuery .= $emptyDayQuery . $day . " || ";
            }
            $fullDayQuery = str_replace(" || )", ")", "(" . $fullDayQuery . ")");
            return $fullDayQuery;
        } else {
            return null;
        }
    }

    public function getTimeFilter(Array $body, $minuteQuery, $fullDayQuery)
    {
        if ($this->date_col != null) {
            $body = ['sort' => [[$this->date_col => ['order' => 'desc']]]];
            if ($this->date_col != null && $this->start_date != null && $this->end_date == null) {
                $body["query"]["bool"]["must"] = ['range' => [$this->date_col => ['gte' => $this->start_date, 'lte' => $this->start_date]]];
            } elseif ($this->date_col != null && $this->start_date != null && $this->end_date != null) {
                $body["query"]["bool"]["must"] = ['range' => [$this->date_col => ['gte' => $this->start_date, 'lte' => $this->end_date]]];
            }

            if ($fullDayQuery != null && ($minuteQuery != null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => "(" . $fullDayQuery . " && " . $minuteQuery . ")"]];
            } elseif ($fullDayQuery != null && ($minuteQuery == null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => $fullDayQuery]];
            } elseif ($fullDayQuery == null && ($minuteQuery != null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => $minuteQuery]];
            }
        }
        return $body;
    }
}
