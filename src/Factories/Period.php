<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    protected $periods = array(
        'daily' => '\\MML\\Booking\\Periods\\Daily'
    );

    public function get($periodName, array $options = null)
    {
        // @todo missing function
        return new Periods\Generic;
    }

    public function getFor(Models\Resource $Resource, $name)
    {
        $Interval = $Resource->getInterval($name);
        $type = strtolower($Interval->getType());

        if (!array_key_exists($type, $this->periods)) {
            throw new Exceptions\Booking("Could not find Period of type $type for resource {$Resource->getName()}");
        }

        return new $this->periods[$type]($Interval);
    }

    public function getAllFor(Models\Resource $Resource)
    {
        // @todo missing function
        return array(new Periods\Generic, new Periods\Weekly);
    }
}
