<?php
namespace MML\Booking\Resources;

use Doctrine\Common\Collections\Collection;
use MML\Booking\Interfaces;
use MML\Booking\Factories;

class Base implements Interfaces\Resource
{
    protected $Factory;
    protected $Entity;

    public function __construct(Interfaces\ResourcePersistence $Entity, Factories\General $Factory)
    {
        $this->Factory = $Factory;
        $this->Entity = $Entity;
    }

    /**
     * @return integer  Database ID
     */
    public function getId()
    {
        return $this->Entity->getId();
    }

    /**
     * @return string Resource name. Usually an internal reference name and to be guaranteed unique
     */
    public function getName()
    {
        return $this->Entity->getName();
    }
    /**
     * @param string $newName Resource name. Usually an internal refernce name, must be unique.
     * @return  null
     */
    public function setName($newName)
    {
        return $this->Entity->setName($newName);
    }

    /**
     * @return int How many of this resource are available
     */
    public function getQuantity()
    {
        return $this->Entity->getQuantity();
    }
    /**
     * @param int $newQuantity How many of this resource are available
     * @return  null
     */
    public function setQuantity($newQuantity)
    {
        return $this->Entity->setQuantity($newQuantity);
    }

    /**
     * @param string A user-facing friendly name for the resource. Can be non-unique.
     */
    public function getFriendlyName()
    {
        return $this->Entity->getFriendlyName();
    }
    /**
     * @param string $newName A user-facing friendly name for the resource. Can be non-unique.
     * @return  null
     */
    public function setFriendlyName($newName)
    {
        return $this->Entity->setFriendlyName($newName);
    }

    /**
     * @return Array|Interfaces\Reservation[]  All reservations associated with the Resource
     */
    public function getReservations()
    {
        $Raw = $this->Entity->getReservations();
        return (count($Raw)) ? $this->wrapEntities($Raw, $this->Factory->getReservationFactory()) : array();
    }

    /**
     * @return Array|Interfaces\BlockReservation[] All block reservations associated with the Resource
     */
    public function getBlockReservations()
    {
        $Raw = $this->Entity->getBlockReservations();
        return (count($Raw)) ? $this->wrapEntities($Raw, $this->Factory->getBlockReservationFactory()) : array();
    }

    /**
     * @return Array|Interfaces\BlockReservation[] All block reservations associated with the Resourcewhich occur after the specified DateTime
     */
    public function getBlockReservationsAfter(\DateTime $DateTime)
    {
        $Raw = $this->Entity->getBlockReservationsAfter($DateTime);
        return (count($Raw)) ? $this->wrapEntities($Raw, $this->Factory->getBlockReservationFactory()) : array();
    }

    /**
     * @return Array|Interfaces\Availability[] All availability associated with the Resource
     */
    public function allAvailability()
    {
        $Raw = $this->Entity->allAvailability();
        return (count($Raw)) ? $this->wrapEntities($Raw, $this->Factory->getAvailabilityFactory()) : array();
    }

    /**
     * @param string $name The identifying name of the Availability being sought
     * @return AvailabilityPersistence The specified availability
     *
     * @throws MML\Booking\Exceptions\Booking If an Availability by the given name is not retrieved
     */
    public function getAvailability($name)
    {
        $Factory = $this->Factory->getAvailabilityFactory();
        return $Factory->wrap($this->Entity->getAvailability($name));
    }

    /**
     * Adds an availability window to the resource
     * @param InterfacesAvailability $Availability
     */
    public function addAvailability(Interfaces\Availability $Availability)
    {
        return $this->Entity->addAvailability($Availability->getEntity());
    }

    /**
     * Removes the supplied availability window from the resource
     * @param AvailabilityPersistence $Availability
     */
    public function removeAvailability(Interfaces\Availability $Availability)
    {
        return $this->Entity->removeAvailability($Availability->getEntity());
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->Entity->getCreated();
    }
    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->Entity->getModified();
    }

    public function getEntity()
    {
        return $this->Entity;
    }

    protected function wrapEntities(Collection $Entities, $Factory)
    {
        $Wrapped = array();
        foreach ($Entities as $Entity) {
            $Wrapped[] = $Factory->wrap($Entity);
        }

        return $Wrapped;
    }
}
