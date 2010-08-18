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
 * An date where an event occures.
 *
 */
class TimeIt_Model_EventDate extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('TimeIt_date_has_events');
        
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('eid', 'integer', 4, array('notnull' => true));
        $this->hasColumn('localeid', 'integer', 4);
        $this->hasColumn('the_date as date', 'date', null, array('notnull' => true));
        $this->hasColumn('cid', 'integer', 4, array('notnull' => true));
    }

    /**
     * Setup relationships.
     *
     * @return void
     */
    public function setUp()
    {
        $this->hasOne('TimeIt_Model_Event as MainEvent', array(
                'local'    => 'eid',
                'foreign'  => 'id',
                'onDelete' => 'CASCADE'
            )
        );

        $this->hasOne('TimeIt_Model_Event as LocalEvent', array(
                'local'   => 'localeid',
                'foreign' => 'id',
            )
        );

        $this->hasOne('TimeIt_Model_Calendar as Calendar', array(
                'local'   => 'cid',
                'foreign' => 'id',
            )
        );

        $this->hasMany('TimeIt_Model_Registration as Registrations', array(
                'local'   => 'id',
                'foreign' => 'eventdateId'
            )
        );

        $this->addListener(new TimeIt_Model_Listener_EventDate());
    }
}

