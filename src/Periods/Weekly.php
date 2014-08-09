<?php
namespace MML\Booking\Periods;

use MML\Booking\Interfaces;

class Weekly implements Interfaces\Period
{
    public function begins(\DateTime $DateTime)
    {

    }
    public function ends(\DateTime $DateTime)
    {

    }
    public function repeat($qty)
    {

    }
    public function forcePerSecond()
    {
        return false;
    }
}
