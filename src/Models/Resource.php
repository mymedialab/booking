<?php
namespace MML\Booking\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 *
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="booking_resources")
 */
class Resource
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column(unique=true) */
    private $name;
    /** @Column(name="friendly_name") */
    private $friendlyName;
    /** @Column(type="datetime") */
    private $created;
    /** @Column(type="datetime") */
    private $modified;
    /** @Column(type="integer") */
    private $quantity;

    /**
     * @OneToMany(targetEntity="MML\Booking\Models\Reservation", mappedBy="Resource")
     * @OrderBy({"start" = "DESC"})
    */
    private $Reservations;
    /**
     * @OneToMany(targetEntity="MML\Booking\Models\BlockReservation", mappedBy="Resource")
     * @OrderBy({"start" = "DESC"})
    */
    private $BlockReservations;
    /**
     * @ManyToMany(targetEntity="MML\Booking\Models\Availability", inversedBy="Resources")
     * @JoinTable(name="booking_resource_availability",
     *      joinColumns={@JoinColumn(name="resource_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="availability_id", referencedColumnName="id")}
     *      )
    */
    private $Availability;

    public function __construct()
    {
        $this->Reservations = new ArrayCollection();
        $this->BlockReservations = new ArrayCollection();
        $this->Intervals = new ArrayCollection();
        $this->Availability = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function getModified()
    {
        return $this->modified;
    }
    public function getReservations()
    {
        return $this->Reservations;
    }
    public function getBlockReservations()
    {
        return $this->BlockReservations;
    }
    public function getBlockReservationsAfter(\DateTime $DateTime)
    {
        $Criteria = Criteria::create();
        $Criteria->where(Criteria::expr()->gt('start', $DateTime));

        return $this->BlockReservations->matching($Criteria);
    }
    public function setName($newName)
    {
        $this->name = $newName;
    }
    public function setQuantity($newQuantity)
    {
        $this->quantity = $newQuantity;
    }
    public function setFriendlyName($newName)
    {
        $this->friendlyName = $newName;
    }

    public function allAvailability()
    {
        return $this->Availability;
    }
    public function getAvailability($name)
    {
        foreach ($this->Availability as $Availability) {
            if (strtolower($Availability->getFriendlyName()) === strtolower($name)) {
                return $Availability;
            }
        }

        throw new Exceptions\Booking("Resource::getAvailability Unknown Availability $name");
    }

    public function addAvailability(Interfaces\Availability $Availability)
    {
        $Entity = $Availability->getEntity();
        $Entity->addResource($this); // synchronously updating inverse side
        $this->Availability[] = $Entity;
    }

    public function removeAvailability(Interfaces\Availability $Availability)
    {
        return $this->Availability->removeElement($Availability);
    }

    /**
     *  @PrePersist
     *  @PreUpdate
     */
    public function prePersist()
    {
        $this->created = $this->created ? $this->created : new \DateTime();
        $this->modified = new \DateTime();
    }
}
