<?php
namespace MML\Booking\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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

    public function __construct()
    {
        $this->Reservations = new ArrayCollection();
        $this->BlockReservations = new ArrayCollection();
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
