<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Intervals;
use MML\Booking\Models;

class Interval
{
    protected $GeneralFactory;

    public function __construct(General $Factory)
    {
        $this->GeneralFactory = $Factory;
    }

    public function get($intervalName)
    {
        $Entity = new Models\Interval();
        $Entity->setType($intervalName);

        // can't persist here in case we need transient entities.
        return $this->createInterval($intervalName, $Entity);
    }

    public function wrap(Interfaces\IntervalPersistence $Entity)
    {
        $type = $Entity->getType();
        return $this->createInterval($type, $Entity);
    }

    public function getFrom(Interfaces\ResourcePersistence $Resource, $name)
    {
        $Availabilities = $Resource->allAvailability();
        if (!count($Availabilities)) {
            throw new Exceptions\Booking(
                "Factories\\Interval Could not retrieve Interval $name from Resource {$Resource->getFriendlyName()}. No Availabilites set."
            );
        }
        foreach ($Availabilities as $Availability) {
            $Entity = $Availability->getBookingInterval($name, false);
            if ($Entity) {
                break;
            }
        }

        if (!$Entity) {
            throw new Exceptions\Booking(
                "Factories\\Interval Could not retrieve Interval $name from Resource {$Resource->getFriendlyName()}"
            );
        }
        $type = $Entity->getType();

        return $this->createInterval($type, $Entity);
    }

    protected function createInterval($name, Interfaces\IntervalPersistence $Entity)
    {
        $class = 'MML\\Booking\\Intervals\\' . $name;

        if (class_exists($class)) {
            return new $class($Entity);
        } else {
            throw new Exceptions\Booking("Factories\\Interval Unknown Interval $name requested");
        }
    }
}
