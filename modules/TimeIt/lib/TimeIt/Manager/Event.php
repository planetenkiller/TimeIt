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
 * Manager for events.
 *
 * This manager allows you to create, update, delete events and more.
 */
class TimeIt_Manager_Event
{
    /**
     * Create a new event.
     *
     * @param TimeIt_Model_Event $event                   Event to save.
     * @param boolean            $noRecurrenceCalculation Set to true to omit the recurrence calculation.
     * 
     * @return boolean true if the event create was successfull.
     * @throws InvalidArgumentException If $event is null.
     */
    public function create(TimeIt_Model_Event $event, $noRecurrenceCalculation=false)
    {
        if($event == null)
            throw new InvalidArgumentException ('$event not set');

        if ($event->isValid()) {
            $event->save();
        } else {
            return false;
        }

        //TODO Workflows

        if (!isset($obj['__META__']['TimeIt']['recurrenceOnly']) || !$obj['__META__']['TimeIt']['recurrenceOnly']) {
            if (!$noRecurrenceCalculation) {
                $prozi = new TimeIt_Recurrence_Processor(new TimeIt_Recurrence_Output_DB(), $obj);
                $prozi->doCalculation();
            }

            // Let any hooks know that we have created a new item
            ModUtil::callHooks('item', 'create', $obj['id'], array('module' => 'TimeIt'));
        }

        return true;
    }

    /**
     * Returns all events sorted by day (array level 1==weeks and array level 2 days).
     *
     * @param int                     $year           Year.
     * @param int                     $month          Month.
     * @param int                     $day            Day.
     * @param int                     $cid            Calendar to get events from.
     * @param int                     $firstDayOfWeek First day of week (-1 Default, 0=Su, 1=Mo).
     * @param TimeIt_Filter_Container $filter_obj     Filter.
     * @param array                   $args           Additional arguments.
     * 
     * @return array
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function getMonthEvents($year, $month, $day, $cid, $firstDayOfWeek=-1, TimeIt_Filter_Container $filter_obj=null, $args=array())
    {
        if (empty($year) || empty($month) || empty($day) || empty($cid)) {
            throw new InvalidArgumentException(__('Error! Could not do what you wanted. Please check your input.', ZLanguage::getModuleDomain('TimeIt')));
        }

        // valid Date?
        if (!ModUtil::apiFunc('TimeIt','user','checkDate',array('year' => $year, 'month' => $month, 'day' => $day))) {
            throw new InvalidArgumentException(__f('Error! The date %s is not valid.', $year.'-'.$month.'-'.$day, ZLanguage::getModuleDomain('TimeIt')));
        }

        // get array for month
        $events = ModUtil::apiFunc('TimeIt', 'user', 'arrayForMonthView', array('month' => $month, 
                                                                                'year' => $year,
                                                                                'firstDayOfWeek' => $firstDayOfWeek));

        // extract start date of first week
        reset($events[0]);
        $start = each($events[0]);
        $start = $start['key'];

        // extract end date of last week
        end($events);
        $end = each($events); // last week
        $key = $end['key']; // key of last week
        end($events[$key]); // last day in last week
        $end = each($events[$key]);
        $end = $end['key'];

        // get events form db
        $data = $this->getDailySortedEvents($start, $end, $cid, $filter_obj, $args);

        // insert events from data to the events array
        foreach ($events as $weeknr => $days) {
            foreach ($days as $k => $v) {
                $timestamp = strtotime($k);
                $events[$weeknr][$k] = isset($data[$timestamp]) ? $data[$timestamp] : null;
            }
        }

        return $events;
    }

    /**
     * Returns all specified events as an array.
     *
     * @param string                  $start      Start date.
     * @param string                  $end        End date.
     * @param int                     $cid        Calendar to get events from.
     * @param TimeIt_Filter_Container $filter_obj Filter.
     * @param boolean                 $args       Additional arguments.
     * 
     * @return array
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function getDailySortedEvents($start, $end, $cid, TimeIt_Filter_Container $filter_obj=null, $args=array())
    {
        // valid Dates?
        if (!ModUtil::apiFunc('TimeIt','user','checkDate',array('date' => $start)) || !ModUtil::apiFunc('TimeIt','user','checkDate',array('date' => $end))) {
            throw new InvalidArgumentException(__f('Error! The date %s is not valid.', $start.' / '.$end, ZLanguage::getModuleDomain('TimeIt')));
        }

        // default values
        if(!isset($args['preformat']))
            $args['preformat'] = true;

        $prozessRepeat = (isset($args['prozessRepeat']))? $args['prozessRepeat']: true;
        $userID = UserUtil::getVar('uid');
        $userLang = ZLanguage::getLanguageCode();

        $query = Doctrine_Query::create()
            ->from('TimeIt_Model_EventDate ed')
            ->innerJoin('ed.MainEvent e')
            ->leftJoin('ed.LocalEvent le')
            ->where('ed.cid = ?', $cid)
            ->andWhere('((le.id IS NOT NULL AND le.status = 1) OR (le.id IS NULL AND e.status = 1))')
            ->andWhere("ed.date >= ? AND ed.date <= ?", array($start, $end));



        // add sharing conditions to where if the filter contains no custom sharing expression
        if ($filter_obj == null || !$filter_obj->hasFilterOnField('sharing')) {
            $where = '(';
            $whereParams = array();

            if (!empty($userID)) {
                $where .= '((le.id IS NOT NULL AND le.cr_uid = ? AND (le.sharing = 1 OR le.sharing = 2)) OR (e.cr_uid = ? AND (e.sharing = 1 OR e.sharing = 2))) OR ';
            
                $whereParams[] = $userID;
                $whereParams[] = $userID;
            }

            $where .= 'e.sharing = 3 OR e.sharing = 4 OR le.sharing = 3 OR le.sharing = 4)';

            $query->andWhere($where, $whereParams);
        }

        // add filter to sql
        if ($filter_obj != null) {
            $filter_sql = $filter_obj->toSQL('le');

            if (!empty($filter_sql)) {
                $query->andWhere('((le.id IS NOT NULL AND '.$filter_sql.') OR (le.id IS NULL AND '.$filter_obj->toSQL('e').'))');
            }
        }

        // get data form database
        $array = $query->fetchArray();
        $ret = array();
        
        // --------------- ContactList integration --------------------
        $buddys = array();
        $ignored = array();
        if (ModUtil::available('ContactList')) {
            if (ModUtil::getVar('TimeIt', 'friendCalendar')) {
                $buddys = ModUtil::apiFunc('ContactList','user','getBuddyList',array('uid'=>$User_ID));
            }

            $ignored = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid'=>$User_ID));
        }
        // --------------- end ContactList integration --------------------

        $site_multilingual = System::getVar('multilingual');
        $eventsAdded = 0;
        foreach ($array as $objDate) {
            $objDate = $this->_setEventRelation($objDate);
            $obj = $objDate['Event'];


            if (!TimeIt_Util_Permission::canViewEvent($objDate, ACCESS_OVERVIEW)) {
                continue; // no permission to this event so ignore it
            }

            if (isset($args['preformat']) && $args['preformat']) {
                $obj = ModUtil::apiFunc('TimeIt','user','getEventPreformat',array('obj'=>$obj));
            } else if (substr($obj['text'],0,11) == "#plaintext#") {
                $obj['text'] = substr_replace($obj['text'],"",0,11);
                $obj['text'] = nl2br($obj['text']);
            }

            if ($site_multilingual && (!isset($args['translate']) || ( isset($args['translate']) && $args['translate'] == true))) {
                if (isset($obj['title_translate'][$user_lang]) && !empty($obj['title_translate'][$user_lang])) {
                    $obj['title'] = $obj['title_translate'][$user_lang];
                }

                if (isset($obj['text_translate'][$user_lang]) && !empty($obj['text_translate'][$user_lang])) {
                    $obj['text'] = $obj['text_translate'][$user_lang];
                }
            }

            $timestamp = strtotime($objDate['date']);
            // Move this event back or forward if the timezone calculation needs a move
            if (isset($obj['allDayStartLocalDateCorrection'])) {
                $timestamp = $timestamp + ($obj['allDayStartLocalDateCorrection'] * (60 * 60 * 24));
            }
            $this->_getDailySortedEventsAddToArray($ret, $timestamp, $objDate);
            $eventsAdded++;

            // limit control
            if (isset($args['limit']) && $eventsAdded >= $args['limit']) {
                break;
            }
        }

        ksort($ret); // sort keys in array
        //print_r($ret);exit();

        foreach ($ret as $key => $events) {
            usort($ret[$key], array($this, "_getDailySortedEventsUsort"));
        }

        return $ret;
    }

    /**
     * Helper for usort.
     *
     * @param array $a1 A.
     * @param array $b1 B.
     *
     * @return boolean
     */
    private function _getDailySortedEventsUsort($a1 ,$b1)
    {
        if (ModUtil::getVar('TimeIt','sortMode') == 'byname') {
            $a = $a1['info']['name'];
            $b = $b1['info']['name'];
            return strcasecmp($a, $b);
        } else {
            $a = $a1['info']['sort_value'];
            $b = $b1['info']['sort_value'];
            if ($a == $b) {
                return 0;
            } else if ($a < $b) {
                return -1;
            } else {
                return 1;
            }
        }
    }

    /**
     * Adds an event to the array.
     *
     * @param array &$array   Data array.
     * @param int   $tmestamp Day to add event to.
     * @param array $obj      Event.
     *
     * @return void
     */
    private function _getDailySortedEventsAddToArray(&$array, $tmestamp, $obj)
    {
        $property = ModUtil::getVar('TimeIt', 'colorCatsProp', 'Main');
        // get category id
        $catID = isset($obj['Event']['__CATEGORIES__'][$property]['id'])? $obj['Event']['__CATEGORIES__'][$property]['id'] : 0;
        // There are events out there which aren't in any category
        if (empty($catID)) {
            $catID = 0;
        }
        // isn't the category id set on $array?
        if (!isset($array[$tmestamp][$catID])) {
            $array[$tmestamp][$catID] = array();
            $name = isset($obj['Event']['__CATEGORIES__'][$property]['name'])? $obj['Event']['__CATEGORIES__'][$property]['name'] : "";
            if (isset($obj['Event']['__CATEGORIES__'][$property]['display_name'][ZLanguage::getLanguageCode()])) {
                $name = $obj['Event']['__CATEGORIES__'][$property]['display_name'][ZLanguage::getLanguageCode()];
            }
            $array[$tmestamp][$catID]['info'] = array('name'       => $name,
                                                      'color'      => isset($obj['Event']['__CATEGORIES__'][$property]['__ATTRIBUTES__']['color'])? $obj['Event']['__CATEGORIES__'][$property]['__ATTRIBUTES__']['color'] : null,
                                                      'sort_value' =>isset($obj['Event']['__CATEGORIES__'][$property]['sort_value'])? (int)$obj['Event']['__CATEGORIES__'][$property]['sort_value'] : 0);
            $array[$tmestamp][$catID]['data'] = array();
            if (empty($array[$tmestamp][$catID]['info']['color']) && $name) {
                $array[$tmestamp][$catID]['info']['color'] = ModUtil::getVar('TimeIt', 'defalutCatColor');
            }

        }


        // add event to category
        $array[$tmestamp][$catID]['data'][] = $obj;

        if (count($array[$tmestamp][$catID]['data']) > 1) {
            // search best pos in $array
            for ($i = count($array[$tmestamp][$catID]['data'])-1; $i > 0; $i--) {
                $item = $array[$tmestamp][$catID]['data'][$i];
                $itembe = $array[$tmestamp][$catID]['data'][$i-1];
                if ($itembe['allDayStartLocal'] > $item['allDayStartLocal']) {
                    $objbe = $array[$tmestamp][$catID]['data'][$i-1];
                    $array[$tmestamp][$catID]['data'][$i-1] = $array[$tmestamp][$catID]['data'][$i];
                    $array[$tmestamp][$catID]['data'][$i] = $objbe;
                }
            }
        }
    }

    /**
     * Add an 'Event' key based on MainEvent and LocalEvent.
     *
     * @param array|TimeIt_Model_EventDate $obj EventDate.
     *
     * @return array|TimeIt_Model_EventDate
     */
    private function _setEventRelation($obj) {
         if(isset($obj['LocalEvent'])) {
            if(is_object($obj)) {
                $obj->mapValue('Event', $obj['LocalEvent']);
            } else {
                $obj['Event'] =& $obj['LocalEvent'];
            }
        } else {
            if(is_object($obj)) {
                $obj->mapValue('Event', $obj['MainEvent']);
            } else {
                $obj['Event'] =& $obj['MainEvent'];
            }
        }

        return $obj;
    }
}
