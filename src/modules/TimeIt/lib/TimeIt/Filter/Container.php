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
 * This filter converts filters like title:eq:Hello to its SQL/URL representation.
 */
class TimeIt_Filter_Container
{
    /**
     * Data holder for groups.
     *
     * @var array
     */
    protected $groups;

    /**
     * True if all OP's prepare() method was called.
     *
     * @var boolean
     */
    protected $prepared;

    /**
     * Object Type.
     * 
     * @var string
     */
    protected $objectType;

    /**
     * Use OR instead of AND to link expressions?.
     *
     * @var boolean
     */
    protected $linkExpressionsInGroupWithOr = false;

    /**
     * Use AND insted of OR to link Groups?.
     *
     * @var boolean
     */
    protected $linkGroupsWithAnd = false;

    /**
     * Create a new empty TimeIt_Filter_Container.
     *
     * @param string $objectType Object type.
     *
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function __construct($objectType)
    {
        if (empty($objectType)) {
            throw new InvalidArgumentException('$objectType may not be null.');
        }
        
        $this->groups = array();
        $this->prepared = false;
        $this->objectType = $objectType;
    }

    /**
     * Allows you to set if expressions in an group should be linked with OR instead of AND.
     *
     * @param boolean $bool Value.
     *
     * @return void
     */
    public function setUseORToLinkExpressionsInAGroup($bool) {
        $this->linkExpressionsInGroupWithOr = (bool)$bool;
    }

    /**
     * Allws you to set if groups should be linked with AND instead of OR.
     *
     * @param boolean $bool Value.
     *
     * @return void
     */
    public function setUseANDToLinkGroups($bool) {
        $this->linkGroupsWithAnd = (bool)$bool;
    }

    /**
     * Returns true if this filter contains a expression with the field $field.
     *
     * @param stirng $field A fieldname.
     *
     * @return boolean
     */
    public function hasFilterOnField($field)
    {
        foreach ($this->groups as $group) {
            foreach ($group as $op) {
                if ($op->getField() == $field) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if theres at leas one group.
     *
     * @return bool true when there is min. one group, false otherwise
     */
    public function hasGroup()
    {
        return count($this->groups) > 0;
    }

    /**
     * Add an expression group. All expressions in the same group are linked with AND. Groups are linked with OR.
     * 
     * @return TimeIt_Filter_Container $this
     */
    public function addGroup()
    {
        $this->groups[] = array();

        return $this;
    }

    /**
     * Add an expression this the current open group.
     * 
     * @param string $string A expression in the format field:oper:value .
     * 
     * @return TimeIt_Filter_Container $this
     * @throws LogicException If there's no open group.
     */
    public function addExp($string)
    {
        if (count($this->groups) === 0) {
            throw new LogicException('No groups found!');
        }
        $op = TimeIt_Filter_OperatorIf::operatorFromExp($this->objectType, $string);
        if ($op) {
            $this->groups[count($this->groups)-1][] = $op;
            $this->prepared = false;

            
            if ($this->objectType == 'event') {
                // security: add cr_uid expression if there is a share filter for private events.
                if ($op->getField() == 'sharing' && (
                        ($op instanceof TimeIt_Filter_OP_le || $op instanceof TimeIt_Filter_OP_lt)
                     || ((int)$op->getValue() <= 2 && ($op instanceof TimeIt_Filter_OP_eq || TimeIt_Filter_OP_ne))
                     || ($op instanceof TimeIt_Filter_OP_like && ($op->getValue() == '%2%' || $op->getValue() == '2%' || $op->getValue() == '2' || $op->getValue() == '%1%' || $op->getValue() == '1%' || $op->getValue() == '1'))
                     )) {
                    $op2 = TimeIt_Filter_OP_Interface::operatorFromExp($this->objectType, 'cr_uid:eq:-1');
                    if ($op2 != null) {
                        $this->groups[count($this->groups)-1][]  = $op2;
                    }
                }
            }
        }
        
        return $this;
    }

    /**
     * Calls prepare() on every operation.
     *
     * @return void
     */
    protected function prepareOps()
    {
        if (!$this->prepared) {
            foreach ($this->groups as $group) {
                foreach ($group as $op) {
                    $op->prepare($this->groups);
                }
            }
            $this->prepared = true;
        }
    }

    /**
     * Converts this TimeIt_Filter_Container to its SQL representation.
     *
     * @param string $table Table alias to use.
     *
     * @return string SQL WHERE part.
     */
    public function toSQL($table=null)
    {
        $this->prepareOps();
        $sql = array();
        $sql_s = '';
        $expLinker = $this->linkExpressionsInGroupWithOr? 'OR' : 'AND';
        $grpLinker = $this->linkGroupsWithAnd? 'AND' : 'OR';

        foreach ($this->groups as $group) {
            $sql_sub = array();
            foreach ($group as $op) {
                $sql_sub[] = $op->toSQL($table);
            }

            if (!empty($sql_sub)) {
                $sql_sub = implode(' '.$expLinker.' ', $sql_sub);
                $sql[] = '('.$sql_sub.')';
            }
        }
        if (!empty($sql)) {
            $sql_s = implode(' '.$grpLinker.' ', $sql);
            if (count($sql) > 1) {
                $sql_s = '('.$sql_s.')';
            }
        }

        return $sql_s;
    }

    /**
     * Converts this TimeIt_Filter to its URL parameter representation.
     * 
     * @return string Format: filter1=exp,exp&filter1=exp,exp
     */
    public function toURL()
    {
        $this->prepareOps();
        $url = array();
        $url_s = '';
        $expLinker = ',';

        foreach ($this->groups as $group) {
            $url_sub = array();
            foreach ($group as $op) {
                $url_sub[] = $op->toURL();
            }

            if (!empty($url_sub)) {
                $url_sub = implode($expLinker, $url_sub);
                $url[] = $url_sub;
            }
        }
        
        if (!empty($url)) {
            if (count($url) == 1) {
                $url_s = 'filter='.$url_sub;
            } else {
                $url2 = array();
                for ($i=1; $i<=count($url); $i++) {
                    $url2[] = 'filter'.$i.'='.$url[$i-1];
                }
                $url_s = implode('&', $url2);
            }
        }

        return $url_s;
    }

    /**
     * Creates a TimeIt_Filter_Container form GET and POST values.
     *
     * @param string $objectType Object type to pass to the constructor.
     *
     * @return TimeIt_Filter_Container created TimeIt_Filter_Container
     */
    public static function getFilterFormGETPOST($objectType='event')
    {
        $ret = new TimeIt_Filter_Container($objectType);
        $filter = FormUtil::getPassedValue('filter', null, 'GETPOST');

        if (!empty($filter)) {
            self::substituteVariables($filter);
            $expressions = explode(',', $filter);
            $ret->addGroup();
            foreach ($expressions as $ex) {
                $ret->addExp($ex);
            }

        } else {
            $filter1 = FormUtil::getPassedValue('filter1', null, 'GETPOST');
            if (!empty($filter1)) {
                $i = 1;
                while ($filter = FormUtil::getPassedValue('filter'.$i, null, 'GETPOST')) {
                    self::substituteVariables($filter);
                    $expressions = explode(',', $filter);
                    $ret->addGroup();
                    foreach ($expressions as $ex) {
                        $ret->addExp($ex);
                    }

                    $i++;
                }
            }
        }

        return $ret;
    }

    /**
     * Creates a TimeIt_Filter_Container form GET and POST values.
     *
     * @param string                  $string     Filter expression to parse.
     * @param TimeIt_Filter_Container $filter     Filter object to use.
     * @param string                  $objectType Obect type to pass to the constructon if $filter was null.
     *
     * @return TimeIt_Filter_Container created TimeIt_Filter_Container
     */
    public static function getFilterFormString($string, TimeIt_Filter_Container $filter=null, $objectType='event')
    {
        if ($filter) {
            $ret = $filter;
        } else {
            $ret = new TimeIt_Filter_Container($objectType);
        }
        if (!$ret->hasGroup()) {
            $ret->addGroup();
        }

        if (!empty($string)) {
            $first = true;
            $filters = explode('&', $string);
            foreach ($filters as $filter) {
                if (!$first) {
                    $ret->addGroup();
                }

                $expressions = explode(',', $filter);
                foreach ($expressions as $ex) {
                    $ret->addExp($ex);
                }
            }

        }

        return $ret;
    }

    /**
     * Replaces all variables (example: $abc) with their valus form GET or POST.
     *
     * @param string &$string String with variables.
     *
     * @return void
     */
    protected static function substituteVariables(&$string)
    {
        if (preg_match_all('/(\$[0-9a-zA-Z_-]+)/', $string, $array) !== false) {
            $vars = $array[0];

            foreach ($vars as $var) {
                $value = FormUtil::getPassedValue(substr($var, 1, strlen($var)-1), '', 'GETPOST');
                $string = self::strReplaceOnce($var, $value, $string);
            }
        }
    }

    /**
     * Str_replace_once.
     *
     * @param string $needle   Search.
     * @param string $replace  Replace.
     * @param string $haystack Data.
     *
     * @return string
     */
    public static function strReplaceOnce($needle , $replace , $haystack)
    {
        // Looks for the first occurence of $needle in $haystack
        // and replaces it with $replace.
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            // Nothing found
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }
}

