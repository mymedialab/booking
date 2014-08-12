<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

/**
 * @Entity
 */
class Weekly extends Base implements Interfaces\Interval
{
    public function stagger($interval)
    {

    }
}
