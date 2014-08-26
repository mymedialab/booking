<?php
namespace MML\Booking\Utilities;

use MML\Booking;

require __DIR__ . "/../vendor/autoload.php";

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    echo "UNCAUGHT ERROR MUPPET!\n";
    print_r([
        'errno'      => $errno,
        'errstr'     => $errstr,
        'errfile'    => $errfile,
        'errline'    => $errline,
        'errcontext' => $errcontext,
    ]);
    echo "\n";
    exit(1);
});

$overrideSettings = array('mysqlDatabase' => 'booking_test');
$additionalEntities = array(/* Put your namespaced custom entities in here */);

$standardClasses = json_decode(file_get_contents(__DIR__ . '/../utilities/classes.json'), true);
$classesToParse = array_merge($standardClasses, $additionalEntities);

$Factory = new Booking\Factories\General($overrideSettings);
$Doctrine = $Factory->getDoctrine();
$Tool = new \Doctrine\ORM\Tools\SchemaTool($Doctrine);

$classes = array();
foreach ($classesToParse as $className) {
  $classes[] = $Doctrine->getClassMetadata($className);
}

$Tool->updateSchema($classes);

// WIPE DB
$Connection = $Doctrine->getConnection();
$Schema     = $Connection->getSchemaManager();
$Platform   = $Connection->getDatabasePlatform();

$Connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
foreach ($Schema->listTables() as $Table) {
    $truncateSql = $Platform->getTruncateTableSQL($Table->getName());
    $Connection->executeUpdate($truncateSql);
}

$Connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

/// test begins...
try {
    $Booking  = new Booking\App(null, $Factory);
    $Setup    = new Booking\Setup(null, $Factory);

    $Day       = $Factory->getIntervalFactory()->get('daily');
    $Day->configure("09:00", "17:00");
    $Nightly   = $Factory->getIntervalFactory()->get('daily');
    $Nightly->configure("13:00", "09:00", 'nightly', 'nights', 'night');
    $Morning   = $Factory->getIntervalFactory()->get('daily');
    $Morning->configure("09:00", "13:00", 'morning', 'mornings', 'morning');
    $Afternoon = $Factory->getIntervalFactory()->get('daily');
    $Afternoon->configure("13:00", "17:00", 'afternoon', 'afternoons', 'afternoon');
    $Evening   = $Factory->getIntervalFactory()->get('daily');
    $Evening->configure("16:00", "00:00", 'evening', 'evenings', 'evening'); // Note you now can't book an afternoon AND evening.

    $MaintainenceOne = $Factory->getPeriodFactory()->get('generic');
    $MaintainenceOne->begins(new \DateTime('2014-10-20'));
    $MaintainenceOne->ends(new \DateTime('2014-10-30'));
    $MaintainenceTwo = $Factory->getPeriodFactory()->get('generic');
    $MaintainenceTwo->begins(new \DateTime('2015-02-20'));
    $MaintainenceTwo->ends(new \DateTime('2015-02-30'));

    $rooms = array(
        'hotel_double_room'   => array('friendly' => 'Double Room', 'qty' => 7),
        'hotel_superior_room' => array('friendly' => 'Superior Double Room', 'qty' => 5),
        'hotel_penthouse'     => array('friendly' => 'Penthouse Suite', 'qty' => 1),
    );
    $facilities = array(
        'hotel_conference_suite'        => array('friendly' => 'Conference Suite', 'qty' => 2),
        'hotel_large_conference_suite'  => array('friendly' => 'Large Conference Suite', 'qty' => 1),
    );

    foreach ($rooms as $name => $details) {
       $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
       $Setup->addBookingIntervals($Resource, array($Nightly));
    }
    foreach ($facilities as $name => $details) {
       $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
       $Setup->addBookingIntervals($Resource, array($Day, $Afternoon, $Morning, $Evening));
    }

    $DoubleRoom = $Booking->getResource('hotel_double_room');
    $Setup->markUnavailable($DoubleRoom, $MaintainenceOne, 5);
    $Setup->markUnavailable($DoubleRoom, $MaintainenceTwo, 5);


    // try a booking!
    $Start = new \DateTime('24-06-2018');
    $Resource = $Booking->getResource('hotel_double_room');
    $Period   = $Booking->getPeriodFor($Resource, 'nightly');

    $Period->begins($Start);
    $Period->repeat(3);

    $reserved = false;
    $i = 10;

    $Reservation = $Booking->createReservation($Resource, $Period, 7);

} catch (\Exception $e) {
    echo "UNCAUGHT EXCEPTION MUPPET!\n";
    print_r([
        'errno'      => $e->getCode(),
        'errstr'     => $e->getMessage(),
        'errfile'    => $e->getFile(),
        'errline'    => $e->getLine(),
        'errcontext' => $e->getTraceAsString(),
    ]);
    echo "\n";
    exit(1);
}
