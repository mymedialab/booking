<?php
use MML\Booking;

require __DIR__ . "/../vendor/autoload.php";

$overrideSettings = array(/*
    Put your database-details in here */
    'mysqlUser'     => 'root',
    'mysqlPassword' => '',
    'mysqlDatabase' => 'your_db_name',
    'mysqlHost'     => 'localhost',
);
$additionalEntities = array(/* Put your namespaced custom entities in here */);

$standardClasses = json_decode(file_get_contents(__DIR__ . '/classes.json'), true);
$classesToParse = array_merge($standardClasses, $additionalEntities);

$Factory = new Booking\Factories\General($overrideSettings);
$Doctrine = $Factory->getDoctrine();
$Tool = new \Doctrine\ORM\Tools\SchemaTool($Doctrine);

$classes = array();
foreach ($classesToParse as $className) {
  $classes[] = $Doctrine->getClassMetadata($className);
}

$Tool->updateSchema($classes);
