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
 * Le operator.
 */
class TimeIt_Filter_OP_le extends TimeIt_Filter_OP_Interface
{
    public function toSQL($table)
    {
        return ($table?  $table.'.' : '').$this->field." <= '".DataUtil::formatForStore($this->value)."'";
    }

    public function toURL()
    {
        return $this->field.":le:".DataUtil::formatForStore($this->value);
    }
}

