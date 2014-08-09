<?php
namespace MML\Booking\Reservations;

use MML\Booking\Exceptions;
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
        if (!$Period->isPopulated()) {
            throw new Exceptions\Booking("Availability::check failed due to unpopulated Period");
        }
        $count = $this->singleReservation($Resource, $Period);
        $count += $this->blockBooking($Resource, $Period);

        // If we've got more rooms than bookings, we have availability!
        return ($Resource->getQuantity() > $count);
    }

    protected function singleReservation(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $Doctrine = $this->Factory->getDoctrine();

        if ($Period->forcePerSecond()) {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r WHERE r.start > :end OR r.end < :start');
        } else {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r WHERE r.start >= :end OR r.end <= :start');
        }

        $Query->setParameter('start', $Period->getStart());
        $Query->setParameter('end', $Period->getEnd());

        return intval($Query->getSingleScalarResult());
    }

    protected function blockBooking(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $count = 0;

        foreach ($Resource->getBlockReservationsAfter($Period->getStart()) as $Block) {
            if ($Block->overlaps($Period)) {
                ++$count;
            }
        }

        return $count;
    }
}
