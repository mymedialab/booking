<?php
// @todo use codeception databae I guess?
global $fullStackTestConfig;
$fullStackTestConfig = array(
    'isDevMode' => true,
    'mysqlUser' => 'root',
    'mysqlPassword' => '',
    'mysqlDatabase' => 'booking_test',
    'mysqlHost' => 'localhost'
);

$classesToParse = json_decode(file_get_contents(__DIR__ . '/../../utilities/classes.json'), true);

$Factory = new MML\Booking\Factories\General($fullStackTestConfig);
$Doctrine = $Factory->getDoctrine();

$classes = array();
foreach ($classesToParse as $className) {
  $classes[] = $Doctrine->getClassMetadata($className);
}

$Tool = new \Doctrine\ORM\Tools\SchemaTool($Doctrine);
$Tool->updateSchema($classes);


$Connection = $Doctrine->getConnection();
$Schema     = $Connection->getSchemaManager();
$Platform   = $Connection->getDatabasePlatform();

$Connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
foreach ($Schema->listTables() as $Table) {
    $truncateSql = $Platform->getTruncateTableSQL($Table->getName());
    $Connection->executeUpdate($truncateSql);
}

$Connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
