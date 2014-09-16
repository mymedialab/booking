<?php
namespace MML\Booking\Reservations;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

abstract class Base implements Interfaces\Reservation
{
    protected $Entity;
    protected $Factory;

    public function __construct(Interfaces\ReservationPersistence $Entity, Factories\General $GeneralFactory)
    {
        $this->Entity = $Entity;
        $this->Factory = $GeneralFactory;
    }

    public function setStart(\DateTime $Date)
    {
        return $this->Entity->setStart($Date);
    }
    public function getStart()
    {
        return $this->Entity->getStart();
    }
    public function setEnd(\DateTime $Date)
    {
        return $this->Entity->setEnd($Date);
    }
    public function getEnd()
    {
        return $this->Entity->getEnd();
    }
    public function getResource()
    {
        return $this->Entity->getResource();
    }
    public function setResource(Interfaces\ResourcePersistence $Resource)
    {
        return $this->Entity->setResource($Resource);
    }
    public function getCreated()
    {
        return $this->Entity->getCreated();
    }
    public function getModified()
    {
        return $this->Entity->getModified();
    }
    public function setupFrom(Interfaces\ResourcePersistence $Resource, Interfaces\Period $Period)
    {
        return $this->Entity->hydrateFrom($Resource, $Period);
    }
    public function addMeta($name, $value)
    {
        return $this->Entity->setMeta($name, $value);
    }
    public function getMeta($name, $default = null)
    {
        return $this->Entity->getMeta($name, $default);
    }
    public function getEntity()
    {
        return $this->Entity;
    }
}
