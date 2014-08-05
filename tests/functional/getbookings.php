<?php
require __DIR__ . "/bootstrap.php";

try {
    $Booking = new MML\Booking\App;

    $Start   = new \DateTime('24-08-2018');
    $End     = new \DateTime('24-09-2018');
    $Entity  = $Booking->getEntity('double_room');

    $Reservation = $Booking->getReservations($Entity, $Start, $End);
} catch (MML\Booking\Exceptions\Booking $e) {
    echo $e->getMessage() . "\n\n";
    exit(1);
}

echo "\n";
exit(0);
