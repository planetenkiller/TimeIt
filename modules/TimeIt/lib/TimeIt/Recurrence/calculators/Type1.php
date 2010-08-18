<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Recurrence
 */

class TimeIt_Recurrence_Calculator_Type1 implements TimeIt_Recurrence_Calculator
{
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out)
    {
        $time = $start;
        $diff = DateUtil::getDatetimeDiff($obj['startDate'], $time);

        // weekly?
        if ($obj['repeatSpec'] == "week") {
            $weeks = (int)date('W', strtotime($start)) - (int)date('W', strtotime($obj['startDate']));
            $weeks = (int)floor($weeks / $obj['repeatFrec']);
            if ($weeks < 0) $weeks = 0;

            $date = DateUtil::getDatetime(strtotime('+'.($weeks*$obj['repeatFrec']).' week', strtotime($obj['startDate'])), DATEONLYFORMAT_FIXED);
            while ($date <= $end && $date <= $obj['endDate']) {
                // in requested range?
                if ($date >= $start && $date <= $end && $date >= $obj['startDate']) {
                    $temp = getDate(strtotime($date));
                    $temp = mktime(0,0,0, $temp['mon'], $temp['mday'], $temp['year']);
                    $out->insert($temp, $obj);
                }
                // next occurence
                $weeks++;
                $date = DateUtil::getDatetime(strtotime('+'.($weeks*$obj['repeatFrec']).' week', strtotime($obj['startDate'])), DATEONLYFORMAT_FIXED);
            }

            // monthly?
        } else if ($obj['repeatSpec'] == "month") {
            // calc start
            $years = ((int)date('Y', strtotime($end))) - (int)date('Y', strtotime($obj['startDate']));
            $months = $years * 12;
            $monthsTemp = ((int)date('n', strtotime($start))) - (int)date('n', strtotime($obj['startDate']));
            $months = $months + $monthsTemp;
            $months = (int)floor($months / $obj['repeatFrec']);

            $date = DateUtil::getDatetime(strtotime('+'.($months*$obj['repeatFrec']).' month', strtotime($obj['startDate'])), DATEONLYFORMAT_FIXED);
            //print_r($monthsTemp);exit();
            while ($date <= $end && $date <= $obj['endDate']) {
                // in requested range?
                if ($date >= $start && $date <= $end && $date >= $obj['startDate']) {
                    $temp = getDate(strtotime($date));
                    $temp = mktime(0,0,0, $temp['mon'], $temp['mday'], $temp['year']);
                    $out->insert($temp, $obj);
                }
                // next occurence
                $months++;
                $date = DateUtil::getDatetime(strtotime('+'.($months*$obj['repeatFrec']).' month', strtotime($obj['startDate'])), DATEONLYFORMAT_FIXED);
            }

            // yearly
        } else if ($obj['repeatSpec'] == "year") {
            $years_start = (int)date('Y', strtotime($obj['startDate']));
            $years_ende = (int)date('Y', strtotime($obj['endDate']));

            for ($year=$years_start; $year <= $years_ende; $year += $obj['repeatFrec']) {
                // calc timestamp
                $temp = getDate(strtotime($obj['startDate']));
                $temp = mktime(0,0,0, $temp['mon'], $temp['mday'], $year);
                $date = DateUtil::getDatetime($temp, DATEONLYFORMAT_FIXED);

                // in requested range?
                if ($date >= $start && $date <= $end && $date >= $obj['startDate']) {
                    $out->insert($temp, $obj);
                }
            }
            
            // daily
        } else {
            $repeats = (int)floor($diff['d']/$obj['repeatFrec']);

            $repeats--;
            if ($repeats < 0) {
                $daysToLastUnusedRepeat = (int)(-$obj['repeatFrec']);
                            /* } else if($repeats == 0)
                    {
                            $daysToLastUnusedRepeat = 0 - $obj['repeatFrec'];*/
            } else {
                $daysToLastUnusedRepeat = $repeats * $obj['repeatFrec'];
            }

            $timestamp = strtotime($obj['startDate']);
            $timestampEnd = strtotime($end);
            $counter = $obj['repeatFrec'];

            while (true) {
                $temp = mktime(0,0,0,date('n',$timestamp), date('j',$timestamp)+$daysToLastUnusedRepeat+$counter, date('Y',$timestamp));
                $counter += $obj['repeatFrec'];
                // end reached?
                if ($temp > strtotime($obj['endDate']) || $temp > $timestampEnd) {
                    break;
                }
                
                $out->insert($temp, $obj);
            }

        }
    }
}
