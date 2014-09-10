<?php
namespace MML\Booking\Factories;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Factories;
use MML\Booking\Models;

class Reservation
{
    protected $Factory;

    public function __construct(Factories\General $Factory)
    {
        $this->Factory  = $Factory;
    }

    public function getNew($type)
    {
        $Entity = new Models\Reservation;
        $Wrapped = $this->make($type, $Entity);
        // only persist once we know we can wrap it!
        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->persist($Entity);

        return $Wrapped;
    }

    public function wrap(Interfaces\ReservationPersistence $Entity)
    {
        $type = $Entity->getType();
        return $this->make($type, $Entity);
    }

    protected function make($type, Interfaces\ReservationPersistence $Entity)
    {
        $Obj = $this->makeSpecific($type, $Entity);
        if (!$Obj) {
            $namespaced = "\\MML\\Booking\\Reservations\\{$type}";
            $Obj = $this->makeSpecific($namespaced, $Entity);
        }

        if (!$Obj) {
            throw new Exceptions\Booking("Could not create reservation of type {$type}");
        }
        return $Obj;
    }

    protected function makeSpecific($classname, Interfaces\ReservationPersistence $Entity)
    {
        if (class_exists($classname)) {
            $interfaces = class_implements($classname);
            if (in_array('MML\\Booking\\Interfaces\\Reservation', $interfaces)) {
                return new $classname($Entity, $this->Factory);
            }
        }

        return null;
    }
}
