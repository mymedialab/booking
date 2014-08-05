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
        return new Periods\Weekly;
    }

    public function getFor(Models\Entity $Entity, $name)
    {
        // @todo
        return new Periods\Weekly;
    }
}
