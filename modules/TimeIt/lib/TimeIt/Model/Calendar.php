<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Models
 */

/**
 * An Single event.
 *
 */
class TimeIt_Model_Calendar extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('TimeIt_calendars');

        $this->hasColumn('pn_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('pn_name as name', 'string', 255, array('notnull' => true));
        $this->hasColumn('pn_desc as desc', 'string', 4000, array('notnull' => true,
                                                                  'default' => ''));

        $this->hasColumn('pn_privateCalendar as privateCalendar', 'boolean', null, array('notnull' => true,
                                                                                         'default' => false));
        $this->hasColumn('pn_globalCalendar as globalCalendar', 'boolean', null, array('notnull' => true,
                                                                                       'default' => true));
        $this->hasColumn('pn_friendCalendar as friendCalendar', 'boolean', null, array('notnull' => true,
                                                                                       'default' => false));

        $this->hasColumn('pn_config as config', 'string', 4000, array('notnull' => true,
                                                                      'default' => ''));
    }
}

