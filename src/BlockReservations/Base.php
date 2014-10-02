<?php
namespace MML\Booking\BlockReservations;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

class Base implements Interfaces\BlockReservation
{
    protected $Entity;
    protected $Factory;

    public function __construct(Interfaces\BlockReservationPersistence $Entity, Factories\General $Factory)
    {
        $this->Entity  = $Entity;
        $this->Factory = $Factory;
    }

    public function setupFrom(
        $friendlyName,
        Interfaces\Resource $Resource,
        Interfaces\Interval $BookingInterval,
        Interfaces\Interval $RepeatInterval,
        \DateTime $FirstBooking,
        \DateTime $Cutoff = null,
        $quantity = 1
    ) {
        $this->Entity->setFriendlyName($friendlyName);
        $this->Entity->setResource($Resource->getEntity());
        $this->Entity->setBookingInterval($BookingInterval->getEntity());
        $this->Entity->setRepeatInterval($RepeatInterval->getEntity());
        $this->Entity->setFirstBooking($FirstBooking);
        $this->Entity->setCutoff($Cutoff);
        $this->Entity->setQuantity($quantity);
    }

    /**
     * Determines if the blockbooking in question overlaps with the given period for purposes of availability checking.
     *
     * @param  Period $Period
     * @return bool
     */
    public function overlaps(Interfaces\Period $Period)
    {

    }

    public function getRepeatInterval()
    {
        $Factory = $this->Factory->getIntervalFactory();
        return $Factory->wrap($this->Entity->getRepeatInterval());
    }
    public function getBookingInterval()
    {
        $Factory = $this->Factory->getIntervalFactory();
        return $Factory->wrap($this->Entity->getBookingInterval());
    }

    public function getQuantity()
    {
        return intval($this->Entity->getQuantity());
    }

    public function getEntity()
    {
        return $this->Entity;
    }
}
