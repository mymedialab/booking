<?php
namespace MML\Booking\Availability;

use MML\Booking\Interfaces;

/**
 */
class Base
{
    protected $Entity;

    public function __construct(Interfaces\AvailabilityPersistence $Entity)
    {
        $this->Entity = $Entity;
    }
    public function getEntity()
    {
        return $this->Entity;
    }

    public function hasBookingInterval(Interfaces\Interval $Interval)
    {
        return $this->Entity->hasBookingInterval($Interval->getName());
    }
    public function addBookingInterval(Interfaces\Interval $Interval)
    {
        return $this->Entity->addBookingInterval($Interval->getEntity());
    }

    public function setAvailableInterval(Interfaces\Interval $Interval)
    {
        return $this->Entity->setAvailableInterval($Interval->getEntity());
    }
    public function getAvailableInterval()
    {
        return $this->Entity->getAvailableInterval();
    }

    public function getIsAvailable()
    {
        return $this->Entity->getAvailable();
    }
    public function setIsAvailable($boolean = true)
    {
        return $this->Entity->setAvailable($boolean);
    }

    public function setFriendlyName($name)
    {
        return $this->Entity->setFriendlyName($name);
    }
    public function getFriendlyName()
    {
        return $this->Entity->getFriendlyName();
    }

    public function setAffectedQuantity($qty)
    {
        $this->Entity->setAffectedQuantity($qty);
    }
    public function getAffectedQuantity()
    {
        return $this->Entity->getAffectedQuantity();
    }
}
