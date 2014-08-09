<?php
namespace MML\Booking\Periods;

use MML\Booking\Interfaces;

/**
 *
 */
class Generic implements Interfaces\Period
{
    public function begins(\DateTime $DateTime)
    {
        // @todo
    }

    public function ends(\DateTime $DateTime)
    {
        // @todo
    }

    public function repeat($qty)
    {
        // @todo
    }

    public function getStart()
    {
        // @todo
    }

    public function getEnd()
    {
        // @todo
    }

    public function forcePerSecond()
    {
        // @todo as this is generic it should be configurable!
        return false;
    }
}
