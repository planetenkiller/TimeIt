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
 * An Outputter saves all recurrences on a implementation specific place.
 */
interface TimeIt_Recurrence_Output
{
    /**
     * The output calls this function for every occurrence.
     *
     * @param int   $timestamp Unix timestamp.
     * @param array &$obj      The current event.
     *
     * @return void
     */
    public function insert($timestamp, array &$obj);
}
