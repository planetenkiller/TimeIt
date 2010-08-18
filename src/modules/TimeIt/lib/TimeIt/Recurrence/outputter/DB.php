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

/**
 * Outputter that saves all occurrences in the DB.
 */
class TimeIt_Recurrence_Output_DB implements TimeIt_Recurrence_Output
{
    public function insert($timestamp, array &$obj)
    {
        $obj = new TimeIt_Model_EventDate();
        $obj->eid = $obj['id'];
        $obj->date = DateUtil::getDatetime($timestamp, DATEONLYFORMAT_FIXED);
        $obj->cid = $obj['cid'];
        $obj->save();
    }
}