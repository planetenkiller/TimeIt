<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Controller
 */

/**
 * User Controller.
 */
class TimeIt_Controller_User extends Zikula_Controller
{
    /**
     * Module entry point.
     *
     * @return string HTML Code
     */
    public function main()
    {
        ////////////////// categories /////////////////////////

        // read
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')
//                    ->createQuery('e')
//                    ->leftJoin('e.Categories cm INDEXBY cm.reg_property')
//                    ->leftJoin('cm.Registry reg')
//                    ->leftJoin('cm.Category c')
//                    ->fetchArray();
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->findAll(Doctrine::HYDRATE_ARRAY);
//
//        return '<pre>cat:'.$event[0]['Categories']['secound']['Category']['id'].' debug:'.print_r($event, true).'</pre>';


        // update
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')
//                    ->createQuery('e')
//                    ->leftJoin('e.Categories cm INDEXBY cm.reg_property')
//                    ->leftJoin('cm.Registry reg')
//                    ->leftJoin('cm.Category c')
//                    ->fetchOne();
//        $event['Categories']['main']['Category'] = Doctrine::getTable('Categories_Model_Category')->find(34);
//        $event->save();
//        return '<pre>cat:'.$event['Categories']['main']['Category']['id'].' debug:'.var_dump($event->toArray()).'</pre>';

        // insert
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')
//                    ->createQuery('e')
//                    ->leftJoin('e.Categories cm INDEXBY cm.reg_property')
//                    ->leftJoin('cm.Registry reg')
//                    ->leftJoin('cm.Category c')
//                    ->fetchOne();
//        $event->setCategory('secound', Doctrine::getTable('Categories_Model_Category')->find(35));
//        $event->save();

          // delete
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(10);
//        $event->delete();


        ////////////////// attributes /////////////////////////

        //insert
//        $event = new TimeIt_Model_Event();
//        $event->title = 'title';
//        $event->startDate = '2010-01-01';
//        $event->endDate = '2010-01-01';
//        $event->__ATTRIBUTES__ = array('key1' => 'value1', 'key2' => 'value2');
//        $event->save();

//        // read
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(13);
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // update
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(13);
//        $event->__ATTRIBUTES__->key1 = 'value11111';
//        $event->save();
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // delete
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(15);
//        $event->delete();




        ////////////////// metadata /////////////////////////

        //insert
//        $event = new TimeIt_Model_Event();
//        $event->title = 'title';
//        $event->startDate = '2010-01-01';
//        $event->endDate = '2010-01-01';
//        $event->__META__->dc_format = 'xml';
//        $event->__META__->dc_comment = 'my comment';
//        $event->__META__->dc_keywords = 'keyword1,keyword2';
//        $event->save();


         // read
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(20);
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // update
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(20);
//        $event->__META__->dc_format = 'xml222';
//        $event->__META__->dc_comment = 'my comment222';
//        $event->__META__->dc_keywords = 'keyword1111,keyword22222';
//        $event->save();
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // delete
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(25);
//        $event->delete();


         ////////////////// logging /////////////////////////

        //insert
//        $event = new TimeIt_Model_Event();
//        $event->title = 'title';
//        $event->startDate = '2010-01-01';
//        $event->endDate = '2010-01-01';
//        $event->save();

        // update
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(30);
//        $event->startDate = '2010-01-02';
//        $event->endDate = '2010-01-02';
//        $event->text = 'text';
//        $event->save();
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // update 2
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(30);
//        $event->text = null;
//        $event->save();
//        return '<pre>'.print_r($event->toArray(), true).'</pre>';

        // delete
//        $event = Doctrine_Core::getTable('TimeIt_Model_Event')->find(30);
//        $event->delete();

       return true;

//        return ModUtil::func('TimeIt', 'user', 'view');
    }

    /**
     * Displays all available events.
     *
     * @return string HTML Code
     */
    public function view()
    {
        // check object type
        $objectType = FormUtil::getPassedValue('ot', 'event', 'GET');
        $this->throwNotFoundUnless(in_array($objectType, TimeIt_Util::getObjectTypes('view')),
                                   $this->__f('Unkown object type %s.', DataUtil::formatForDisplay($objectType)));


        // load filter
        $filter = TimeIt_Filter_Container::getFilterFormGETPOST($objectType);


        $this->view->assign('modvars', ModUtil::getVar('TimeIt'));

        // vars
        $tpl = null;
        $theme = null;
        $domain = $this->serviceManager->getService('timeit.manager.'.$objectType);

        // load the data
        if ($objectType == 'event') {
            $calendarId = (int)FormUtil::getPassedValue('cid', ModUtil::getVar('TimeIt', 'defaultCalendar'), 'GETPOST');
            $calendar = $this->serviceManager->getService('timeit.manager.calendar')->getObject($calendarId);
            $this->throwNotFoundIf(empty($calendar), $this->__f('Calendar [%s] not found.', $calendarId));

            $year    = (int)FormUtil::getPassedValue('year', date("Y"), 'GETPOST');
            $month   = (int)FormUtil::getPassedValue('month', date("n"), 'GETPOST');
            $day     = (int)FormUtil::getPassedValue('day', date("j"), 'GETPOST');
            $tpl = FormUtil::getPassedValue('viewType', FormUtil::getPassedValue('viewtype', $calendar['config']['defaultView'], 'GETPOST'), 'GETPOST');
            $firstDayOfWeek = (int)FormUtil::getPassedValue('firstDayOfWeek', -1, 'GETPOST');
            $theme = FormUtil::getPassedValue('template', $calendar['config']['defaultTemplate'] , 'GETPOST');

            // backward compatibility
            if($theme == 'default')
                $theme = 'table';

            // check for a valid $tpl
            if ($tpl != 'year' && $tpl != 'month' && $tpl != 'week' && $tpl != 'day') {
                $tpl = $calendar['config']['defaultView'];
            }

            $tpl = 'month';
            $theme = 'table';

            $this->view->assign('template', $theme);
            $this->view->assign('viewed_day', $day);
            $this->view->assign('viewed_month', $month);
            $this->view->assign('viewed_year', $year);
            $this->view->assign('viewType', $tpl);
            $this->view->assign('calendar', $calendar);
            $this->view->assign('viewed_date', DateUtil::getDatetime(mktime(0, 0, 0, $month, $day, $year), DATEONLYFORMAT_FIXED));
            $this->view->assign('date_today', DateUtil::getDatetime(null, DATEONLYFORMAT_FIXED));
            $this->view->assign('month_startDate', DateUtil::getDatetime(mktime(0, 0, 0, $month, 1, $year), DATEONLYFORMAT_FIXED)  );
            $this->view->assign('month_endDate', DateUtil::getDatetime(mktime(0, 0, 0, $month, DateUtil::getDaysInMonth($month, $year), $year), DATEONLYFORMAT_FIXED) );
            $this->view->assign('filter_obj_url', $filter->toURL());
            $this->view->assign('firstDayOfWeek', $firstDayOfWeek);
            $this->view->assign('selectedCats', array());

            $categories = CategoryRegistryUtil::getRegisteredModuleCategories('TimeIt', 'TimeIt_events');
            foreach ($categories as $property => $cid) {
                $cat = CategoryUtil::getCategoryByID($cid);

                if (isset($cat['__ATTRIBUTES__']['calendarid']) && !empty($cat['__ATTRIBUTES__']['calendarid'])) {
                    if ($cat['__ATTRIBUTES__']['calendarid'] != $calendar['id']) {
                        unset($categories[$property]);
                    }
                }
            }
            $this->view->assign('categories', $categories);

            // load event data
            switch ($tpl) {
                case 'year':
                    $objectData = $domain->getYearEvents($year, $calendar['id'], $firstDayOfWeek);
                    break;
                case 'month':
                    $objectData = $domain->getMonthEvents($year, $month, $day, $calendar['id'], $firstDayOfWeek, $filter);
                    break;
                case 'week':
                    $objectData = $domain->getWeekEvents($year, $month, $day, $calendar['id'], $filter);
                    break;
                case 'day':
                    $objectData = $domain->getDayEvents($year, $month, $day, $calendar['id'], $filter);
                    break;
            }
        }

        // assign the data
        $this->view->assign('objectArray', $objectData);

        // render the html
        return $this->_renderTemplate($this->view, $objectType, 'user', 'view', $theme, $tpl, 'table');
    }

    /**
     * Render template.
     *
     * @param Zikula_View $render       Renderer.
     * @param string      $objectType   Object type.
     * @param string      $type         Controller.
     * @param string      $func         Function of Controller.
     * @param string      $theme        Theme to use (themes are subfolders in /templates).
     * @param string      $tpl          Sub template name.
     * @param string      $defaultTheme Default theme (Use if $theme is null).
     *
     * @return string Rendered Template (HTML)
     */
    private function _renderTemplate(Zikula_View $render, $objectType, $type, $func, $theme=null, $tpl=null, $defaultTheme=null)
    {
        $template = $type . '_' . $func . '_' . $objectType;
        if ($tpl != null) {
            $template .= '_' . $tpl;
        }
        $template .= '.tpl';

        if (!empty($theme) && $render->template_exists(DataUtil::formatForOS($theme).'/'.$template)) {
            $template = DataUtil::formatForOS($theme).'/'.$template;
        } else if (!empty($defaultTheme) && $render->template_exists(DataUtil::formatForOS($defaultTheme).'/'.$template)) {
            $template =  DataUtil::formatForOS($defaultTheme).'/'.$template;
        }

        return $render->fetch($template);
    }
}
