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
        $Start  = $this->Entity->getFirstBooking();
        $Cutoff = $this->Entity->getCutoff(); // may be null!

        if ($Period->getEnd() < $Start) {
            // booking won't overlap as it's not started yet.
            return false;
        }
        if ($Cutoff && $Period->getStart() > $Cutoff) {
            // booking won't overlap as our run will finish before the start
            return false;
        }

        $Repeat  = $this->getRepeatInterval();
        $Booking = $this->getBookingInterval();

        // step through planned bookings before period ends or when we hit our [optional] cutoff.
        while ($Start < $Period->getEnd() && (!$Cutoff || $Start < $Cutoff)) {
            // find the end of this proposed booking.
            $End = $Booking->calculateEnd($Start);

            // @todo this overlap logic peppers the codebase. Need to make a utility. Already had a few bugs from it!
            if (($Period->getStart() >= $Start && $Period->getStart() < $End) ||
                ($Period->getEnd()   > $Start && $Period->getEnd()   <= $End) ||
                ($Period->getStart() <= $Start && $Period->getEnd()   >= $End)) {
                // soon as one overlaps, return
                return true;
            }

            // That booking didn't hit, wind on to the next one.
            $Start = $Repeat->getNextFrom($End);
        }

        // found
        return false;
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
