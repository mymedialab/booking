<?php

$Booking = new MML\Booking\App;

$Start   = new \DateTime('24-08-2018');
$End     = new \DateTime('24-09-2018');
$Resource  = $Booking->getResource('double_room');

$Reservation = $Booking->getReservations($Resource, $Start, $End);
