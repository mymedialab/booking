<?php
$Booking = new MML\Booking\App;
$Night   = new \DateTime('24-08-2018');
$Resource  = $Booking->getResource('double_room');
$Period = $Booking->getPeriodFor($Resource, 'night');
$Period->begins($Night);

$available = $Booking->checkAvailability($Resource, $Period);
