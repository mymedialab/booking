<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    protected $IntervalFactory;

    public function __construct(Interval $IntervalFactory)
    {
        $this->IntervalFactory = $IntervalFactory;
    }

    public function getStandalone()
    {
        return new Periods\Standalone();
    }

    public function getBacked($intervalType)
    {
        $Interval = $this->IntervalFactory->get($intervalType);
        return new Periods\IntervalBacked($Interval);
    }

    public function getFor(Interfaces\Resource $Resource, $name)
    {
        $Interval = $this->findIntervalIn($Resource, $name);
        return new Periods\IntervalBacked($Interval);
    }

    // @todo should this be part of the Resource?
    protected function findIntervalIn(Interfaces\Resource $Resource, $name)
    {
        $Availabilities = $Resource->allAvailability();
        if (!count($Availabilities)) {
            throw new Exceptions\Booking(
                "Factories\\Period Could not retrieve Interval $name from Resource {$Resource->getFriendlyName()}. No Availabilites set."
            );
        }
        foreach ($Availabilities as $Availability) {
            $Interval = $Availability->getBookingInterval($name);
            if ($Interval) {
                break;
            }
        }

        if (is_null($Interval)) {
            throw new Exceptions\Booking(
                "Factories\\Period Could not retrieve Interval $name from Resource {$Resource->getFriendlyName()}"
            );
        }

        return $Interval;
    }
}
