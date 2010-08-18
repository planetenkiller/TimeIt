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

class TimeIt_Recurrence_Calculator_Type3 implements TimeIt_Recurrence_Calculator
{
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out)
    {
        $out->insert(strtotime($obj['startDate']), $obj);

        $dates = explode(',',$obj['repeatSpec']);
        // add all occurrences
        foreach ($dates as $date) {
            $out->insert(strtotime($date), $obj);
        }
    }
}
