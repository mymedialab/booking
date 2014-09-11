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
    protected $day      = 1; // Monday by default. internally we use numeric ordering. Externally, whatever strtotime
                             // accepts.

    protected $regex = '/^[0-1][0-9]:[0-5][0-9]$/';
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
        $Exact->setTime($hour, $minute, '00');
        $day = $Exact->format('w');

        $diff = $day - $this->day;

        if ($diff > 3) {
            $mod = 7 - $diff;
            $Exact->modify("+{$mod} days"); // move forward to the next weeks day
        } elseif ($diff > 0) {
            $Exact->modify("-{$diff} days"); // move back to the last day which was this day
        } elseif ($diff < -3) {
            $mod = 7 - abs($diff);
            $Exact->modify("-{$mod} days"); // move back to the last tim we passed this day
        } elseif ($diff < 0) {
            $mod = abs($diff);
            $Exact->modify("+{$mod} days"); // move forward to the next weeks day
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
        $this->name       = is_null($name)     ? ucfirst($day)   : $name;
        $this->plural     = is_null($plural)   ? $this->plural   : $plural;
        $this->singular   = is_null($singular) ? $this->singular : $singular;

        $this->opens = $opens;
        $this->closes = $closes;
        $this->day = $this->translate($day, 'int');
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
            'day'    => '/^[0-6]$/',
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

        $Opens  = new \DateTime($this->opens . ":00");
        $Closes = new \DateTime($this->closes . ":00");

        if ($Opens >= $Closes) {
            $this->straddles = true;
        } else {
            $this->straddles = false;
        }

        $this->save();
    }

    protected function translate($day, $output = 'string')
    {
        if (strlen("$day") === 1) {
            return intval($day);
        }
        $stamp = strtotime($day);

        if ($output === 'string') {
            return date('l', $stamp);
        } else {
            return date('w', $stamp);
        }
    }
}
