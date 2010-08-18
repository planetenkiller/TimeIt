<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Manager
 */

/**
 * Calendar manager.
 */
class TimeIt_Manager_Calendar
{
    /**
     * Returns an Object by its Id.
     *
     * @param int $id Calendar id.
     *
     * @return TimeIt_Model_Calendar
     */
    public function getObject($id)
    {
        return Doctrine_Core::getTable('TimeIt_Model_Calendar')->find($id);
    }
}

