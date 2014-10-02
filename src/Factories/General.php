<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\BlockReservations;
use MML\Booking\Calendar;
use MML\Booking\Models;
use MML\Booking\Reservations;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class General
{
    protected $configOverrides;
    protected $cache = array();

    public function __construct(array $configOverrides = null)
    {
        if (!is_null($configOverrides)) {
            $this->configOverrides = $configOverrides;
        }
    }

    /**
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
            $Config = new Booking\Config();
            if (!is_null($this->configOverrides)) {
                $Config->setup($this->configOverrides);
            }

            return $Config;
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
            return new Period($this->getIntervalFactory());
        }, 'PeriodFactory');
    }
    public function getIntervalFactory()
    {
        return $this->cache(function () {
            return new Interval($this);
        }, 'IntervalFactory');
    }

    public function getAvailabilityFactory()
    {
        return $this->cache(function () {
            return new Availability($this->getIntervalFactory(), $this);
        }, 'AvailabilityFactory');
    }

    public function getReservationFactory()
    {
        return $this->cache(function () {
            return new Reservation($this);
        }, 'ReservationFactory');
    }

    public function getResourceFactory()
    {
        return $this->cache(function () {
            return new Resource($this);
        }, 'ResourceFactory');
    }

    public function getBlockReservationFactory()
    {
        return $this->cache(function () {
            return new BlockReservation($this);
        }, 'BlockReservationFactory');
    }

    public function getAvailability($name)
    {
        $Factory = $this->getAvailabilityFactory();
        return $Factory->getNew($name);
    }

    public function getReservationFinder()
    {
        return $this->cache(function () {
            return new Reservations\Utilities\Finder($this);
        }, 'ReservationsFinder');
    }

    public function getInterval($name)
    {
        $Factory = $this->getIntervalFactory();
        return $Factory->get($name);
    }

    public function getReservationAvailability()
    {
        return $this->cache(function () {
            return new Reservations\Utilities\Availability($this);
        }, 'ReservationsAvailability');
    }

    public function getEmptyResource()
    {
        $Factory = $this->getResourceFactory();
        return $Factory->getNew();
    }
    public function getEmptyReservation($name = 'plain')
    {
        $Factory = $this->getReservationFactory();
        return $Factory->getNew($name);
    }
    public function getBlockBooking()
    {
        $Factory = $this->getBlockReservationFactory($this);
        return $Factory->getNew();
    }


    public function getDayCalendar()
    {
        return new Calendar\Day($this);
    }
}
