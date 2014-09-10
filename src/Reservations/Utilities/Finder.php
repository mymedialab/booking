<?php
namespace MML\Booking\Reservations\Utilities;

use MMl\Booking\Models;

class Finder
{
    protected $Factory;

    public function __construct($Factory)
    {
        $this->Factory = $Factory;
    }

    public function resourceBetween(Models\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        $Doctrine = $this->Factory->getDoctrine();
        $Query = $Doctrine->createQuery('SELECT R FROM \MML\Booking\Models\Reservation R WHERE R.start >= :start AND R.end <= :end');

        $Query->setParameter('start', $Start);
        $Query->setParameter('end', $End);

        return $Query->getResult();
    }
}
