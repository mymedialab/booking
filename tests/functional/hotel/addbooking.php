<?php

$Booking = new MML\Booking\App;

$Start = new \DateTime('24-06-2018');
$Resource = $Booking->getResource('double_room');
if (!$Resource) {
    // you wouldn't usually do this inline! This would be a pre-release step. Probably.
    $Resource = $Setup->createResource('double_room');
}
$Period = $Booking->getPeriodFor($Resource, 'night');

// reserve for three nights
$Period->begins($Start);
$Period->repeat(3);
$Reservation = $Booking->createReservation($Resource, $Period);
