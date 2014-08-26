<?php
namespace MML\Booking\Periods;

use MML\Booking\Interfaces;

class Weekly implements Interfaces\Period
{
    protected $Interval;

    public function __construct(Interfaces\Interval $Interval)
    {
        $this->Interval = $Interval;
    }

    public function begins(\DateTime $DateTime)
    {

    }
    public function ends(\DateTime $DateTime)
    {

    }
    public function repeat($qty)
    {

    }
    public function getStart()
    {
        // @todo
        return new \DateTime();
    }

    public function getEnd()
    {
        // @todo
        return new \DateTime();
    }

    public function isPopulated()
    {
        // @todo
        return true;
    }

    public function forcePerSecond()
    {
        return false;
    }
}
