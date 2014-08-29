<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Intervals;
use MML\Booking\Models;

class Availability
{
    protected $IntervalFactory;
    protected $GeneralFactory;

    public function __construct(Interval $IntervalFactory, General $GeneralFactory)
    {
        $this->IntervalFactory = $IntervalFactory;
        $this->GeneralFactory  = $GeneralFactory;
    }

    public function getNew($name)
    {
        $name = strtolower($name);

        $Entity = new Models\Availability();
        $Entity->setType(ucfirst($name));

        $Doctrine = $this->GeneralFactory->getDoctrine();
        $Doctrine->persist($Entity);

        return $this->createWrapper($name, $Entity);
    }

    public function getFrom(Models\Resource $Resource, $name)
    {
        $Entity = $Resource->getAvailability($name);
        $type = strtolower($Entity->getType());

        return $this->createWrapper($type, $Entity);
    }

    protected function createWrapper($name, Interfaces\AvailabilityPersistence $Entity)
    {
        $class= "MML\\Booking\\Availability\\{$name}";

        if (class_exists($class)) {
            return new $class($Entity);
        } else {
            throw new Exceptions\Booking("Factories\\Availability::getFrom Unknown Availability type $name requested");
        }
    }
}
