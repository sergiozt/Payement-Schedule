<?php

/**
 * User: s.zheleznytskyi
 * Date: 6/25/15
 * Time: 10:54 PM
 */
class Schedule
{
    /**
     * Qty of months in the year.
     */
    const MONTH_QTY = 12;

    /**
     * Default export file name.
     */
    const DEFAULT_FILE_NAME = 'schedule.csv';

    /**
     * Payment day position in the month for bonus.
     */
    const DATE_OF_BONUS = 15;

    /**
     * Payment day position in th week
     * if current day isn't weekday.
     */
    const BONUS_DAY_POSITION = 3;

    /**
     * Day position of the first day on the month.
     */
    const FIRST_DATE_OF_MONTH = 1;

    /**
     * Output csv file resource.
     *
     * @var null|resource
     */
    protected $_outputCsv = null;

    /**
     * Contain current year number.
     *
     * @var null|string
     */
    protected $_currentYear = null;

    /**
     * Days in which salary|bonus can't be payed.
     *
     * @var array
     */
    public $_forbiddenDayPositions = array(
        0,
        6,
    );

    /**
     * Column names.
     *
     * @var array
     */
    protected $_outputCsvHeaders = array(
        'Month',
        'Salary',
        'Bonus',
    );

    /**
     * Create resource for the export file.
     * Check getopt if isset file name as param.
     */
    public function __construct()
    {
        if ($this->_outputCsv === null) {
            $filename = self::DEFAULT_FILE_NAME;
            $opt = getopt("f:");
            if (count($opt) && isset($opt['f'])) {
                $filename = $opt['f'];
            }
            $this->_outputCsv = fopen($filename, 'w');
        }
    }

    /**
     * Init schedule.
     * Collect dates for year.
     * Write them to file.
     */
    public function initSchedule()
    {
        $this->_writeHeaders();
        for ($monthNumber = 1; $monthNumber <= self::MONTH_QTY; $monthNumber++) {
            $salaryDate = $this->getSalaryDate($monthNumber);
            $bonusDate = $this->getBonusDate($monthNumber);
            $this->_writeScheduleLine($salaryDate, $bonusDate, $monthNumber);
        }
    }

    /**
     * Get Salary date depends on month number.
     *
     * @param $monthNumber
     * @return string
     */
    public function getSalaryDate($monthNumber)
    {
        $dateTime = $this->_dateTime();
        $dateTime->setDate($this->_getCurrentYear(), $monthNumber, self::FIRST_DATE_OF_MONTH);
        $dateTime->modify('last day of this month');
        $weekDayPosition = $dateTime->format('w');

        while (in_array($weekDayPosition, $this->_forbiddenDayPositions)) {
            $dateTime->modify('-1 day');
            $weekDayPosition = $dateTime->format('w');
        }

        return $dateTime->format('Y-m-d');
    }

    /**
     * Get Bonus date depends on month number.
     *
     * @param $monthNumber
     * @return string
     */
    public function getBonusDate($monthNumber)
    {
        $dateTime = $this->_dateTime();
        $dateTime->setDate($this->_getCurrentYear(), $monthNumber, self::DATE_OF_BONUS);
        $weekDayPosition = $dateTime->format('w');
        if (in_array($weekDayPosition, $this->_forbiddenDayPositions)) {
            while ($weekDayPosition != self::BONUS_DAY_POSITION) {
                $dateTime->modify('+1 day');
                $weekDayPosition = $dateTime->format('w');
            }
        }

        return $dateTime->format('Y-m-d');
    }

    /**
     * Write into csv schedule for special month.
     *
     * @param $salaryDate
     * @param $bonusDate
     * @param $monthNumber
     */
    protected function _writeScheduleLine($salaryDate, $bonusDate, $monthNumber)
    {
        $scheduleLine = array(
            $this->_getMonthName($monthNumber),
            $salaryDate,
            $bonusDate,
        );

        $this->_writeCsvLine($scheduleLine);
    }

    /**
     * Get DateTime object.
     *
     * @return DateTime
     */
    protected function _dateTime()
    {
        return new DateTime();
    }

    /**
     * Get month name based on it's number in the year.
     *
     * @param $monthNumber
     * @return string
     */
    protected function _getMonthName($monthNumber)
    {
        $dateTime = $this->_dateTime();
        $dateTime->setDate($this->_getCurrentYear(), $monthNumber, self::FIRST_DATE_OF_MONTH);

        return $dateTime->format("F");
    }

    /**
     * Get current year.
     *
     * @return string
     */
    protected function _getCurrentYear()
    {
        if ($this->_currentYear === null) {
            $dateTime = $this->_dateTime();
            $this->_currentYear = $dateTime->format("Y");
        }

        return $this->_currentYear;
    }

    /**
     *  Write column names.
     */
    protected function _writeHeaders()
    {
        $this->_writeCsvLine($this->_outputCsvHeaders);
    }

    /**
     * Write into csv data per line.
     *
     * @param array $fields
     */
    protected function _writeCsvLine(array $fields)
    {
        fputcsv($this->_outputCsv, $fields);
    }

    /**
     * Closes an open file pointer
     */
    public function __destruct()
    {
        if (is_resource($this->_outputCsv)) {
            fclose($this->_outputCsv);
        }
    }
}

$schedule = new Schedule();
$schedule->initSchedule();