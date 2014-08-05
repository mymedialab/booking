<?php
namespace MML\Booking\Utilities;

use MML\Booking;

require __DIR__ . "/../../vendor/autoload.php";

$Factory = new Booking\Factories\General();
$Doctrine = $Factory->getDoctrine();
$Tool = new \Doctrine\ORM\Tools\SchemaTool($Doctrine);

$classes = array(
  $Doctrine->getClassMetadata('MML\\Booking\\Data\\Entity'),
  $Doctrine->getClassMetadata('MML\\Booking\\Data\\Reservation')
);

// @todo block from inclusion!
$Tool->updateSchema($classes);
