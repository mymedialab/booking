<?php
namespace MML\Booking\Interfaces;

interface BlockReservationPersistence
{
    public function setResource(ResourcePersistence $Resource);
    public function setBookingInterval(IntervalPersistence $Interval);
    public function setRepeatInterval(IntervalPersistence $Interval);
    public function setFirstBooking(\DateTime $FirstBooking);
    public function setCutoff(\DateTime $Cutoff = null);
    public function setQuantity($quantity);
}
