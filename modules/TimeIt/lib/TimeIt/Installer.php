<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Core
 */

/**
 * Installer for TimeIt.
 */
class TimeIt_Installer extends Zikula_Installer
{
    /**
     * Install the TimeIt module.
     *
     * @return boolean
     */
    function install()
    {
        // create the socialNetwork table
        try {
            DoctrineUtil::createTablesFromModels('TimeIt');
        } catch (Exception $e) {
            LogUtil::registerError($e->getMessage());
            return false;
        }

        
        // add module vars
        pnModSetVar('TimeIt', 'monthtoday', '#FF3300');
        pnModSetVar('TimeIt', 'monthon', '');
        pnModSetVar('TimeIt', 'monthoff', '#d4d2d2');
        pnModSetVar('TimeIt', 'rssatomitems', 20);
        pnModSetVar('TimeIt', 'notifyEvents', 0);
        pnModSetVar('TimeIt', 'notifyEventsEmail', pnUserGetVar('email', 2));
        pnModSetVar('TimeIt', 'itemsPerPage', 25);
        pnModSetVar('TimeIt', 'filterByPermission', 0);
        pnModSetVar('TimeIt', 'popupOnHover', 0);
        pnModSetVar('TimeIt', 'colorCats', 1);
        pnModSetVar('TimeIt', 'googleMapsApiKey', '');
        pnModSetVar('TimeIt', 'mapViewType', 'googleMaps');
        pnModSetVar('TimeIt', 'mapHeight', 320);
        pnModSetVar('TimeIt', 'mapWidth', 480);
        pnModSetVar('TimeIt', 'colorCatsProp', 'Main');
        pnModSetVar('TimeIt', 'hideTimeItAddress', 0);
        pnModSetVar('TimeIt', 'defaultCalendar', 1);
        pnModSetVar('TimeIt', 'firstWeekDay', 1);
        pnModSetVar('TimeIt', 'defalutCatColor', 'silver');
        pnModSetVar('TimeIt', 'truncateTitle', 30);
        pnModSetVar('TimeIt', 'enablecategorization', 1);
        pnModSetVar('TimeIt', 'userdeletionMode', 'anonymize'); // or delete
        pnModSetVar('TimeIt', 'dateformat', 'datebrief');
        pnModSetVar('TimeIt', 'defaultPrivateCalendar', 0);
        pnModSetVar('TimeIt', 'sortMode', 'byname'); // or bysortvalue

        return true;
    }


    /**
     * Upgrade the module from an old version.
     *
     * @return boolean
     */
    function upgrade($oldversion)
    {
        return true;
    }


    /**
     * Uninstall the TimeIt module.
     *
     * @return boolean
     */
    function uninstall()
    {
        // drop tables
        DoctrineUtil::dropTable('TimeIt_calendars');
        DoctrineUtil::dropTable('TimeIt_events');
        DoctrineUtil::dropTable('TimeIt_date_has_events');
        DoctrineUtil::dropTable('TimeIt_regs');

        // remove all module vars
        ModUtil::delVar('TimeIt');

        return true;
    }
}