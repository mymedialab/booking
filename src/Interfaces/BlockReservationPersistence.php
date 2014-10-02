<?php
namespace MML\Booking\Interfaces;

interface BlockReservationPersistence
{
    public function setResource(ResourcePersistence $Resource);
    public function setBookingInterval(IntervalPersistence $Interval);
    public function setRepeatInterval(IntervalPersistence $Interval);
    public function setFirstBooking(\DateTime $FirstBooking);
    public function setCutoff(\DateTime $Cutoff = null);
    public function setFriendlyName($friendlyName);
    public function setQuantity($quantity);

    public function getResource();
    public function getBookingInterval();
    public function getRepeatInterval();
    public function getFirstBooking();
    public function getCutoff();
    public function getFriendlyName();
    public function getQuantity();
}
