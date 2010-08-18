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

// register manager of timeit events to DI Container
$evmgrdef = new Zikula_ServiceManager_Definition('TimeIt_Manager_Event');
ServiceUtil::getManager()->registerService(new Zikula_ServiceManager_Service('timeit.manager.event', $evmgrdef, true));

// register manager of timeit calendars to DI Container
$clmgrdef = new Zikula_ServiceManager_Definition('TimeIt_Manager_Calendar');
ServiceUtil::getManager()->registerService(new Zikula_ServiceManager_Service('timeit.manager.calendar', $clmgrdef, true));
