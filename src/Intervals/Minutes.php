<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Utilities\RegEx;

class Minutes extends Base implements Interfaces\Interval
{
    protected $name     = 'Minutes';
    protected $plural   = 'Minutes';
    protected $singular = 'Interval';

    protected $dayStarts = '00:00';
    protected $duration  = 30;

    /**
     *
     * @param  DateTime $RoughStart A time near to the desired start
     * @return DateTime             The exact time with the interval calculated from the opening
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
        return $this->nearestTo($RoughEnd, true);
    }

    /**
     *
     * @param  DateTime $Rough A time near to the desired start point
     * @return DateTime        The exact time on the hour
     */
    protected function nearestTo(\DateTime $Rough, $end = false)
    {
        $start = explode(':', $this->dayStarts);
        $Starting = clone $Rough;
        $Starting->setTime($start[0], $start[1], '00');
        if ($end) {
            // can't set the end time to be opneing or the start will be before
            $Starting->modify("+{$this->duration} minutes");
        }

        // wind forward to opening time if we're before it.
        if ($Rough <= $Starting) {
            return $Starting;
        }

        // if we're after opening, step through until we meet up
        while (!$this->nearStart($Starting, $Rough)) {
            $Starting->modify("+{$this->duration} minutes");
        }

        return $Starting;
    }

    protected function nearStart(\DateTime $Exact, \DateTime $Rough)
    {
        $interval = round($this->duration / 2);
        $Diff = $Exact->diff($Rough);

        $hours = min(24, $Diff->h);
        if ($hours === 24) {
            $hours = 0;
        }
        $minutesDifference = $Diff->i + ($hours * 60);

        return ($minutesDifference <= $interval);
    }

    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        if ($qty < 1) {
            throw new Exceptions\Booking("Intervals\Duration::clculateEnd qty must be a positive integer");
        }
        $End = clone $Start;
        $minutes = $this->duration * $qty;
        $End->modify("+{$minutes} minutes");

        return $End;
    }

    public function calculateStart(\DateTime $End, $qty = 1)
    {
        if ($qty < 1) {
            throw new Exceptions\Booking("Intervals\Duration::clculateEnd qty must be a positive integer");
        }
        $Start = clone $End;
        $minutes = $this->duration * $qty;
        $Start->modify("-{$minutes} minutes");

        return $Start;
    }

    public function getNextFrom(\DateTime $From)
    {
        // @todo missing function
    }

    /**
     *
     * @param  integer $numberMinutes
     * @param  string  $dayStarts  The time that the durations day begins in 24hr clock. eg 18:00
     * @param  string  $name
     * @param  string  $plural
     * @param  string  $singular
     * @return null
     */
    public function configure($numberMinutes, $dayStarts = null, $name = null, $plural = null, $singular = null)
    {
        if (intval($numberMinutes) < 1) {
            throw new Exceptions\Booking("Intervals\Duration::configure Invalid number of minutes {$numberMinutes}.");
        }
        if (!is_null($dayStarts) && !preg_match(RegEx::time, $dayStarts)) {
            throw new Exceptions\Booking("Intervals\Duration::configure Invalid start time {$dayStarts}.");
        }

        $this->name       = is_null($name)     ? $this->name     : $name;
        $this->plural     = is_null($plural)   ? $this->plural   : $plural;
        $this->singular   = is_null($singular) ? $this->singular : $singular;

        $this->dayStarts = is_null($dayStarts) ? $this->dayStarts : $dayStarts;
        $this->duration  = $numberMinutes;

        $this->Entity->setName($this->name);
        $this->Entity->setPlural($this->plural);
        $this->Entity->setSingular($this->singular);
        $this->setup(true);
    }

    public function setup($reconfigured = false)
    {
        // if we've just been reconfigured, this needs to be overwritten so don't fetch
        $starts   = ($reconfigured) ? false : $this->Entity->getMeta('dayStarts', false);
        $duration = ($reconfigured) ? false : $this->Entity->getMeta('duration', false);

        if ($starts && preg_match(RegEx::time, $starts)) {
            $this->dayStarts = $starts;
        } else {
            $this->Entity->setMeta('dayStarts', $this->dayStarts);
        }
        // if we've just been reconfigured, this needs to be overwritten so don't fetch

        if ($duration && intval($duration) > 0) {
            $this->duration = $duration;
        } else {
            $this->Entity->setMeta('duration', $this->duration);
        }
    }
}
