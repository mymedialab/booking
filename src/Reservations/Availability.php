<?php
namespace MML\Booking\Reservations;

use MML\Booking\Factories;
use MML\Booking\Interfaces;
use MML\Booking\Models;

class Availability
{
    protected $Factory;

    public function __construct(Factories\General $Factory)
    {
        $this->Factory = $Factory;
    }

    public function check(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $Doctrine = $this->Factory->getDoctrine();

        if ($Period->forcePerSecond()) {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r WHERE r.start > :end OR r.end < :start');
        } else {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r WHERE r.start >= :end OR r.end <= :start');
        }

        $Query->setParameter('start', $Period->getStart());
        $Query->setParameter('end', $Period->getEnd());

        $count = $Query->getSingleScalarResult();

        // @todo Block bookings. EEK!
        return (intval($count) === 0);
    }
}
