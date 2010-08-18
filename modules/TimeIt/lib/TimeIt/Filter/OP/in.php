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
 * In operator.
 */
class TimeIt_Filter_OP_in extends TimeIt_Filter_OP_Interface
{
    protected static $filter;

    /**
     * In constructor.
     *
     * @param string $objectType Object type.
     * @param string $field      Field.
     * @param string $value      Value.
     *
     * @throws InvalidArgumentException If $field is not 'category'.
     */
    public function __construct($objectType, $field, $value)
    {
        // cast all numbers in the list to integers because of security
        if ($field == 'id') {
            $ids = explode(',', $value);
            $newValue = array();
            foreach ($ids as $id) {
                $newValue[] = (int)$id;
            }
            $value = $newValue;
        }

        parent::__construct($objectType, $field, $value);

        if ($field != 'category' && $field != 'id') {
            throw new InvalidArgumentException('The in operator "in" supports only "category" and "id" as field.');
        }

        self::$filter = null;
    }

    public function __destruct()
    {
        self::$filter = null;
    }

    public function prepare(&$groups)
    {
        if (!self::$filter && $this->field == 'category') {
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
                if ($op instanceof TimeIt_Filter_OP_in && !in_array($op->getValue(), $items)) {
                    $items[] = (int)$op->getValue();
                }
            }
        }
        return $items;
    }

    public function toSQL($table)
    {
        if ($this->field == 'category') {
            if (self::$filter) {
                return ($table?  $table.'.' : '').$this->field.' '.self::$filter;
            } else {
                return '';
            }
        } else {
            return ($table?  $table.'.' : '').$this->field.' IN('.implode(',', $this->value).')';
        }
    }

    public function toURL()
    {
        return $this->field.":in:".DataUtil::formatForStore($this->value);
    }
}

