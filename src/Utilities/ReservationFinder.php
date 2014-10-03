<?php
namespace MML\Booking\Utilities;

use Doctrine\Common\Collections\Criteria;
use MMl\Booking\Interfaces;
use MMl\Booking\Models;

class ReservationFinder
{
    protected $Factory;

    public function __construct($Factory)
    {
        $this->Factory = $Factory;
    }

    /**
     *
     * @param  Interfaces\Resource  $Resource
     * @param  DateTime             $Start
     * @param  DateTime             $End
     * @return array|Interfaces\Reservation[]
     */
    public function reservationsBetween(Interfaces\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        $ReservationFactory = $this->Factory->getReservationFactory();
        $Doctrine = $this->Factory->getDoctrine();

        // @todo this is fine and all; but can we make it so that we use relationships? Doubt Doctrine can optimise
        // this at all. Building a calendar may just take a LOT of queries
        $Query = $Doctrine->createQuery(
            'SELECT
                R FROM \MML\Booking\Models\Reservation R
             WHERE
                ((R.start >= :start AND R.start < :end) OR
                (R.end > :start AND R.end <= :end)) OR
                (R.end >= :end AND R.start <= :start)
             AND R.Resource = :resource'
        );

        $Query->setParameter('start', $Start);
        $Query->setParameter('end', $End);
        $Query->setParameter('resource', $Resource->getEntity());

        $return = array();
        foreach ($Query->getResult() as $ReservationEntity) {
            $return[] = $ReservationFactory->wrap($ReservationEntity);
        }

        return $return;
    }

    /**
     * Finds any block reservations for the supplied resource which have reservation instances falling between the
     * supplied dates
     *
     * @param  Interfaces\Resource  $Resource
     * @param  DateTime             $Start
     * @param  DateTime             $End
     *
     * @return array|Interfaces\BlockReservation[]
     */
    public function blockReservationsBetween(Interfaces\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        $Wrapped = array();

        $Criteria = Criteria::create();
        // where cutoff is after Start, OR last booking is null
        $Criteria->where(
            Criteria::expr()->andX(
                Criteria::expr()->orX(
                    Criteria::expr()->isNull('Cutoff'),
                    Criteria::expr()->gt('Cutoff', $Start)
                ),
                Criteria::expr()->lt('FirstBooking', $Start)
            )
        );
        $Reservations = $Resource->getEntity()->getBlockReservations()->matching($Criteria);

        $Period = $this->Factory->getPeriodFactory()->getStandalone();
        $Period->begins($Start);
        $Period->ends($End);

        $Factory = $this->Factory->getBlockReservationFactory();
        foreach ($Reservations as $Entity) {
            $Block = $Factory->wrap($Entity);
            if ($Block->overlaps($Period)) {
                $Wrapped[] = $Block;
            }
        }

        return $Wrapped;
    }

    /**
     * Searches for any reservations and any lock reservations between these periods and coerces them all into fixed
     * reservations which are returned. Note that whilst the Block reservation "Reservations" meet the interface for a
     * reservation, any database functionality is crippled as they have no persistence methods
     *
     * @param  InterfacesResource $Resource
     * @param  DateTime           $Start
     * @param  DateTime           $End
     * @return array|Interfaces\Reservation[]
     */
    public function allAsFixedBetween(Interfaces\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        $all = $this->reservationsBetween($Resource, $Start, $End);
        $Factory = $this->Factory->getReservationFactory();
        foreach ($this->blockReservationsBetween($Resource, $Start, $End) as $Block) {
            foreach ($Block->occurrencesBetween($Start, $End) as $Period) {
                $TransientReservation = $Factory->getNew('Transient');
                $TransientReservation->setupFrom($Resource, $Period);
                $TransientReservation->addMeta('blockBooking', $Block->getId());
                $all[] = $TransientReservation;
            }
        }

        return $all;
    }

    public function blockReservationsAfter(Interfaces\Resource $Resource, \DateTime $DateTime)
    {
        $Wrapped = array();
        $Criteria = Criteria::create();
        // where cutoff is after Start, OR last booking is null
        $Criteria->where(Criteria::expr()->isNull('Cutoff'))
                 ->orWhere(Criteria::expr()->gt('Cutoff', $DateTime));

        $Reservations = $Resource->getEntity()->getBlockReservations()->matching($Criteria);
        $Factory = $this->Factory->getBlockReservationFactory();
        foreach ($Reservations as $Entity) {
            $Wrapped[] = $Factory->wrap($Entity);
        }

        return $Wrapped;
    }
}
