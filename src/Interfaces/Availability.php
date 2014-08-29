<?php
namespace MML\Booking\Interfaces;

use MML\Booking\Factories;

interface Availability
{
    public function __construct(AvailabilityPersistence $Entity, Factories\General $GeneralFactory);
    public function getEntity();
    public function hasBookingInterval(Interval $Interval);
    public function addBookingInterval(Interval $Interval);
    public function setAvailableInterval(Interval $Interval);
    public function getAvailableInterval();
    public function getAvailable();
    public function setAvailable($boolean = true);

    public function overlaps(Period $Period);
}
