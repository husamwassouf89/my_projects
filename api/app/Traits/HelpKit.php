<?php


namespace App\Traits;


use Carbon\Carbon;

trait HelpKit
{
    private $dayCount
        = [
            '1'  => 31,
            '2'  => 28,
            '3'  => 31,
            '4'  => 30,
            '5'  => 31,
            '6'  => 30,
            '7'  => 31,
            '8'  => 31,
            '9'  => 30,
            '10' => 31,
            '11' => 30,
            '12' => 31,
        ];

    function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    function getDateRange($year, $quarter)
    {
        $firstDay   = '1';
        $firstMonth = null;
        $lastMonth  = null;
        if (strtolower($quarter) == 'q1') {
            $firstMonth = '1';
            $lastMonth  = '3';
        } else if (strtolower($quarter) == 'q2') {
            $firstMonth = '4';
            $lastMonth  = '6';
        } else if (strtolower($quarter) == 'q3') {
            $firstMonth = '7';
            $lastMonth  = '9';
        } else if (strtolower($quarter) == 'q4') {
            $firstMonth = '10';

            $lastMonth  = '12';
        }
        $lastDay   = $this->getLastDayInTheMonth($year, $lastMonth);
        $firstDate = Carbon::createFromDate($year, $firstMonth, $firstDay);
        $lastDate  = Carbon::createFromDate($year, $lastMonth, $lastDay);
        return [
            'first_date' => $firstDate->toDateTimeString(),
            'last_date'  => $lastDate->toDateTimeString(),
        ];


    }

    public function getLastDayInTheMonth($year, $month)
    {
        if ($month == '2' and $year % 4 == 0) {
            return '29';
        } else {
            return $this->dayCount[$month];
        }

    }

}
