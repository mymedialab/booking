<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 *
 */
class DayOfWeek extends Base implements Interfaces\Interval
{
    protected $name     = 'Weekday';
    protected $singular = 'Weekday';
    protected $plural   = 'Weekdays';

    protected $opens    = '09:00';
    protected $closes   = '17:00';
    protected $day      = "Monday";

    protected $regex = '/^[0-2][0-9]:[0-5][0-9]$/';
    protected $straddles = false;

    public function getNearestStart(\DateTime $RoughStart)
    {
        $open = explode(':', $this->opens);
        return $this->nearestTo($RoughStart, $open[0], $open[1]);
    }

    public function getNearestEnd(\DateTime $RoughEnd)
    {
        $close = explode(':', $this->closes);
        return $this->nearestTo($RoughEnd, $close[0], $close[1]);
    }

    protected function nearestTo(\DateTime $Rough, $hour, $minute)
    {
        $Exact = clone $Rough;
        // by subtracting 4 days, we can always find the nearest day by going to next day.
        $Exact->modify("-4 days");
        $Exact->modify("next {$this->day}");
        $Exact->setTime($hour, $minute, '00');

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

    public function configure($day, $opens, $closes, $plural = null, $singular = null, $name = null)
    {
        if (!preg_match($this->regex, $opens)) {
            throw new Exceptions\Booking("Intervals\DayOfWeek::configure Invalid opening time {$opens}.");
        }
        if (!preg_match($this->regex, $closes)) {
            throw new Exceptions\Booking("Intervals\DayOfWeek::configure Invalid closing time {$closes}.");
        }
        if (!in_array(strtolower($day), array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'))) {
            throw new Exceptions\Booking("Intervals\DayOfWeek::configure Invalid day {$day}.");
        }
        $this->name       = is_null($name)     ? ucfirst($day)   : $name;
        $this->plural     = is_null($plural)   ? $this->plural   : $plural;
        $this->singular   = is_null($singular) ? $this->singular : $singular;

        $this->opens = $opens;
        $this->closes = $closes;
        $this->day = $day;
        $this->save();
    }

    protected function save()
    {
        $this->Entity->setName($this->name);
        $this->Entity->setPlural($this->plural);
        $this->Entity->setSingular($this->singular);
        $this->Entity->setMeta('day', $this->day);
        $this->Entity->setMeta('opens', $this->opens);
        $this->Entity->setMeta('closes', $this->closes);
    }

    /**
     * called by __construct
     *
     * @return null
     */
    protected function setup()
    {
        $this->name     = $this->Entity->getName()     ? $this->Entity->getName()     : $this->name;
        $this->plural   = $this->Entity->getPlural()   ? $this->Entity->getPlural()   : $this->plural;
        $this->singular = $this->Entity->getSingular() ? $this->Entity->getSingular() : $this->singular;

        $properties = array(
            'opens'  => $this->regex,
            'closes' => $this->regex
        );

        foreach ($properties as $id => $regex) {
            $value  = $this->Entity->getMeta($id, false);
            // must strict compare as day could be integer 0 (falsy)
            if ($value !== false && preg_match($regex, $value)) {
                $this->$id = $value;
            }
        }
        $day  = $this->Entity->getMeta('day', false);
        if ($day && in_array(strtolower($day), array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'))) {
            $this->day = $day;
        }

        $Opens  = new \DateTime($this->opens . ":00");
        $Closes = new \DateTime($this->closes . ":00");

        if ($Opens >= $Closes) {
            $this->straddles = true;
        } else {
            $this->straddles = false;
        }

        $this->save();
    }
}
