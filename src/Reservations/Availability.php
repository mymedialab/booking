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

        $available = $this->resourcesForPeriod($Resource, $Period);
        if ($available === 0) {
            return false;
        }

        $taken  = $this->singleReservations($Resource, $Period);
        $taken += $this->blockBooking($Resource, $Period);

        // If we've got enough rooms not taken, we have availability!
        return (($available - $taken) >= $qty);
    }

    protected function singleReservations(Models\Resource $Resource, Interfaces\Period $Period)
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

    /**
     * Counts resources available for reservation (eg, not under maitainence or out of action).
     *
     * @param  Models\Resource   $Resource
     * @param  Interfaces\Period $Period
     * @return integer
     */
    protected function resourcesForPeriod(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $qty = intval($Resource->getQuantity());
        if ($qty === 0) {
            // regardless of availability, there's none!
            return 0;
        }

        $Factory = $this->Factory->getAvailabilityFactory();
        $Availabilities = $Factory->getAllFor($Resource);

        $total = 0;
        foreach ($Availabilities as $Availability) {
            // @todo this seems too flimsy and easy to break. What about overlapping periods? Eg days / mornings?
            if ($Availability->getAvailable()) {
                $total += $this->resourcesAvailable($Availability, $Period, $qty);
            } else {
                $total -= $this->resourcesAvailable($Availability, $Period, $qty);
            }
        }
        return $total;
    }

    protected function resourcesAvailable(Interfaces\Availability $Availability, Interfaces\Period $Period, $resourceTotal)
    {
        $qty = intval($Availability->getAffectedQuantity());
        if ($qty === 0) {
            $qty = $resourceTotal;
        }
        if ($Availability->overlaps($Period)) {
            return $qty;
        } else {
            return 0;
        }
    }
}
