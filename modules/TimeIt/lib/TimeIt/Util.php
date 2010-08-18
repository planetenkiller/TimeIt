<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Util
 */

/**
 * Util class with some usefull functions.
 */
abstract class TimeIt_Util
{
    /**
     * Returns all available object types for a functions.
     *
     * @param string $function Possible values: view,display,edit,delete.
     *
     * @return array available object types
     */
    public static function getObjectTypes($function)
    {
        // available types
        $types = array('view'    => array('calendar','event','reg'),
                       'display' => array('event'),
                       'edit'    => array('calendar','event'),
                       'delete'  => array('calendar','event','reg'));

        if (isset($types[$function])) {
            return $types[$function];
        } else {
            return array();
        }
    }

    /**
     * Converts the $obj[allDayStart] to the current timezone.
     *
     * @param array &$obj Event.
     *
     * @return void
     */
    public static function convertAlldayStartToLocalTime(&$obj) {
        if ($obj['allDay'] == 0) {
            if (strpos($obj['allDayStart'], ' ') !== false) {
                // calc local start time
                $time = substr($obj['allDayStart'], 0, strpos($obj['allDayStart'], ' '));
                $timezone = (int)substr($obj['allDayStart'], strpos($obj['allDayStart'], ' ')+1);
                $timezoneCurr = (int)(UserUtil::getVar('tzoffset')!==false ? UserUtil::getVar('tzoffset') : System::getVar('timezone_offset'));
                $zoneOffset = ($timezone * -1) + $timezoneCurr;
                list($hour, $min) = explode(':', $time);
                list($zone_hour, $zone_minDez) = explode('.', $zoneOffset);
                $hour += $zone_hour;
                $min += $zone_minDez * 60; // convert e.g. 0.75 to 45
                // more than 60 minutes than add an hour and reduce the minutes
                if ($min >= 60) {
                    $hour++;
                    $min = $min - 60;
                }

                if ($hour < 0) {
                    $obj['allDayStartLocalDateCorrection'] = -1;
                    $hour = 24 + $hour; // fix minus value
                } else if ($hour > 24) {
                    $obj['allDayStartLocalDateCorrection'] = +1;
                    $hour = $hour - 24; // fix to big value
                }

                $obj['allDayStartLocal'] = ($hour < 10?'0':'').$hour.':'.($min<10?'0':'').$min;
            } else {
                $obj['allDayStartLocal'] = $obj['allDayStart'];
            }

            // format it
            $obj['allDayStartLocalFormated'] = DateUtil::getDatetime(strtotime($obj['startDate'].' '.$obj['allDayStartLocal'].':00'), 'timebrief');
            // Add timezone to the time
            //$obj['allDayStartLocalFormated'] = $obj['allDayStartLocalFormated'].' '.DateUtil::strftime('%Z');

        }
    }
}

