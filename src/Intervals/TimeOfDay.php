<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;
use MML\Booking\Utilities\RegEx;

/**
 * Used for example to make a booking between two times on any day. eg specifically 9-5
 *
 */
class TimeOfDay extends Base implements Interfaces\Interval
{
    protected $start = "00:01";
    protected $end   = "23:59";
    protected $straddles = false;

    protected $defaults = array(
        'name'     => 'Time of day',
        'singular' => 'Time of day',
        'plural'   => 'Time of day',
    );

    public function configure($start, $end, $name = null, $plural = null, $singular = null)
    {
        if (!preg_match(RegEx::time, $start) || !preg_match(RegEx::time, $end)) {
            throw new Exceptions\Booking("Intervals\TimeOfDay::configure invalid time format, should be hh:mm");
        }

        $this->Entity->setMeta('start', $start);
        $this->Entity->setMeta('end', $end);

        $this->start = $start;
        $this->end   = $end;

        $name     = is_null($name)     ? $this->defaults['name']     : $name;
        $plural   = is_null($plural)   ? $this->defaults['plural']   : $plural;
        $singular = is_null($singular) ? $this->defaults['singular'] : $singular;

        $this->Entity->setName($name);
        $this->Entity->setPlural($plural);
        $this->Entity->setSingular($singular);

        $this->calculateStraddles();
    }

    public function getNearestStart(\DateTime $RoughStart)
    {
        $Start = clone $RoughStart;
        list($hour, $minute) = $this->splitTime($this->start);
        $Start->setTime($hour, $minute, '00');

        return $Start;
    }
    public function getNearestEnd(\DateTime $RoughEnd)
    {
        $End = clone $RoughEnd;
        list($hour, $minute) = $this->splitTime($this->end);
        $End->setTime($hour, $minute, '00');

        return $End;
    }
    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        $End = clone $Start;
        if (!$this->straddles) {
            $qty--; // for one day, don't adjust date unless we pass midnight
        }
        list($hour, $minute) = $this->splitTime($this->end);
        $End->setTime($hour, $minute, '00');

        if ($qty > 0) {
            $End->modify("+{$qty} days");
        }

        return $End;
    }
    public function calculateStart(\DateTime $End, $qty = 1)
    {
        $Start = clone $End;
        if (!$this->straddles) {
            $qty--; // for one day, don't adjust date unless we pass midnight
        }
        list($hour, $minute) = $this->splitTime($this->start);
        $Start->setTime($hour, $minute, '00');

        if ($qty > 0) {
            $Start->modify("-{$qty} days");
        }

        return $Start;
    }

    public function getNextFrom(\DateTime $From)
    {
        $Next = clone $From;
        list($hour, $minute) = $this->splitTime($this->start);
        $Next->setTime($hours, $minutes, '00');
        while ($Next < $From) {
            $Next->modify("+1 day");
        }

        return $Next;
    }

    protected function splitTime($time)
    {
        $bits = explode(':', $time);
        return array($bits[0], $bits[1]);
    }

    /**
     * Called by __construct
     * @return null
     */
    protected function setup()
    {
        $start = $this->Entity->getMeta('start', false);
        $end   = $this->Entity->getMeta('end', false);
        if ($start && preg_match(RegEx::time, $start)) {
            $this->start = $start;
        }
        if ($end && preg_match(RegEx::time, $end)) {
            $this->end = $end;
        }
        $this->calculateStraddles();
    }

    protected function calculateStraddles()
    {
        $Start = new \DateTime();
        list($hour, $minute) = $this->splitTime($this->start);
        $Start->setTime($hour, $minute);
        $End   = new \DateTime();
        list($hour, $minute) = $this->splitTime($this->end);
        $End->setTime($hour, $minute);

        if ($End < $Start) {
            $this->straddles = true;
        }
    }
}
