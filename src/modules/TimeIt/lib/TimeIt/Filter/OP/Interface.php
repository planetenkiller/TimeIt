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
 * Base class for filter operators.
 */
abstract class TimeIt_Filter_OP_Interface
{
    /**
     * Name of the field.
     *
     * @var string
     */
    protected $field;

    /**
     * Value of the field.
     *
     * @var string
     */
    protected $value;

    /**
     * Contructior.
     *
     * @param string $objectType Object type.
     * @param string $field      Field of object type.
     * @param string $value      Value.
     *
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function __construct($objectType, $field, $value)
    {
        if (empty($field) || empty($objectType)) {
            throw new InvalidArgumentException('$field  or $objectType is empty');
        }
        
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * Called before getSQL().
     *
     * @param array &$groups All expressions.
     *
     * @return void
     */
    public function prepare(&$groups)
    {
    }

    /**
     * Converts an expression (eg cruid:eq:2) to a SQL string (eg. pn_cr_ui = 2).
     *
     * @param string $table Table alias to use.
     *
     * @return stirng SQL
     */
    public abstract function toSQL($table);

    /**
     * Converts an expression to a URL compitable string (eg. cruid:eq:2).
     *
     * @return string URL Query string
     */
    public abstract function toURL();

    /**
     * Returns the value of this expression.
     * 
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the field of this expression.
     * 
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns an TimeIt_Filter_OperatorIf instance form an expression.
     *
     * @param string $objectType Object type.
     * @param string $exp        Expression in format: field:operator:value .
     * 
     * @return TimeIt_Filter_OperatorIf
     * @throws InvalidArgumentException In case of invalid parameters.
     * @throws LogicException If operation class does not extend this class.
     */
    public static function operatorFromExp($objectType, $exp)
    {
        $pattern = '/^([0-9a-zA-Z_-]+):([0-9a-zA-Z_-]+):(.*)$/';
        
        // extract parts
        if (preg_match_all($pattern, $exp, $array)) {
            $field = $array[1][0];
            $operator = $array[2][0];
            $value = $array[3][0];

            if (strlen($value) > 0) {
                // check field

                $class = 'TimeIt_Filter_OP_'.DataUtil::formatForOS($operator);
                // check operator
                if (class_exists($class)) {
                    $rfclass = new ReflectionClass($class);
                    // check operator class (need to use reflection because we can't create an instance yet)
                    if ($rfclass->isSubclassOf(new ReflectionClass('TimeIt_Filter_OP_Interface'))) {

                        if (($field == 'cr_uid' || $field == 'lu_uid') && (int)$value == -1) {
                            $value = UserUtil::getVar('uid',-1,1); // set uid of current user
                        } else if (($field == 'cr_uid' || $field == 'lu_uid') && !preg_match('/^[0-9]+$/', $value)) {
                            if ($value == 'User Name') {
                                return null;
                            } else {
                                $name = $value;
                                $value = $uid = UserUtil::getIdFromName($value); // get user id form user name
                                if (empty($uid)) {
                                    // show error
                                    LogUtil::registerError(__f('The user named "%s" not found (TimeIt filter).', $name, ZLanguage::getModuleDomain('TimeIt')));
                                    return null;
                                }
                            }
                        } else if (($field == 'cr_uid' || $field == 'up_uid') && preg_match('/^[0-9]+$/', $value)) {
                            $value = (int)$value;
                        }
                        if ($value) {
                            return new $class($objectType, $field, $value);
                        } else {
                            return null;
                        }
                    } else {
                        throw new LogicException('Class of operator '.$operator.' ('.$class.') is not a subclass of TimeIt_Filter_OP_Interface.');
                    }

                } else {
                    throw new InvalidArgumentException('Expression has got an invalid operator ('.$operator.').');
                }
                
            } // ignore filter
        } else {
            throw new InvalidArgumentException('Expression has got an invalid format.');
        }
    }
}

