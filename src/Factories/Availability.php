<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Intervals;
use MML\Booking\Factories;
use MML\Booking\Models;

class Availability
{
    protected $IntervalFactory;
    protected $GeneralFactory;

    public function __construct(Interval $IntervalFactory, Factories\General $GeneralFactory)
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

    public function wrap(Interfaces\AvailabilityPersistence $Availability)
    {
        $type = $Availability->getType();
        return $this->createWrapper($type, $Entity);
    }
    public function getFrom(Interfaces\ResourcePersistence $Resource, $name)
    {
        $Entity = $Resource->getAvailability($name);
        $type = $Entity->getType();

        return $this->createWrapper($type, $Entity);
    }

    public function getAllFor(Interfaces\ResourcePersistence $Resource)
    {
        $all = array();

        foreach ($Resource->allAvailability() as $Entity) {
            $type = $Entity->getType();
            $all[] = $this->createWrapper($type, $Entity);
        }

        return $all;
    }

    protected function createWrapper($name, Interfaces\AvailabilityPersistence $Entity)
    {
        $class= "MML\\Booking\\Availability\\{$name}";

        if (class_exists($class)) {
            return new $class($Entity, $this->GeneralFactory);
        } else {
            throw new Exceptions\Booking("Factories\\Availability::getFrom Unknown Availability type $name requested");
        }
    }
}
