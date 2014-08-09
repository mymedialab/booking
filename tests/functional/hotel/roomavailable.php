<?php
$Booking = new MML\Booking\App;
$Night   = new \DateTime('24-08-2018');
$Resource  = $Booking->getResource('double_room');

$available = $Resource->getAvailability($Night);
