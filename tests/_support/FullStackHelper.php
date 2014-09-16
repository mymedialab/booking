<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FullStackHelper extends \Codeception\Module
{
    public static function getDbConf()
    {
        return array(
            'isDevMode' => true,
            'mysqlUser' => 'root',
            'mysqlPassword' => '',
            'mysqlDatabase' => 'booking_test',
            'mysqlHost' => 'localhost'
        );
    }

    public static function wipeEntireDb()
    {
        $classesToParse = json_decode(file_get_contents(__DIR__ . '/../../utilities/classes.json'), true);

        $Factory = new \MML\Booking\Factories\General(self::getDbConf());
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
    }

    public function _beforeSuite()
    {
        self::wipeEntireDb();
    }

    public function seeExceptionThrown($exceptionName, $function)
    {
        try {
            $function();
            return false;
        } catch (\Exception $e) {
            if( get_class($e) === $exceptionName){
                return true;
            }
            return false;
        }
    }
}
