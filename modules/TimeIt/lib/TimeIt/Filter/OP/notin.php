<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Filter
 */

/**
 * Notin operator.
 */
class TimeIt_Filter_OP_notin extends TimeIt_Filter_OP_Interface
{
    protected static $filter;

    /**
     * Notin constructor.
     *
     * @param string $field Field.
     * @param string $value Value.
     *
     * @throws InvalidArgumentException If $field is not 'category'.
     */
    public function __construct($field,$value)
    {
        parent::__construct($field,$value);

        if ($field != 'category') {
            throw new InvalidArgumentException('The in operator "notin" supports only "category" as field.');
        }

        self::$filter = null;
    }

    public function __destruct()
    {
        self::$filter = null;
    }

    public function prepare(&$groups)
    {
        if (!self::$filter) {
            $filter = array('__META__'=>array('module'=>'TimeIt'));
            $items = $this->getItems($groups);

            // load the categories system
            if (!($class = Loader::loadClass('CategoryRegistryUtil')))
                z_exit ('Unable to load class [CategoryRegistryUtil] ...');
            $properties  = CategoryRegistryUtil::getRegisteredModuleCategories ('TimeIt', 'TimeIt_events');
            foreach ($properties as $prop => $catid) {
                $filter[$prop] = $items;
            }
            
            self::$filter = DBUtil::generateCategoryFilterWhere('TimeIt_events', false, $filter);
        }
    }

    protected function getItems(&$groups)
    {
        $items = array();
        foreach ($groups as $group) {
            foreach ($group as $op) {
                if ($op instanceof TimeIt_Filter_OP_notin && !in_array($op->getValue(), $items)) {
                    $items[] = (int)$op->getValue();
                }
            }
        }
        return $items;
    }

    public function toSQL($table)
    {
        if (self::$filter) {
            return 'NOT '.($table?  $table.'.' : '').$this->field.' '.self::$filter;
        } else {
            return '';
        }
    }

    public function toURL()
    {
        return $this->field.":notin:".DataUtil::formatForStore($this->value);
    }
}

