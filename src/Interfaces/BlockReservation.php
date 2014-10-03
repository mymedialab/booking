<?php
namespace MML\Booking\Interfaces;

interface BlockReservation
{
    public function setupFrom(
        $friendlyName,
        Resource $Resource,
        Interval $BookingInterval,
        Interval $RecurringInterval,
        \DateTime $FirstBooking,
        \DateTime $Cutoff = null,
        $quantity = 1
    );

    /**
     * Determines if the blockbooking in question overlaps with the given period for purposes of availability checking.
     *
     * @param  Period $Period
     * @return bool
     */
    public function overlaps(Period $Period);

    public function getFirstBooking();
    public function getCutoff();
    public function getResource();
    public function getRepeatInterval();
    public function getBookingInterval();
    public function getFriendlyName();
    public function getQuantity();
    public function getEntity();
    public function getId();
}
