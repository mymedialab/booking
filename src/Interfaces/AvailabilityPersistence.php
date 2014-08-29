<?php
namespace MML\Booking\Interfaces;

interface AvailabilityPersistence
{
    public function setFriendlyName($name);
    public function hasBookingInterval(IntervalPersistence $Interval);
    public function addBookingInterval(IntervalPersistence $Interval);
    public function getAvailable();
    public function setAvailable($boolean);
}
