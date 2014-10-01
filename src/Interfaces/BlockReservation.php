<?php
namespace MML\Booking\Interfaces;

interface BlockReservation
{
    public function setupFrom(
        ResourcePersistence $Resource,
        Interval $BookingInterval,
        Interval $RecurringInterval,
        \DateTime $FirstBooking,
        \DateTime $LastBooking = null
    );

    /**
     * Determines if the blockbooking in question overlaps with the given period for purposes of availability checking.
     *
     * @param  Period $Period
     * @return bool
     */
    public function overlaps(Period $Period);
}
