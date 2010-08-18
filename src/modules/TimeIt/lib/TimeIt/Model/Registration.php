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
 * An registration to an event date.
 *
 */
class TimeIt_Model_Registration extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('TimeIt_regs');

        $this->hasColumn('pn_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('pn_eid as eventdateId', 'integer', 4, array('notnull' => true));
        $this->hasColumn('pn_uid as uid', 'integer', 4, array('notnull' => true));
        $this->hasColumn('pn_status as status', 'integer', 4, array('notnull' => true,
                                                       'default' => 1)); // 1 = ok, 0 = pending state
        $this->hasColumn('pn_data as data', 'string', 4000, array('notnull' => true,
                                                       'default' => ''));
    }

    /**
     * Setup relationships.
     *
     * @return void
     */
    public function setUp()
    {
        $this->hasOne('TimeIt_Model_EventDate as EventDate', array(
                'local'    => 'eventdateId',
                'foreign'  => 'id',
                'onDelete' => 'CASCADE'
            )
        );

        // Apply standard fields by attaching StandardFields Behavior
        $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pn_'));
    }
}

