<?php
namespace MML\Booking\Reservations\Utilities;

use MMl\Booking\Interfaces;
use MMl\Booking\Models;

class Finder
{
    protected $Factory;

    public function __construct($Factory)
    {
        $this->Factory = $Factory;
    }

    /**
     *
     * @param  Resource $Resource [description]
     * @param  DateTime $Start    [description]
     * @param  DateTime $End      [description]
     * @return [type]             [description]
     *
     */
    public function resourceBetween(Interfaces\Resource $Resource, \DateTime $Start, \DateTime $End)
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
                (R.end > :start AND R.end <= :end))
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
}
