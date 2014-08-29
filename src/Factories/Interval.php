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
        $intervalName = ucfirst(strtolower($intervalName));
        $Entity = new Models\Interval();
        $Entity->setType($intervalName);

        $Doctrine = $this->GeneralFactory->getDoctrine();
        $Doctrine->persist($Entity);

        return $this->createInterval($intervalName, $Entity);
    }

    public function getFrom(Models\Resource $Resource, $name)
    {
        foreach ($Resource->allAvailability() as $Availability) {
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
        $type = strtolower($Entity->getType());

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
