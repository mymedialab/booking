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
        // @todo as this is generic it should be configurable!
        return false;
    }
}
