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
 * Central point with all permission check functions.
 */
abstract class TimeIt_Util_Permission
{
    /**
     * Checks permissions for TimeIt admin access.
     *
     * @param boolean $return False to an array of valid Groups.
     *
     * @return boolean|array
     */
    public static function adminAccessCheck($return=false)
    {
        if (!SecurityUtil::checkPermission( 'TimeIt::', "::", ACCESS_MODERATE)) {
            $groups = UserUtil::getGroupsForUser(UserUtil::getVar('uid'));
            $groups[] = array('name' => 'all', 'gid'=>'all');

            // check each group for permission
            $ret = array();
            foreach ($groups as $group) {
                if (isset($group['gid']) && $group['gid'] == 'all') {
                    $name = 'all';
                } else {
                    $group = UserUtil::getPNGroup((int)$group);
                    $name = $group['name'];
                }
                if (SecurityUtil::checkPermission('TimeIt:Group:', $name."::", ACCESS_MODERATE)) {
                    if (!$return) {
                        return true;
                    } else {
                        $ret[] = $group['gid'];
                    }
                }
            }
            if (!$return) {
                return false;
            } else {
                return $ret;
            }
        } else {
            return true;
        }
    }

    /**
     * Returns true if the current user can create an event.
     *
     * @param int     $calendarId   Calendar id to check create permission in this calendar.
     * @param boolean $modeModerate True to check moderate access (false comment access).
     *
     * @return boolean
     */
    public static function canCreateEvent($calendarId=null, $modeModerate=false)
    {
        $permLevel = $modeModerate? ACCESS_MODERATE : ACCESS_COMMENT;

        return (SecurityUtil::checkPermission('TimeIt::', '::', $permLevel)
                || ($calendarId != null && SecurityUtil::checkPermission('TimeIt:Calendar:', $calendarId.'::', $permLevel)));
    }

    /**
     * Returns true if the current user can create a calendar.
     * 
     * @return boolean
     */
    public static function canCreateCalendar() {
        return SecurityUtil::checkPermission('TimeIt::', "::", ACCESS_ADMIN);
    }

    /**
     * Checks if the current us has admin access to TimeIt.
     *
     * @return boolean
     */
    public static function isAdmin() {
        return SecurityUtil::checkPermission('TimeIt::', "::", ACCESS_ADMIN);
    }

    /**
     * Returns true if the current user can edit a calendar.
     * 
     * @return boolean
     */
    public static function canEditCalendar() {
        return SecurityUtil::checkPermission('TimeIt::', "::", ACCESS_ADMIN);
    }

    /**
     * Returns true if the current user can edit the event $event.
     *
     * @param TimeIt_Model_EventDate $eventDate     Event.
     * @param boolean                $modeModerator True to check moderate access (false edit access).
     *
     * @return boolean
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public static function canEditEvent(TimeIt_Model_EventDate $eventDate, $modeModerator=false)
    {
        if (empty($eventDate)) {
            throw new InvalidArgumentException("canEditEvent called with an empty \$eventDate arg!");
        }

        $event = $eventDate['Event'];

        // get group names
        if ($event['group'] == 'all') {
            $groupName = array('all'); // group irrelevant
        } else {
            $groupNames = array();
            foreach (explode(',', $event['group']) as $grpId) {
                $groupObj = UserUtil::getPNGroup((int)$grpId);
                $groupNames[] = $groupObj['name'];
            }

            $groupName = $groupNames;
        }

        // get calendar
        $calendar = $eventDate['Calendar'];

        // check permissions
        $permLevel = !$modeModerator? ACCESS_EDIT : ACCESS_MODERATE;
        if (!SecurityUtil::checkPermission('TimeIt::', '::', $permLevel)) {
            if (!SecurityUtil::checkPermission('TimeIt:Calendar:', $calendar['id'].'::', $permLevel)) {
                $access = false;
                foreach ($groupName as $name) {
                    if (SecurityUtil::checkPermission('TimeIt:Group:', $name.'::', $permLevel)) {
                        $access = true;
                    }
                }

                // continue permission checks when $access is false (== no permissions)
                if (!$access) {
                    if ($calendar != null && $calendar['userCanEditHisEvents'] && $event['cr_uid'] == UserUtil::getVar('uid')) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Returns true if the current user can translate the event $event.
     *
     * @param array $event Event.
     *
     * @return boolean
     */
    public static function canTranslateEvent($event)
    {
        return (System::getVar('multilingual') && (self::canEditEvent($event) || SecurityUtil::checkPermission('TimeIt:Translate:', '::', ACCESS_EDIT)));
    }

    /**
     * Returns true if the current user can delete the calender with the id $calendarId.
     *
     * @param int $calendarId Calendar id.
     *
     * @return boolean
     */
    public static function canDeleteCalendar($calendarId) {
        return SecurityUtil::checkPermission('TimeIt::', '::', ACCESS_ADMIN);
    }

    /**
     * Returns true if the current user can delete the registration with the date_has_events id $dheid.
     *
     * @param int $dheId TimeIt_Model_EventDate id.
     *
     * @return boolean
     */
    public static function canDeleteReg($dheId) {
        $dheobj = Doctrine_Core::getTable('TimeIt_Model_EventDate')->find($dheId);
        $event = $dheobj['Event'];

        return self::canEditEvent($event);
    }

    /**
     * Returns true if the current user can delete the event $event.
     *
     * @param TimeIt_Model_EventDate $eventDate Event.
     *
     * @return boolean
     */
    public static function canDeleteEvent(TimeIt_Model_EventDate $eventDate)
    {
        $event = $eventDate['Event'];

        // get group names
        if ($event['group'] == 'all') {
            $groupName = array('all'); // group irrelevant
        } else {
            $groupNames = array();
            foreach (explode(',', $event['group']) as $grpId) {
                $groupObj = UserUtil::getPNGroup((int)$grpId);
                $groupNames[] = $groupObj['name'];
            }

            $groupName = $groupNames;
        }

        // get calendar
        $calendar = $eventDate['Calendar'];

        // check permissions
        if (!SecurityUtil::checkPermission('TimeIt::', '::', ACCESS_DELETE)) {
            if (!SecurityUtil::checkPermission('TimeIt:Calendar:', $calendar['id'].'::', ACCESS_DELETE)) {
                $access = false;
                foreach ($groupName as $name) {
                    if (SecurityUtil::checkPermission('TimeIt:Group:', $name.'::', ACCESS_DELETE)) {
                        $access = true;
                    }
                }


                if (!$access) {
                    if ($calendar != null && $calendar['userCanEditHisEvents'] && $event['cr_uid'] == UserUtil::getVar('uid')) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Returns true if the current user can view the event $event.
     * 
     * @param array $eventDate TimeIt_Model_EventDate as an array.
     * @param int   $level     ACCESS_* constant.
     *
     * @return boolean
     */
    public static function canViewEvent(array $eventDate, $level=ACCESS_READ)
    {
        $event = $eventDate['Event'];

        $groups = UserUtil::getGroupsForUser(UserUtil::getVar('uid'));
        // hack: Admins (group id 2 are in group 1(users) to)
        if (in_array(2, $groups)) {
            $groups[] = 1;
        }

        if ($event['group'] == 'all') {
            $groupId = null; // group irrelevant
        } else {
            $groupId = explode(',', $event['group']);
        }

        static $calendarCache = array();
        if (!isset($calendarCache[(int)$event['id']])) {
            // get calendar
            $calendarCache[(int)$event['id']] = $eventDate['Calendar'];
        }

        $calendar = $calendarCache[(int)$event['id']];

        // check permissions

        // hierarchy level 1: module itself
        if(!SecurityUtil::checkPermission('TimeIt::', '::', $level))
            return false;

        // hierarchy level 2: calendar
        if(!SecurityUtil::checkPermission('TimeIt:Calendar:', $calendar['id'].'::', $level))
            return false;

        // hierarchy level 3: group
        if (!empty($groupId)) {
            $access = false;
            foreach ($groupId as $grpId) {
                if (in_array($grpId, $groups)) {
                    $access = true;
                }
            }

            if (!$access) {
                return false;
            }
        }

        // hierarchy level 5: timeit category permission
        if (count($event['__CATEGORIES__']) > 0) {
            $permissionOk = false;
            foreach ($event['__CATEGORIES__'] as $cat) {
                $cid = $cat;
                if (is_array($cat)) {
                    $cid = $cat['id'];
                }

                $permissionOk = SecurityUtil::checkPermission('TimeIt:Category:', $cid."::", $level);
                if ($permissionOk) {
                    // user has got permission -> stop permission checks
                    $hasPermission = true;
                    break;
                }
            }

            if(!$hasPermission)
                return false;
        }

        // hierarchy level 6: zikula category permission
        if (ModUtil::getVar('TimeIt', 'filterByPermission', 0) && !CategoryUtil::hasCategoryAccess($event['__CATEGORIES__'], 'TimeIt', $level)) {
            return false;
        }

        // hierarchy level 7: event
        if(!SecurityUtil::checkPermission('TimeIt::Event', $event['id'].'::', $level))
            return false;


        // hierarchy level 8: contact list
        if (ModUtil::available('ContactList')) {
            // cache
            static $ignored = null;

            if ($ignored == null) {
                $ignored = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid' => UserUtil::getVar('uid')));
            }

            if ($calendar['friendCalendar']) {
                $buddys = ModUtil::apiFunc('ContactList','user','getBuddyList',array('uid' => $event['cr_uid']));
            }

            if ((int)$event['sharing'] == 4 && $event['cr_uid'] != UserUtil::getVar('uid')) {
                $buddyFound = false;
                foreach ($buddys as $buddy) {
                    if ($buddy['uid'] == UserUtil::getVar('uid')) {
                        $buddyFound = true;
                        break;
                    }
                }

                if(!$buddyFound)
                    return false;
            }

            $ignoredFound = false;
            foreach ($ignored as $ignore) {
                if ($ignore['iuid'] == $obj['cr_uid']) {
                    $ignoredFound = true;
                    break;
                }
            }
            if ($ignoredFound) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if the current user can register itself to the event $event.
     *
     * @param TimeIt_Model_EventDate $eventDate Event.
     *
     * @return boolean
     */
    public static function canCreateReg(TimeIt_Model_EventDate $eventDate)
    {
        $event = $eventDate['Event'];

        // get calendar
        $calendar = $eventDate['Calendar'];

        // check permissions
        if (SecurityUtil::checkPermission('TimeIt:subscribe:', '::', ACCESS_COMMENT)) {
            if ($calendar != null && $calendar['allowSubscribe'] && $event['subscribeLimit'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the current user can register to events.
     *
     * @param array $event Event.
     *
     * @return boolean
     */
    public static function canViewRegDetails($event)
    {
        
        //    self::canCreateReg($event) security problem: all users with register permissions can see the address!
        //    || 
        if (SecurityUtil::checkPermission( 'TimeIt::', "::", ACCESS_ADMIN)
           || $event['cr_uid'] === UserUtil::getVar('uid')) {
            return true;
        }

        return false;
    }
}