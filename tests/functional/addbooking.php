<?php
require __DIR__ . "/bootstrap.php";

try {
    $Booking = new MML\Booking\App;

    $Start = new \DateTime('24-06-2018');
    $Entity = $Booking->getEntity('double_room');
    $Period = $Booking->getPeriodFor($Entity, 'weekly');

    $Reservation = $Booking->createReservation($Entity, $Start, $Period);
} catch (MML\Booking\Exceptions\Booking $e) {
    echo $e->getMessage() . "\n\n";
    exit(1);
}

echo "\n";
exit(0);
