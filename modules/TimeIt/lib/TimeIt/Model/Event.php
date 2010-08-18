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
 * @property int $id event id
 *
 */
class TimeIt_Model_Event extends Doctrine_Record
{
    /**
     * Setup table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('TimeIt_events');
        
        $this->hasColumn('pn_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('pn_iid as iid', 'string', 255, array('notnull' => true,
                                                               'default' => ''));

        $this->hasColumn('pn_title as title', 'string', 255, array('notnull' => true));
        $this->hasColumn('pn_text as text', 'string', 4000, array('notnull' => true,
                                                                  'default' => ''));
        $this->hasColumn('pn_title_translate as title_translate', 'string', 4000, array('notnull' => true,
                                                                                        'default' => ''));
        $this->hasColumn('pn_text_translate as text_translate', 'string', 4000, array('notnull' => true,
                                                                                      'default' => ''));
        $this->hasColumn('pn_data as data', 'string', 4000, array('notnull' => true,
                                                                  'default' => ''));

        $this->hasColumn('pn_allDay as allDay', 'boolean', null, array('notnull' => true,
                                                                       'default' => true));
        $this->hasColumn('pn_allDayStart as allDayStart', 'string', 10, array('notnull' => true,
                                                                              'default' => '00:00'));
        $this->hasColumn('pn_allDayDur as allDayDur', 'string', 15, array('notnull' => true,
                                                                          'default' => '0'));

        $this->hasColumn('pn_repeatType as repeatType', 'integer', 1, array('notnull' => true,
                                                                            'default' => 0));
        $this->hasColumn('pn_repeatSpec as repeatSpec', 'string', 4000, array('notnull' => true,
                                                                              'default' => ''));
        $this->hasColumn('pn_repeatFrec as repeatFrec', 'integer', 4, array('notnull' => true,
                                                                            'default' => 0));
        $this->hasColumn('pn_repeatIrg as repeatIrg', 'string', 4000, array('notnull' => true,
                                                                            'default' => ''));

        $this->hasColumn('pn_startDate as startDate', 'date', null, array('notnull' => true));
        $this->hasColumn('pn_endDate as endDate', 'date', null, array('notnull' => true));

        $this->hasColumn('pn_sharing as sharing', 'integer', 1, array('notnull' => true,
                                                                      'default' => 3));
        $this->hasColumn('pn_group as group', 'string', 255, array('notnull' => true,
                                                                   'default' => 'all'));
        $this->hasColumn('pn_status as status', 'boolean', null, array('notnull' => true,
                                                                       'default' => 1));
        $this->hasColumn('pn_subscribeLimit as subscribeLimit', 'integer', 4, array('notnull' => true,
                                                                                    'default' => 0));
        $this->hasColumn('pn_subscribeWPend as subscribeWPend', 'integer', 4, array('notnull' => true,
                                                                                    'default' => 0));
    }

    /**
     * Setup relationships.
     *
     * @return void
     */
    public function setUp()
    {
//        $this->hasMany('TimeIt_Model_EventDate as EventDates', array(
//                'local'    => 'id',
//                'foreign'  => 'eid'
//            )
//        );

//        $this->hasOne('TimeIt_Model_EventDate as LocalEventDate', array(
//                'local'   => 'id',
//                'foreign' => 'localeid',
//                'onDelete' => 'CASCADE'
//            )
//        );

        $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pn_'));
        $this->actAs('Zikula_Doctrine_Template_Categorisable');
        $this->actAs('Zikula_Doctrine_Template_Attributable');
        $this->actAs('Zikula_Doctrine_Template_MetaData');
        $this->actAs('Zikula_Doctrine_Template_Logging');
    }
    
}

