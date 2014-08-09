<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Models;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class General
{
    protected $cache = array();

    /**
     * @todo might this be better as a caching factory? Could use a decorator and __call around a
     * real factory. Pros: cleaner. Cons: no granularity? (could do a lookup?)
     *
     * @param  [type] $fn         [description]
     * @param  [type] $identifier [description]
     * @return [type]             [description]
     */
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
            return new Booking\Config();
        }, 'config');
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
            return new Period();
        }, 'PeriodFactory');
    }
    public function getIntervalFactory()
    {
        return $this->cache(function () {
            return new Interval();
        }, 'IntervalFactory');
    }

    public function getEmptyResource()
    {
        return new Models\Resource;
    }
    public function getEmptyReservation()
    {
        return new Models\Reservation;
    }
}
