<?php
namespace MML\Booking\Reservations\Utilities;

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

    public function check(Interfaces\Resource $Resource, Interfaces\Period $Period, $qty = 1)
    {
        if (intval($qty) <= 0) {
            throw new Exceptions\Booking("Availability::check requires a positive integer quantity");
        }
        if (!$Period->isPopulated()) {
            throw new Exceptions\Booking("Availability::check failed due to unpopulated Period");
        }

        $available = $this->resourcesForPeriod($Resource, $Period);
        if ($available === 0) {
            return false;
        }

        $taken  = $this->singleReservations($Resource, $Period);
        $taken += $this->blockBooking($Resource, $Period);

        // If we've got enough rooms not taken, we have availability!
        return (($available - $taken) >= $qty);
    }

    protected function singleReservations(Interfaces\Resource $Resource, Interfaces\Period $Period)
    {
        $Doctrine = $this->Factory->getDoctrine();

        // @todo should this be moved into a custom repo or something?
        if ($Period->forcePerSecond()) {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id WHERE ((r.start > :start AND r.start < :end) OR (r.end > :start AND r.end < :end))');
        } else {
            $Query = $Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id WHERE ((r.start >= :start AND r.start < :end) OR (r.end > :start AND r.end <= :end))');
        }

        $Query->setParameter('resource_id', $Resource->getId());
        $Query->setParameter('start', $Period->getStart());
        $Query->setParameter('end', $Period->getEnd());

        return intval($Query->getSingleScalarResult());
    }

    protected function blockBooking(Interfaces\Resource $Resource, Interfaces\Period $Period)
    {
        $count = 0;

        foreach ($Resource->getBlockReservationsAfter($Period->getStart()) as $Block) {
            if ($Block->overlaps($Period)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Counts resources available for reservation (eg, not under maitainence or out of action).
     *
     * @param  Interfaces\Resource  $Resource
     * @param  Interfaces\Period    $Period
     * @return integer
     */
    protected function resourcesForPeriod(Interfaces\Resource $Resource, Interfaces\Period $Period)
    {
        $qty = intval($Resource->getQuantity());
        if ($qty === 0) {
            // regardless of availability, there's none!
            return 0;
        }

        $total = 0;
        foreach ($Resource->allAvailability() as $Availability) {
            // @todo this seems too flimsy and easy to break. What about overlapping periods? Eg days / mornings?
            if ($Availability->getAvailable()) {
                // this is a window of availability. Ensure the reservation period is fully inside it
                $total += $this->resourcesAvailable($Availability, $Period, $qty, 'contains');
            } else {
                // this is a time when the place is closed. Ensure no part of the reservation is within the window
                $total -= $this->resourcesAvailable($Availability, $Period, $qty, 'overlaps');
            }
        }

        return $total;
    }

    protected function resourcesAvailable(Interfaces\Availability $Availability, Interfaces\Period $Period, $resourceTotal, $method)
    {
        $qty = intval($Availability->getAffectedQuantity());
        if ($qty === 0) {
            $qty = $resourceTotal;
        }

        if ($Availability->$method($Period)) {
            return $qty;
        } else {
            return 0;
        }
    }
}
