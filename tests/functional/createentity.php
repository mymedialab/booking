<?php
require __DIR__ . "/bootstrap.php";

try {
    $Setup = new MML\Booking\Setup;
    $Setup->createEntity('double_room');
} catch (MML\Booking\Exceptions\Booking $e) {
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Doctrine\DBAL\DBALException $e) {
    echo $e->getMessage() . "\n\n";
    exit(1);
}

echo "\n";
exit(0);
