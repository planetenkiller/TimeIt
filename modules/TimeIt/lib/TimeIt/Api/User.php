<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Api
 */

/**
 * User Api.
 */
class TimeIt_Api_User extends Zikula_Api
{
    /**
     * Checks if an date is valid.
     *
     * @param array $args Date.
     *
     * @return boolean
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    function checkDate($args)
    {
        if (empty($args)) {
            throw new InvalidArgumentException('$args was empty');
        }

        if (isset($args['date'])) {
            $a = explode('-', $args['date']);
            $args['day'] = (int)$a[2];
            $args['month'] = (int)$a[1];
            $args['year'] = (int)$a[0];
        }

        if (isset($args['week'])) {
            $args['weeknr'] = $args['week'];
        }

        if (isset($args['day'])) {
            $i = (int)$args['day']; // cast to int
            // invalid day=
            if ($i < 1 || $i > 31) {
                return false;
            }
        }

        if (isset($args['weeknr'])) {
            $i = (int)$args['weeknr']; // cast to int
            // invalid day=
            if ($i < 1 || $i > 53) {
                return false;
            }
        }

        if (isset($args['month'])) {
            $i = (int)$args['month']; // cast to int
            // invalid day=
            if ($i < 1 || $i > 12) {
                return false;
            }
        }

        if (isset($args['year'])) {
            $i = (int)$args['year']; // cast to int
            // invalid day=
            if ($i < 1970 || $i > 2037) {
                return false;
            }
        }

        if (isset($args['day']) && isset($args['month']) && isset($args['year'])) {
            $i = (int)DateUtil::getDaysInMonth($args['month'], $args['year']);
            if ((int)$args['day'] > $i) {
                return false;
            }
        }

        return true;
    }

    /**
     * Formats an event.
     *
     * @param array $args Event.
     *
     * @return array
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    function getEventPreformat($args)
    {
        if (!isset($args['obj']) || empty($args['obj'])) {
            throw new InvalidArgumentException('$obj arg not set');
        }

        $obj = &$args['obj'];

        //process text format
        if (substr($obj['text'],0,11) == "#plaintext#") {
            $obj['text'] = substr_replace($obj['text'],"",0,11);
            $obj['text'] = nl2br($obj['text']);
        }

        // hooks
        if (!isset($args['noHooks']) || $args['noHooks'] == false) {
            $obj['text'] = ModUtil::callHooks('item', 'transform', '', array($obj['text']));
            $obj['text'] = $obj['text'][0];
        }

        // repeats
        if ($obj['repeatType'] == 2) {
            $temp = explode(' ', $obj['repeatSpec']);
            $obj['repeat21'] = $temp[0];
            $obj['repeat22'] = $temp[1];
        }

        // split duration
        $obj['allDayDur'] = explode(',', $obj['allDayDur']);

        TimeIt_Util::convertAlldayStartToLocalTime($obj);

        // set username
        $obj['cr_name'] = UserUtil::getVar('uname', (int)$obj['cr_uid']);
        $obj['cr_datetime'] = DateUtil::getDatetime(strtotime($obj['cr_date']), "datetimebrief");

        // set group name
        if ($obj['group'] == 'all') {
            $obj['group_name'] = 'all';
        } else {
            $groupNames = array();
            foreach (explode(',', $obj['group']) as $grpId) {
                $groupObj = UserUtil::getPNGroup((int)$grpId);
                $groupNames[] = $groupObj['name'];
            }

            $obj['group_name'] = $groupNames;
        }


        return $obj;
    }

    /**
     * Creates an array for an month.
     *
     * @param array $args Date.
     *
     * @return array 2 dimensional array.
     * e.g.: array[0][YYYY-MM-DD] = NULL;
     *
     */
    function arrayForMonthView($args)
    {
        if (!isset($args['month']) || !isset($args['year'])) {
            return LogUtil::registerError (_MODARGSERROR);
        }

        if (!ModUtil::apiFunc('TimeIt','user','checkDate',$args)) {
            return LogUtil::registerError (_MODARGSERROR._TIMEIT_INVALIDDATE);
        }

        $array = array();
        $year = $args['year'];
        $month = $args['month'];
        $firstDayOfWeek = (isset($args['firstDayOfWeek'])
                               && (int)$args['firstDayOfWeek'] >= 0
                               &&(int)$args['firstDayOfWeek'] <= 6)?(int)$args['firstDayOfWeek'] : (int)ModUtil::getVar('TimeIt', 'firstWeekDay'); // 0 = Sun 1 = Mo ...

        // calc first day of week in the first week of the month
        $timestampFirstDayOfMonth = gmmktime(0,0,0,(int)$month,1,(int)$year);
        $day1 = (gmdate('w', $timestampFirstDayOfMonth) - $firstDayOfWeek) % 7;
        if($day1 < 0)
            $day1 += 7;

        $timestamp = strtotime('-'.$day1.' days',$timestampFirstDayOfMonth);
        $daysInMonth = DateUtil::getDaysInMonth((int)$month, (int)$year);

        // create array
        $lastDayInMonthFound = false;
        for ($week=0; $week < 6; $week++) {
            for ($day=1; $day <= 7; $day++) {
                $dayNum = date('j',$timestamp);
                //$array[$week][DateUtil::getDatetime($timestamp, DATEONLYFORMAT_FIXED)] = NULL;
                $array[$week][gmdate('Y-m-d', $timestamp)] = null;
                if ($dayNum == $daysInMonth && $week > 0) {
                    $lastDayInMonthFound = true;
                }
                $timestamp += 86400;
            }
            if ($lastDayInMonthFound) {
                break;
            }
        }

        return $array;
    }
}
