<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    public function get($periodName, array $options = null)
    {
        // @todo missing function
        return new Periods\Generic;
    }

    public function getFor(Models\Resource $Resource, $name)
    {
        // @todo missing function
        return new Periods\Generic;
    }

    public function getAllFor(Models\Resource $Resource)
    {
        // @todo missing function
        return array(new Periods\Generic, new Periods\Weekly);
    }
}
