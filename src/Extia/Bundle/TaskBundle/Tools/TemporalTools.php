<?php

namespace Extia\Bundle\TaskBundle\Tools;

use \DateTime;
use \DateInterval;

/**
 * task calculation handler
 * @see ExtiaTaskBundle/Resources/services/tools.xml
 */
class TemporalTools
{
    protected $workingDays;
    protected $offDays;

    /**
     * construct
     */
    public function __construct($workingDays = null, $offDays = null)
    {
        $this->workingDays = is_null($workingDays) ? range(1, 5) : $workingDays;
        $this->offDays     = is_null($offDays) ? range(6, 7) : $offDays;

        $allDayValues = array_merge($this->workingDays, $this->offDays);
        foreach (range(1, 7) as $dayInt) {
            if (!in_array($dayInt, $allDayValues)) {
                throw new \InvalidArgumentException('A day is missiong into parameters.');
            }
        }
    }

    /**
     * create a DateTime object from a timestamp or a US format
     * @param  mixed    $date date to modify (timestamp or US format)
     * @return DateTime
     */
    public function createDateTime($date)
    {
        if ($date instanceof DateTime) {
            return $date;
        }

        if (is_numeric($date)) {
            return DateTime::createFromFormat('U', $date);
        }

        if (strpos('-', $date) === false) {
            throw new \InvalidArgumentException(sprintf('Need a US date format, "%s" given', $date));
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }

    /**
     * change a date with a period
     *
     * @param  DateTime|string $date   date to modify (timestamp or DateTime)
     * @param  string          $period period
     * @param  string          $format optionnal output format for datetime object
     * @return DateTime
     */
    public function changeDate($date, $period, $output = null)
    {
        $newDate = $this->createDateTime($date)
            ->add(\DateInterval::createFromDateString($period))
        ;

        return $output ? $newDate->format($output) : $newDate;
    }

    /**
     * find next working day from given date
     *
     * @param  mixed     $date
     * @return DateTime
     */
    public function findNextWorkingDay($date)
    {
        $date          = $this->createDateTime($date);
        $dateTimestamp = $date->format('U');
        $dateDay       = $date->format('N');

        while (in_array($dateDay, $this->offDays)) {
            $dateTimestamp += 3600*24;
        }

        return $this->createDateTime($dateTimestamp);
    }
}
