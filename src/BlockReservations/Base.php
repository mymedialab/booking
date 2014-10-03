<?php
namespace MML\Booking\BlockReservations;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

class Base implements Interfaces\BlockReservation
{
    protected $Entity;
    protected $Factory;

    protected $cachedOccurences = array();

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

        $this->cachedOccurences = array();
    }

    /**
     * Determines if the blockbooking in question overlaps with the given period for purposes of availability checking.
     *
     * @param  Period $Period
     * @return bool
     */
    public function overlaps(Interfaces\Period $Period)
    {
        return !!count($this->occurrencesBetween($Period->getStart(), $Period->getEnd()));
    }

    /**
     * Finds all planned bookings occuring between start and end
     * @param  DateTime $Start
     * @param  DateTime $End
     *
     * @return array|Interfaces\Period[]
     * @todo  this is a lot of stepping through a calendar. Can we make it more efficient? Also, could we cache the
     *        results more permanently?
     */
    public function occurrencesBetween(\DateTime $Start, \DateTime $End)
    {
        $cacheKey = $Start->format('c') . $End->format('c');
        if (array_key_exists($cacheKey, $this->cachedOccurences)) {
            return $this->cachedOccurences[$cacheKey];
        }

        $found = array();
        $BookingStart = $this->Entity->getFirstBooking();
        $Cutoff = $this->Entity->getCutoff(); // may be null!

        if ($End < $BookingStart) {
            // booking won't overlap as it's not started yet.
            return $found;
        }
        if ($Cutoff && $Start > $Cutoff) {
            // booking won't overlap as our run will finish before the start
            return $found;
        }

        $Repeat  = $this->getRepeatInterval();
        $Booking = $this->getBookingInterval();
        $PeriodFactory = $this->Factory->getPeriodFactory();

        // step through planned bookings before period ends or when we hit our [optional] cutoff.
        while ($BookingStart < $End && (!$Cutoff || $BookingStart < $Cutoff)) {
            // find the end of this proposed booking.
            $BookingEnd = $Booking->calculateEnd($BookingStart);

            // @todo this overlap logic peppers the codebase. Need to make a utility. Already had a few bugs from it!
            if (($Start >= $BookingStart && $Start < $BookingEnd) ||
                ($End   > $BookingStart && $End   <= $BookingEnd) ||
                ($Start <= $BookingStart && $End   >= $BookingEnd)
            ) {
                $Period = $PeriodFactory->getStandalone();
                $Period->begins($BookingStart);
                $Period->ends($BookingEnd);
                $found[] = $Period;
            }

            // move on
            $BookingStart = $Repeat->getNextFrom($BookingEnd);
        }

        $this->cachedOccurences[$cacheKey] = $found;
        return $found;
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

    public function getFirstBooking()
    {
        return $this->Entity->getFirstBooking();
    }

    public function getCutoff()
    {
        return $this->Entity->getCutoff();
    }

    public function getResource()
    {
        $Factory = $this->getResourceFactory();
        $Entity = $this->Entity->getResource();
        return ($Entity) ? $Factory->wrap($Entity) : null;
    }

    public function getFriendlyName()
    {
        return $this->Entity->getFriendlyName();
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
