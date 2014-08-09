<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    public function get($periodName, array $options)
    {
        // @todo
        return new Periods\Generic;
    }

    public function getFor(Models\Resource $Resource, $name)
    {
        // @todo
        return new Periods\Generic;
    }

    public function getAllFor(Models\Resource $Resource)
    {
        // @todo
        return array(new Periods\Generic, new Periods\Weekly);
    }
}
