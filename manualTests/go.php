<?php
namespace MML\Booking\Utilities;

use MML\Booking;

require __DIR__ . "/../vendor/autoload.php";

function expectThrow($id, $fn, $message = null)
{
    try {
        $fn();
        // this one should throw an exception as all the rooms are now booked for this period
    } catch (Booking\Exceptions\Booking $e) {
        if ($message && $message !== $e->getMessage()) {
            die("invalid message from exception $id \n\n");
        }
        return;
    }

    die("missing expected exception $id \n\n");
}

function assertEquals($a, $b)
{
    if ($a !== $b) {
        echo "following are unequal: ";
        var_dump($a);
        var_dump($b);
        echo "\n";
        die();
    }
}

function assertTrue($x, $comment = null) {
    if ($x !== true) {
        echo "Following is not true: ";
        if ($comment) {
            echo $comment;
        } else {
            var_dump($x);
        }
        echo "\n";
        die();
    }
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    echo "UNCAUGHT ERROR MUPPET!\n";
    print_r([
        'errno'      => $errno,
        'errstr'     => $errstr,
        'errfile'    => $errfile,
        'errline'    => $errline,
        // 'errcontext' => $errcontext,
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

$Booking  = new Booking\App($Factory);
$Setup    = new Booking\Setup($Factory);

/// test begins...
try {

    $Object = new Booking\Calendar\Day($Factory);

    $Weekday = $Factory->getIntervalFactory()->get('weekday');
    $Weekday->configure('09:00', "20:00");

    $Saturday = $Factory->getIntervalFactory()->get('dayOfWeek');
    $Saturday->configure('saturday', '09:00', "18:00");

    $Sunday = $Factory->getIntervalFactory()->get('dayOfWeek');
    $Sunday->configure('sunday', "10:00", "16:00");

    $Hourly = $Factory->getIntervalFactory()->get('hourly');
    $Hourly->configure("00");

    $Resource = $Setup->createResource('leisureCentre_indoor_tennis_court', 'Indoor Tennis Court', 2);
    $Setup->addAvailabilityWindow($Resource, $Weekday, array($Hourly));
    $Setup->addAvailabilityWindow($Resource, $Saturday, array($Hourly));
    $Setup->addAvailabilityWindow($Resource, $Sunday, array($Hourly));

    $Resource = $Booking->getResource('leisureCentre_indoor_tennis_court');
    $Period   = $Booking->getPeriodFor($Resource, 'hourly');

    $Period->begins(new \DateTime('2014/09/04 10:00:00'));
    $Period->repeat(2);

    // one booking from 10:00 -> 12:00. Should still leave one court available.
    $Reservation = $Booking->createReservation($Resource, $Period);

    $Period->repeat(1);
    // one booking from 10:00 -> 11:00. Should use the last court
    $Reservation = $Booking->createReservation($Resource, $Period);

    $Period->begins(new \DateTime('2014/09/04 17:00:00'));
    $Period->repeat(2);
    // Two bookings from 17:00 -> 19:00. Should use all courts
    $Reservation = $Booking->createReservation($Resource, $Period, 2);

    $Reservations = $Booking->getReservations($Resource, new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
    assertEquals(4, count($Reservations));
    foreach ($Reservations as $Reservation) {
        assertTrue(in_array($Reservation->getStart()->format('H:i'), array('10:00', '17:00')));
        assertTrue(in_array($Reservation->getEnd()->format('H:i'), array('11:00', '12:00', '19:00')));
    }

    $dataFile = __DIR__ . "/../tests/_data/bookedDay.json";

    assertTrue(is_file($dataFile), "file not found");
    $data = json_decode(file_get_contents($dataFile), true);
    assertTrue(is_array($data), "file invalid");

    $Object->setBounds(new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
    $Resource = $Booking->getResource('leisureCentre_indoor_tennis_court');
    assertEquals($data, $Object->availabilityFor($Resource));

} catch (\Exception $e) {
    echo "UNCAUGHT EXCEPTION MUPPET!\n";
    print_r([
        'errno'      => $e->getCode(),
        'errstr'     => $e->getMessage(),
        'errfile'    => $e->getFile(),
        'errline'    => $e->getLine(),
        // 'errcontext' => $e->getTraceAsString(),
    ]);
    echo "\n";
    exit(1);
}
