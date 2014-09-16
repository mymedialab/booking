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

    $resources = array(
        'double_room' => array('friendly' => 'Double Room', 'qty' => 10),
    );
    foreach ($resources as $name => $details) {
        $Resource = $Booking->getResource($name);
        if (!$Resource) {
            $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
            $Nightly  = $Factory->getIntervalFactory()->get('Daily');
            $Nightly->configure("13:00", "09:00", "nightly", "nights", "night");
            $Setup->addBookingIntervals($Resource, array($Nightly));
        }
    }

    $Doctrine->flush();

    $Start = new \DateTime('24-06-2018');
    $Resource = $Booking->getResource('double_room');
    $Period   = $Booking->getPeriodFor($Resource, 'nightly');
    $Period->begins($Start);
    $Period->repeat(3);

    $Reservation = $Booking->createReservation($Resource, $Period, 1);
    $Reservation->addMeta('some_rubbish', 'this thing here');

    assertEquals('this thing here', $Reservation->getMeta('some_rubbish'));
    assertEquals('24-06-2018 13:00', $Reservation->getStart()->format('d-m-Y H:i'));
    assertEquals('27-06-2018 09:00', $Reservation->getEnd()->format('d-m-Y H:i'));

    $Doctrine->flush();
    assertEquals(1, count($Reservation->allMeta()));

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
