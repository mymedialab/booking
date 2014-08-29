<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

/**
 * Not even a real interval at all! This is just a period in intervals clothing. Ideal for closing out
 * availability on something for a fixed window or whatnot.
 */
class Fixed extends Base implements Interfaces\Interval
{
    protected $Start;
    protected $End;
    protected $defaults = array(
        'name'     => 'Fixed Period',
        'singular' => 'Fixed Period',
        'plural'   => 'Fixed Period',
    );

    public function configure(\DateTime $Start, \DateTime $End, $name = null, $plural = null, $singular = null)
    {
        $this->Entity->setMeta('start', $Start->format('Y-m-d H:i:s'));
        $this->Entity->setMeta('end', $End->format('Y-m-d H:i:s'));

        $this->Start = $Start;
        $this->End   = $End;

        $name     = is_null($name)     ? $this->defaults['name']     : $name;
        $plural   = is_null($plural)   ? $this->defaults['plural']   : $plural;
        $singular = is_null($singular) ? $this->defaults['singular'] : $singular;

        $this->Entity->setName($name);
        $this->Entity->setPlural($plural);
        $this->Entity->setSingular($singular);
    }

    public function getNearestStart(\DateTime $RoughStart)
    {
        return $this->Start;
    }
    public function getNearestEnd(\DateTime $RoughEnd)
    {
        return $this->End;
    }
    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        return $this->End;
    }
    public function calculateStart(\DateTime $End, $qty = 1)
    {
        return $this->Start;
    }

    /**
     * Called by __construct
     * @return null
     */
    protected function setup()
    {
        $start = $this->Entity->getMeta('start', false);
        if ($start) {
            $this->Start = new \DateTime($start);
        }
        $end = $this->Entity->getMeta('end', false);
        if ($end) {
            $this->End = new \DateTime($end);
        }
    }
}
