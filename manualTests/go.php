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

function assertTrue($x) {
    if ($x !== true) {
        echo "Following is not true: ";
        var_dump($x);
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

/// test begins...
try {
    $Booking  = new Booking\App($Factory);
    $Setup    = new Booking\Setup($Factory);

    $opensAt  = "08:00";
        $closesAt = "20:00";

        $Weekday  = $Factory->getIntervalFactory()->get('weekday');
        $Weekday->configure($opensAt, $closesAt);

        $Saturday = $Factory->getIntervalFactory()->get('dayOfWeek');
        $Saturday->configure('saturday', $opensAt, "18:00");

        $Sunday   = $Factory->getIntervalFactory()->get('dayOfWeek');
        $Sunday->configure('sunday', "10:00", "16:00");

        $Hourly    = $Factory->getIntervalFactory()->get('hourly');
        $Hourly->configure("00");

        $Morning   = $Factory->getIntervalFactory()->get('daily');
        $Morning->configure("08:00", "12:00");

        $Afternoon = $Factory->getIntervalFactory()->get('daily');
        $Afternoon->configure("12:00", "16:00");

        $Evening   = $Factory->getIntervalFactory()->get('daily');
        $Evening->configure("16:00", "20:00");

        $resources = array(
            'leisureCentre_squash_court'          => array('friendly' => 'Squash Court', 'qty' => 3),
            'leisureCentre_indoor_tennis_court'   => array('friendly' => 'Indoor Tennis Court', 'qty' => 10),
            'leisureCentre_grass_tennis_court'    => array('friendly' => 'Grass Tennis Court', 'qty' => 4),
            // @todo Linked resources one precludes the other. Use Doctrine's inheritance? OUT OF SCOPE
            'leisureCentre_swimming_pool'         => array('friendly' => 'Swimming Pool', 'qty' => 1),
            'leisureCentre_half_pool'             => array('friendly' => 'Half Pool', 'qty' => 2),
        );

        foreach ($resources as $name => $details) {
           $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
           $Setup->addAvailabilityWindow($Resource, $Weekday, array($Hourly, $Morning, $Afternoon, $Evening));
           $Setup->addAvailabilityWindow($Resource, $Saturday, array($Hourly, $Morning, $Afternoon));
           $Setup->addAvailabilityWindow($Resource, $Sunday, array($Hourly));
        }

        $RoughStart = new \DateTime('2015-09-04 10:15');
        $Court  = $Booking->getResource('leisureCentre_squash_court');
        assertTrue(!is_null($Court), 'Resource not found');
        $Period = $Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $Start = $Period->getStart();
        $End   = $Period->getEnd();

        assertEquals('04/09/2015 10:00:00', $Start->format('d/m/Y H:i:s'));
        assertEquals('04/09/2015 11:00:00', $End->format('d/m/Y H:i:s'));

        $RoughStart = new \DateTime('2015-09-04 10:15');
        $Court  = $Booking->getResource('leisureCentre_squash_court');
        $Period = $Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $reservations = $Booking->createReservation($Court, $Period, 2);
        assertEquals(2, count($reservations));


        expectThrow('court full', function() use($Court, $Period, $Booking) {
            $reservations = $Booking->createReservation($Court, $Period, 2);
        }, 'Squash Court does not have enough availability for the selected period');

        // trying to reserve from 07:00 - 09:00. Place doesn't open til 8!
        $RoughStart = new \DateTime('2015-09-04 07:00');
        $Court  = $Booking->getResource('leisureCentre_squash_court');
        $Period = $Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $Period->repeat(2);

        expectThrow('Too early', function() use($Court, $Period, $Booking) {
            $Reservation = $Booking->createReservation($Court, $Period);
        }, 'Squash Court does not have enough availability for the selected period');

        $Tennis  = $Booking->getResource('leisureCentre_grass_tennis_court');
        $Period = $Booking->getPeriodFor($Tennis, 'hourly');

        $Fri = new \DateTime('2014-08-29 18:00');
        $SatEarly = new \DateTime('2014-08-30 17:00');
        $SatLate = new \DateTime('2014-08-30 18:00');

        $Period->begins($Fri);
        $Reservation = $Booking->createReservation($Tennis, $Period);

        $Period->begins($SatEarly);
        $Reservation = $Booking->createReservation($Tennis, $Period);

        $Period->begins($SatLate);
        expectThrow('closes early saturdays', function() use($Tennis, $Period, $Booking) {
            $Reservation = $Booking->createReservation($Tennis, $Period);
        }, 'Grass Tennis Court does not have enough availability for the selected period');

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
