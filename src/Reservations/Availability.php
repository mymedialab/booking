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

    public function check(Models\Resource $Resource, Interfaces\Period $Period, $qty = 1)
    {
        if (intval($qty) <= 0) {
            throw new Exceptions\Booking("Availability::check requires a positive integer quantity");
        }
        if (!$Period->isPopulated()) {
            throw new Exceptions\Booking("Availability::check failed due to unpopulated Period");
        }

        $taken = $this->singleReservation($Resource, $Period);
        $taken += $this->blockBooking($Resource, $Period);

        // If we've got enough rooms not taken, we have availability!
        return (($Resource->getQuantity() - $taken) >= $qty);
    }

    protected function singleReservation(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $Doctrine = $this->Factory->getDoctrine();

        if ($Period->forcePerSecond()) {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id WHERE ((r.start > :start AND r.start < :end) OR (r.end > :start AND r.end < :end))');
        } else {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id WHERE ((r.start >= :start AND r.start <= :end) OR (r.end >= :start AND r.end <= :end))');
        }

        $Query->setParameter('resource_id', $Resource->getId());
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