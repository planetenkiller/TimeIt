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

class TimeIt_Recurrence_Calculator_Type0 implements TimeIt_Recurrence_Calculator
{
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out)
    {
        if ($obj['endDate'] == $obj['startDate']) {
            $out->insert(strtotime($obj['startDate']), $obj);
        } else {
            $diff = DateUtil::getDatetimeDiff($obj['startDate'], $obj['endDate']);

            $timestamp = strtotime($obj['startDate']);
            $timestamp = mktime(0,0,0,date('n',$timestamp), date('j',$timestamp), date('Y',$timestamp));
            // add days
            for ($i=0; $i<=$diff['d']; $i++) {
                $out->insert($timestamp, $obj);

                // next day
                $timestamp += 86400;
            }
        }
    }
}
