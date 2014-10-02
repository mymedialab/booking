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

function assertEquals($a, $b, $message = null)
{
    if ($a !== $b) {
        echo "following are unequal: ";
        var_dump($a);
        var_dump($b);
        echo "\n";
        if ($message) {
            echo "Message Provided was\n";
            echo $message;
            echo "\n";
        }
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

    $Resource = $Setup->createResource('blocktest_something', 'This thing here', 2);
    $Setup->addBookingIntervals($Resource, array());

    $IntervalFactory = $Factory->getIntervalFactory();

    $RecurringInterval = $IntervalFactory->get('Weekly');
    $BookingInterval   = $IntervalFactory->get('TimeOfDay');

    $Start = date_create_from_format('d/m/Y H:i', '04/09/1982 00:00');
    $End   = date_create_from_format('d/m/Y H:i', '10/01/2011 23:59');

    $RecurringInterval->configure($Start, $End, 'Recurring interval'); // end is irrelevant
    $BookingInterval->configure('00:00', '23:59', 'All day.');

    // Limited runs weekly from my birthday to Finleys birthday. Unlimited runs through until infiinity
    $Booking->createBlockReservation('Limited Reservation', $Resource, $BookingInterval, $RecurringInterval, $Start, $End);
    $Booking->createBlockReservation('unlimited Reservation', $Resource, $BookingInterval, $RecurringInterval, $Start); // no end!

    $Booking->persist();

    assertEquals(2, count($Resource->getBlockReservations()), "Block reservations found!");
    // between birthdays, should return 2.
    assertEquals(2, count($Resource->getBlockReservationsAfter(new \DateTime('2000-07-15 00:00:00'))), "Block reservations after 2000 found");
    // After Fin's birthday, return one
    assertEquals(1, count($Resource->getBlockReservationsAfter(new \DateTime('2011-07-15 00:00:00'))), "Block reservations after 2011 found");

    // Any Saturdays prior to my birthday should return 2. Any after finleys birthday should return 1.
    $Availability = $Factory->getReservationAvailability();
    $Period = new Booking\Periods\Standalone();
    $Period->setDuration(new \DateInterval('PT23H59M'));

    // first check that we do have 2 things available on a day other than Saturday...
    $Period->begins(new \DateTime('2010-07-15 00:00:00'));
    assertEquals(true, $Availability->check($Resource, $Period, 2), "Two available on a " . $Period->getStart()->format('l'));

    // Now check that we only have 1 thing available on a Saturday after Fins B'day
    $Period->begins(new \DateTime('2011-07-16 00:00:00'));
    assertEquals(true, $Availability->check($Resource, $Period, 1), "One available on a " . $Period->getStart()->format('l'));
    assertEquals(false, $Availability->check($Resource, $Period, 2), "Two UNavailable on a " . $Period->getStart()->format('l'));

    // Now check that we have nothing available on a Saturday before Fins B'day
    $Period->begins(new \DateTime('2000-07-15 00:00:00'));
    assertEquals(false, $Availability->check($Resource, $Period, 1), "One UNavailable on a " . $Period->getStart()->format('l'));

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
