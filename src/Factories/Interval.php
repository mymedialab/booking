<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Models;
use MML\Booking\Intervals;

class Interval
{
    public function get($IntervalName)
    {
        // @todo missing function
        return new Intervals\Generic;
    }

    public function getAllFor(Models\Resource $Resource)
    {
        // @todo missing function
        return array(new Intervals\Generic, new Intervals\Weekly);
    }
}
