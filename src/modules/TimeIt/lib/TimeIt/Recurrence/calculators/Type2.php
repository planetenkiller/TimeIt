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

class TimeIt_Recurrence_Calculator_Type2 implements TimeIt_Recurrence_Calculator
{
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out)
    {
        // mappings from numbers to strings for strtotime
        $typMap = array(1 => '',
                        2 => '+ 1 week',
                        3 => '+ 2 week',
                        4 => '+ 3 week',
                        5 => '- 1 week');
        $weekdayMap = array(0 => 'sun',
                            1 => 'mon',
                            2 => 'tue',
                            3 => 'wed',
                            4 => 'thu',
                            5 => 'fri',
                            6 => 'sat');
        $spec = explode(' ', $obj['repeatSpec']);
        $specOld = $spec;
        $specOld[0] = explode(',', $specOld[0]);

        // loop through checked days
        foreach ($specOld[0] as $specValue) {
            $spec[0] = $specValue;
            // calc unix timestamp
            $a = $typMap[(int)$spec[0]];
            $b = $weekdayMap[(int)$spec[1]];
            $c = '';

            // special case
            if ((int)$spec[0] == 5) {
                $temp = explode('-', $start);
                $date = strtotime('+1 month +1 day', mktime(0,0,0, (int)$temp[1], 1, (int)$temp[0]));
            } else {
                // normal
                $temp = explode('-', $start);
                $date = mktime(0,0,0, (int)$temp[1], 1, (int)$temp[0]);
            }
            $stamp = strtotime($b, /*strtotime('-1 day',*/$date/*)*/);

            // special case: secound is used for the time in strtotime
            // emulation with first and the addition of seven days
            if ($a) {
                $stamp = strtotime($a, $stamp);

            }

            $months = 0;
            $date = DateUtil::getDatetime($stamp, DATEONLYFORMAT_FIXED);

            // set helper variable for "date out of range detection"
            if ((int)substr($date, 0, 4) >= 2000) {
                $inYear2000 = true;
            } else {
                $inYear2000 = false;
            }


            while ($date <= $end && $date <= $obj['endDate']) {
                // in requested range?
                if ($date >= $start && $date <= $end && $date >= $obj['startDate']) {
                    $temp1 = getDate(strtotime($date));
                    $temp1 = mktime(0,0,0, $temp1['mon'], $temp1['mday'], $temp1['year']);
                    $out->insert($temp1, $obj);
                }
                // next occurence
                $months++;
                // calc unix timestamp
                if ((int)$spec[0] == 5) {
                    // special case: last from strtotime(to the past) != last form TimeIt(to the future)
                    $month_calc = $months*(int)$obj['repeatFrec']+1;
                } else {
                    $month_calc = $months*(int)$obj['repeatFrec'];
                }
                $tdate = strtotime('+'.$month_calc.' month', mktime(0,0,0, (int)$temp[1], 1, (int)$temp[0]));
                $stamp = strtotime($b.''.$a, $tdate);
                $date = DateUtil::getDatetime($stamp, DATEONLYFORMAT_FIXED);

                // detect date out of range: after year 2037 the time jumps back to 1970
                // Stop calculation after a jump
                if ($inYear2000 && (int)substr($date, 0, 4) < 2000) {
                    break;
                } else if (!$inYear2000 && (int)substr($date, 0, 4) >= 2000) {
                    $inYear2000 = true;
                }
            }
        }
    }
}
