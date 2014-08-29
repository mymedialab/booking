<?php
namespace MML\Booking\Interfaces;

interface Availability
{
    public function __construct(AvailabilityPersistence $Entity);
    public function getEntity();
    public function hasBookingInterval(Interval $Interval);
    public function addBookingInterval(Interval $Interval);
    public function setAvailableInterval(Interval $Interval);
    public function getAvailableInterval();
    public function getIsAvailable();
    public function setIsAvailable($boolean = true);
}
