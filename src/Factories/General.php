<?php
namespace MML\Booking\Factories;

use MML\Booking;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class General
{
    protected $cache = array();

    protected function cache($fn, $identifier)
    {
        if (!isset($this->cache[$identifier])) {
            $this->cache[$identifier] = $fn();
        }

        return $this->cache[$identifier];
    }

    public function getConfig()
    {
        return $this->cache(function () {
            return new \MML\Booking\Config();
        }, 'config');
    }

    public function makeModel($modelName, $BackingData)
    {
        return new $modelName($BackingData);
    }

    public function getDoctrine()
    {
        $Config = $this->getConfig();

        $paths = array(__DIR__ . '/Data');
        $config = Setup::createAnnotationMetadataConfiguration($paths, $Config->isDevMode);

        // database configuration parameters
        if ($Config->has('doctrineConnection')) {
            $conn = $Config->doctrineConnection;
        } else {
            $conn = array(
                'driver'   => 'pdo_mysql',
                'user'     => $Config->mysqlUser,
                'password' => $Config->mysqlPassword,
                'dbname'   => $Config->mysqlDatabase,
                'host'     => $Config->mysqlHost,
            );
        }

        // obtaining the entity manager
        return $this->cache(function () use ($conn, $config) {
            return EntityManager::create($conn, $config);
        }, 'doctrine-' . md5(serialize($conn), serialize($config)));
    }

    public function getPeriodFactory()
    {
        return $this->cache(function () {
            return new \MML\Booking\Factories\Period();
        }, 'PeriodMapper');
    }

    public function getDataMapper()
    {
        return $this->cache(function () {
            return new \MML\Booking\Factories\DataMapper($this);
        }, 'DataMapper');
    }
}
