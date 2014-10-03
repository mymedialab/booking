<?php
namespace MML\Booking\Reservations;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

/**
 * Used to create a reservation which will never be persisted. For example to convert a potential block reservation to a
 * fixed time reservation inside an application.
 *
 * @todo caching this would be nice. If we have to keep looping through block reservations all the time, why not store
 *       them somewhere temporarily? Maybe memcached? How do we invalidate that though? (and what would we name it?!)
 */
class Transient implements Interfaces\Reservation
{
    private $id;
    private $start;
    private $end;
    private $created;
    private $modified;
    private $Resource;
    private $type;
    private $ReservationMeta;

    /**
     * Both of these will be ignored, just here to meet the interface spec.
     *
     * @param Interfaces\ReservationPersistence $Entity
     * @param Factories\General                 $GeneralFactory
     */
    public function __construct(Interfaces\ReservationPersistence $Entity, Factories\General $GeneralFactory)
    {
        $this->ReservationMeta = array();
    }

    public function setStart(\DateTime $Date)
    {
        $this->Start = $Date;
    }
    public function getStart()
    {
        return $this->Start;
    }
    public function setEnd(\DateTime $Date)
    {
        return $this->End = $Date;
    }
    public function getEnd()
    {
        return $this->End;
    }
    public function getResource()
    {
        $Factory = $this->Factory->getResourceFactory();
        return $Factory->wrap($this->Resource);
    }
    public function setResource(Interfaces\Resource $Resource)
    {
        return $this->Resource = $Resource;
    }
    public function getCreated()
    {
        return new \Datetime();
    }
    public function getModified()
    {
        return new \DateTime();
    }
    public function setupFrom(Interfaces\Resource $Resource, Interfaces\Period $Period)
    {
        $this->Resource = $Resource;
        $this->Start = $Period->getStart();
        $this->End   = $Period->getEnd();
    }
    public function addMeta($name, $value)
    {
        return $this->ReservationMeta[$name] = $value;
    }
    public function getMeta($name, $default = null)
    {
        return array_key_exists($name, $this->ReservationMeta) ? $this->ReservationMeta[$name] : $default;
    }
    public function allMeta()
    {
        return $this->ReservationMeta;
    }
    public function getId()
    {
        return null;
    }
    public function getEntity()
    {
        return null;
    }
}
