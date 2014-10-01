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

    $Resource = $Setup->createResource('blocktest_something', 'This thing here', 2);

    $IntervalFactory = $Factory->getIntervalFactory();

    $RecurringInterval = $IntervalFactory->get('Daily');
    $BookingInterval   = $IntervalFactory->get('TimeOfDay');

    $Resource = $Booking->getResource('blocktest_something');
    $Start = date_create_from_format('d/m/Y', '04/09/1982');
        $End   = date_create_from_format('d/m/Y', '10/01/2011');

    $RecurringInterval->configure('10:00', '12:30', 'This is the recurring interval');
    $BookingInterval->configure('10:00', '12:30', 'some friendly name for booking interval');

    $Booking->createBlockReservation($Resource, $BookingInterval, $RecurringInterval, $Start, $End); // no end!

    $Booking->persist();

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
