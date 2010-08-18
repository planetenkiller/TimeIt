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
 * An calculator calculates all occurrences of an event.
 */
interface TimeIt_Recurrence_Calculator
{
    /**
     * Performs the calculation for the given event.
     *
     * @param int                      $start Only occurrences from this start date (incl.).
     * @param int                      $end   Only occurrences to this end date (incl.).
     * @param array                    &$obj  The event.
     * @param TimeIt_Recurrence_Output $out   The output object.
     *
     * @return void
     */
    public function calculate($start, $end, array &$obj, TimeIt_Recurrence_Output $out);
}