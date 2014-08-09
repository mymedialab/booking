<?php
/**
 * Demonstating block booking or recurring bookings.
 * @var MML
 */
$Booking = new MML\Booking\App;
$Setup = new MML\Booking\Setup;

$Resource = $Booking->getResource('conference_suite');
if (!$Resource) {
    // you wouldn't usually do this inline! This would be a pre-release step. Probably.
    $Resource = $Setup->createResource('conference_suite');
}

// start with setting up the first two hour booking
$FirstStart = new \DateTime('2018-06-24 10:00:00');
$Period     = $Booking->getPeriodFor($Resource, 'hourly');
$Period->begins($FirstStart);
$Period->repeat(2);

// Repeat every other week ad infinitum
$Interval = $Booking->getInterval('weekly');
$Interval->stagger(2);

$Reservation = $Booking->createBlockReservation($Resource, $Period, $Interval);
