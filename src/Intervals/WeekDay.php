<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Utilities\RegEx;

/**
 *
 */
class WeekDay extends Base implements Interfaces\Interval
{
    protected $name     = 'Weekday';
    protected $singular = 'Weekday';
    protected $plural   = 'Weekdays';

    protected $opens    = '09:00';
    protected $closes   = '17:00';

    protected $straddles = false;

    public function getNearestStart(\DateTime $RoughStart)
    {
        $opening = explode(':', $this->opens);
        return $this->nearestTo($RoughStart, $opening[0], $opening[1]);
    }

    public function getNearestEnd(\DateTime $RoughEnd)
    {
        $closing = explode(':', $this->closes);
        return $this->nearestTo($RoughEnd, $closing[0], $closing[1]);
    }

    protected function nearestTo(\DateTime $Rough, $targetHour, $targetMinute)
    {
        $Exact = clone $Rough;
        $Exact->setTime($targetHour, $targetMinute, '00');

        $day = intval($Exact->format('w'));
        if ($day === 0) { // Sunday is not a weekday. Roll-on Monday
            $Exact->modify('+1 day');
        } elseif ($day === 6) { // Saturday is not a weekday. Roll back to Friday.
            $Exact->modify('-1 day');
        }

        return $Exact;
    }

    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        $qty = max(intval($qty), 1);
        $closing = explode(':', $this->closes);
        $End = clone $Start;
        $End->setTime($closing[0], $closing[1], '00');

        if (!$this->straddles) {
            $qty--; // for one interval, we keep the same day UNLESS we pass midnight.
        }

        // @todo Should this be exception-y if it goes onto a weekend?
        if ($qty > 0) {
            $End->modify("+{$qty} days");
        }

        return $End;
    }

    public function calculateStart(\DateTime $End, $qty = 1)
    {
        $qty = max(intval($qty), 1);
        $opening = explode(':', $this->opens);
        $Start = clone $End;
        $Start->setTime($opening[0], $opening[1], '00');

        if (!$this->straddles) {
            $qty--; // for one interval, we keep the same day UNLESS we pass midnight.
        }

        if ($qty > 0) {
            $Start->modify("-{$qty} days");
        }

        return $Start;
    }


    public function getNextFrom(\DateTime $From)
    {
        $day = intval($From->format('w'));
        $Exact = clone $From;

        $opening = explode(':', $this->opens);
        $Exact->setTime($opening[0], $opening[1], '00');

        while ($day === 6 || $day === 0 || $Exact <= $From) {
            $Exact->modify('+1 day');
            $day = intval($Exact->format('w'));
        }

        return $Exact;
    }

    public function configure($opens, $closes, $name = null, $plural = null, $singular = null)
    {
        if (!preg_match(RegEx::time, $opens)) {
            throw new Exceptions\Booking("Intervals\Weekday::configure Invalid opening time {$opens}.");
        }
        if (!preg_match(RegEx::time, $closes)) {
            throw new Exceptions\Booking("Intervals\Weekday::configure Invalid closing time {$closes}.");
        }
        $this->name       = is_null($name)     ? $this->name     : $name;
        $this->plural     = is_null($plural)   ? $this->plural   : $plural;
        $this->singular   = is_null($singular) ? $this->singular : $singular;

        $this->opens = $opens;
        $this->closes = $closes;

        $this->Entity->setName($this->name);
        $this->Entity->setPlural($this->plural);
        $this->Entity->setSingular($this->singular);

        $this->setup(true);
    }

    protected function setup($reconfigured = false)
    {
        // if we've just been reconfigured, this needs to be overwritten so don't fetch
        $opens  = ($reconfigured) ? false : $this->Entity->getMeta('opens', false);
        $closes = ($reconfigured) ? false : $this->Entity->getMeta('closes', false);

        if ($opens && preg_match(RegEx::time, $opens)) {
            $this->opens = $opens;
        } else {
            $this->Entity->setMeta('opens', $this->opens);
        }
        if ($closes && preg_match(RegEx::time, $closes)) {
            $this->closes = $closes;
        } else {
            $this->Entity->setMeta('closes', $this->closes);
        }

        $Opens  = new \DateTime($this->opens . ":00");
        $Closes = new \DateTime($this->closes . ":00");

        if ($Opens >= $Closes) {
            $this->straddles = true;
        } else {
            $this->straddles = false;
        }
    }
}
