<?php
/**
 * TimeIt Calendar Module
 *
 * @copyright (c) TimeIt Development Team
 * @link http://code.zikula.org/timeit
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package TimeIt
 * @subpackage Recurrence
 */

/**
 * Recurrence processor.
 *
 * This processor calculates all recurrences of a TimeIt event.
 * An TimeIt_Recurrence_Output object saves the recurrences in the DB or in an arrray.
 */
class TimeIt_Recurrence_Processor
{
    protected $out;
    protected $obj;
    protected $types;

    /**
     * Creates a new recurrence processor.
     *
     * @param TimeIt_Recurrence_Output $out                 The output object to use.
     * @param TimeIt_Model_Event       $obj                 The event for which the occurrences are calculated.
     * @param boolean                  $noIgnoreDateFilter  True to ignore the repeatIrg field.
     * @param array                    $customDatesToIgnore Array of dates to ignore.
     *
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function __construct(TimeIt_Recurrence_Output $out, TimeIt_Model_Event $obj, $noIgnoreDateFilter=false, $customDatesToIgnore=null)
    {
        if ($out == null || $obj == null) {
            throw new InvalidArgumentException('$out or $obj is null!');
        }
        
        if (!$noIgnoreDateFilter && isset($obj['repeatIrg']) && !empty($obj['repeatIrg'])) {
            $out = new TimeIt_Recurrence_IgnoreDateFilter($out, explode(',', $obj['repeatIrg']));
        }

        if (!empty($customDatesToIgnore)) {
            $out = new TimeIt_Recurrence_IgnoreDateFilter($out, $customDatesToIgnore);
        }

        $this->out   = $out;
        $this->obj   = $obj;
        $this->types = array();

        $this->addDefaultCalculators();
    }

    /**
     * Performs the calculation.
     *
     * @param string $start Date with format DATEONLYFORMAT_FIXED(yyyy-mm-dd) or null.
     * @param string $end   Date with format DATEONLYFORMAT_FIXED(yyyy-mm-dd) or null.
     *
     * @return void
     * @throws LogicException If no calculator was found for the repeatType.
     */
    public function doCalculation($start=null, $end=null)
    {
        // set dates if start or end is null
        $start = ($start != null)? $start : $this->obj['startDate'];
        $end = ($end != null)? $end : $this->obj['endDate'];

        if (isset($this->types[$this->obj['repeatType']])) {
            $this->types[$this->obj['repeatType']]->calculate($start, $end, $this->obj, $this->out);
        } else {
            throw new LogicException('Unkown repeatType "'.$this->obj['repeatType'].'"!');
        }
    }

    /**
     * Adds a new calculator.
     *
     * @param int                          $type Int representation of the calculator (repeatType column).
     * @param TimeIt_Recurrence_Calculator $calc The calculator.
     *
     * @return void
     * @throws InvalidArgumentException In case of invalid parameters.
     */
    public function addCalculator($type, TimeIt_Recurrence_Calculator $calc)
    {
        if (!is_int($type) || $calc == null) {
            throw new InvalidArgumentException('$type is not an int or $calc is null!');
        }

        $this->types[$type] = $calc;
    }

    /**
     * Add all default calculators.
     *
     * @return void
     */
    protected function addDefaultCalculators()
    {
        $this->addCalculator(0, new TimeIt_Recurrence_Calculator_Type0());
        $this->addCalculator(1, new TimeIt_Recurrence_Calculator_Type1());
        $this->addCalculator(2, new TimeIt_Recurrence_Calculator_Type2());
        $this->addCalculator(3, new TimeIt_Recurrence_Calculator_Type3());
        $this->addCalculator(4, new TimeIt_Recurrence_Calculator_Type4());
    }
}

/**
 * This ouput delegates all calls to the $out output but omits all dates that are in $dates.
 */
class TimeIt_Recurrence_IgnoreDateFilter implements TimeIt_Recurrence_Output
{
    /**
     * Outputter.
     *
     * @var TimeIt_Recurrence_Output
     */
    private $_out;

    /**
     * Dates to ignore.
     *
     * @var array
     */
    private $_dates;

    /**
     * Creates a new IgnoreDateFilter outputter.
     *
     * @param TimeIt_Recurrence_Output $out   Original output.
     * @param array                    $dates Dates to ignore (format of dates DATEONLYFORMAT_FIXED(yyyy-mm-dd)).
     */
    public function __construct(TimeIt_Recurrence_Output $out, array $dates)
    {
        $this->_out = $out;
        $this->_dates = $dates;
    }

    public function insert($timestamp, array &$obj)
    {
        if (!in_array(DateUtil::getDatetime($timestamp, DATEONLYFORMAT_FIXED), $this->_dates)) {
            $this->_out->insert($timestamp, $obj);
        }
    }
}