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

Loader::requireOnce('modules/TimeIt/pnuserapi.php');

class TimeIt_Recurrence_Calculator_Type4 implements TimeIt_Recurrence_Calculator
{
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out)
    {
        $ret = array();
        // calculate recurrences
        TimeIt_userapi_icalRruleProzess($ret, $obj, $start, $end);

        // add all recurrences
        foreach (array_keys($ret) as $timestamp) {
            $out->insert($timestamp, $obj);
        }
    }
}
