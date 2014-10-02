<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

class Weekly extends Base implements Interfaces\Interval
{
    protected $Start;
    protected $End;

    protected $defaults = array(
        'name'     => 'Weekly',
        'plural'   => 'Weeks',
        'singular' => 'Week',
        'start'    => 'Last Monday 09:00',
        'end'      => 'Next Friday 17:00',
    );

    public function getNearestStart(\DateTime $RoughStart)
    {
        $Start = clone $RoughStart;
        // by subtracting 4 days, we can always find the nearest day by going to next day.
        $Start->modify("-4 days");
        $Start->modify("next " .$this->Start->format("l"));
        $Start->setTime($this->Start->format("H"), $this->Start->format("i"), "00");
        return $Start;
    }

    public function getNearestEnd(\DateTime $RoughEnd)
    {
        $End = clone $RoughEnd;
        // by subtracting 4 days, we can always find the nearest day by going to next day.
        $End->modify("-4 days");
        $End->modify("next " .$this->End->format("l"));
        $End->setTime($this->End->format("H"), $this->End->format("i"), "00");
        return $End;
    }

    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        $End = clone $Start;
        while ($qty > 0) {
            // keep skipping weeks
            $End->modify("next " .$this->End->format("l"));
            $qty--;
        }
        $End->setTime($this->End->format("H"), $this->End->format("i"), "00");

        return $End;
    }

    public function calculateStart(\DateTime $End, $qty = 1)
    {
        $Start = clone $End;
        while ($qty > 0) {
            // keep skipping weeks
            $Start->modify("last " .$this->Start->format("l"));
            $qty--;
        }
        $Start->setTime($this->Start->format("H"), $this->Start->format("i"), "00");

        return $Start;
    }

    public function getNextFrom(\DateTime $From)
    {
        $Next = clone $From;
        $Next->setTime($this->Start->format("H"), $this->Start->format("i"), "00");
        if ($Next->format('l') !== $this->Start->format('l') || $Next < $From) {
            // If days match, but we've had to wind time backward, then skip to next week or we're not really going
            // forward. If days DON'T match, then skip forwad to obvs.
            $Next->modify('+7 days');
        }

        return $Next;
    }

    /**
     * Extracts day and time from start and end and repeats every week.
     *
     * @param  DateTime $Start
     * @param  DateTime $End
     * @param  string   $name
     * @param  string   $plural
     * @param  string   $singular
     * @return null
     */
    public function configure(\DateTime $Start, \DateTime $End, $name = null, $plural = null, $singular = null)
    {
        $this->Start = $Start;
        $this->End = $End;

        $this->Entity->setMeta('start', $Start->format('Y-m-d H:i:s'));
        $this->Entity->setMeta('end', $End->format('Y-m-d H:i:s'));

        $name     = is_null($name)     ? $this->defaults['name']     : $name;
        $plural   = is_null($plural)   ? $this->defaults['plural']   : $plural;
        $singular = is_null($singular) ? $this->defaults['singular'] : $singular;

        $this->Entity->setName($name);
        $this->Entity->setPlural($plural);
        $this->Entity->setSingular($singular);
    }

    public function setup()
    {
        $start = $this->Entity->getMeta('start', $this->defaults['start']);
        $end   = $this->Entity->getMeta('end', $this->defaults['end']);

        $this->Start  = new \DateTime($start);
        $this->End    = new \DateTime($end);
    }
}
