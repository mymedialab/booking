<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

class Hourly extends Base implements Interfaces\Interval
{
    protected $name     = 'Hourly';
    protected $plural   = 'Hours';
    protected $singular = 'Hour';

    protected $hourStarts = '00';

    /**
     *
     * @param  DateTime $RoughStart A time near to the desired start
     * @return DateTime             The exact time on the hour
     */
    public function getNearestStart(\DateTime $RoughStart)
    {
        return $this->nearestTo($RoughStart);
    }

    /**
     *
     * @param  DateTime $RoughEnd   A time near to the desired end
     * @return DateTime             The exact time on the hour
     */
    public function getNearestEnd(\DateTime $RoughEnd)
    {
        return $this->nearestTo($RoughEnd);
    }

    /**
     * Because of the nature of an hour, the start and end can be calculated in the same way!
     *
     * @param  DateTime $Rough A time near to the desired start point
     * @return DateTime        The exact time on the hour
     */
    protected function nearestTo(\DateTime $Rough)
    {
        $Exact = clone $Rough;
        $Exact->setTime($Exact->format('H'), $this->hourStarts, '00');

        $Diff = $Exact->diff($Rough);
        $difference = intval($Diff->s) + (intval($Diff->i) * 60);

        if ($difference > 1800) {
            $Exact->modify('+1 hour');
        }

        return $Exact;
    }

    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        $qty = intval($qty);
        if ($qty < 1) {
            throw new Exceptions\Booking("Intervals\Hourly::calculateEnd requires a positive integer $qty");
        }

        $End = clone $Start;
        $End->modify("+{$qty} hour");
        return $End;
    }

    public function calculateStart(\DateTime $End, $qty = 1)
    {
        $qty = intval($qty);
        if ($qty < 1) {
            throw new Exceptions\Booking("Intervals\Hourly::calculateStart requires a positive integer $qty");
        }

        $Start = clone $End;
        $Start->modify("-{$qty} hour");
        return $Start;
    }

    public function configure($hourStarts = '00', $name = null, $plural = null, $singular = null)
    {
        if (!preg_match('/[0-5][0-9]/', $hourStarts)) {
            throw new Exceptions\Booking("Intervals\Hourly::configure Invalid start time {$hourStarts}.");
        }
        $this->name       = is_null($name)     ? $this->name     : $name;
        $this->plural     = is_null($plural)   ? $this->plural   : $plural;
        $this->singular   = is_null($singular) ? $this->singular : $singular;

        $this->hourStarts = $hourStarts;

        $this->Entity->setName($this->name);
        $this->Entity->setPlural($this->plural);
        $this->Entity->setSingular($this->singular);

        $this->setup(true);
    }

    public function setup($reconfigured = false)
    {
        // if we've just been reconfigured, this needs to be overwritten so don't fetch
        $starts = ($reconfigured) ? false : $this->Entity->getMeta('hourStarts', false);

        if ($starts && preg_match('/[0-5][0-9]/', $starts)) {
            $this->hourStarts = $starts;
        } else {
            $this->Entity->setMeta('hourStarts', $this->hourStarts);
        }
    }

    public function getNextFrom(\DateTime $From)
    {
        // @todo missing function
    }
}
