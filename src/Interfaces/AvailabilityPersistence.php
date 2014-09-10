<?php
namespace MML\Booking\Interfaces;

interface AvailabilityPersistence
{
    public function setFriendlyName($name);

    public function addResource(ResourcePersistence $Resource);

    public function hasBookingInterval(IntervalPersistence $Interval);
    public function addBookingInterval(IntervalPersistence $Interval);
    public function getBookingInterval($name, $default = null);

    public function setAvailableInterval(IntervalPersistence $Interval);
    public function getAvailableInterval();

    public function getAvailable();
    public function setAvailable($boolean);

    public function setAffectedQuantity($qty);
    public function getAffectedQuantity();
}
