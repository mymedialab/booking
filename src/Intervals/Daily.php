<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

/**
 * @Entity
 */
class Daily extends Base implements Interfaces\Interval
{
    // defaults; over-ridable by the methods in the base class
    protected $name     = "daily";
    protected $plural   = "days";
    protected $singular = "day";

    public function stagger($interval)
    {
        // @todo missing function
    }

    public function configure($startTime, $endTime, $name = null, $plural = null, $singular = null)
    {

    }
}
