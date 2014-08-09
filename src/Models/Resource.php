<?php
namespace MML\Booking\Models;

use Doctrine\Common\Collections\ArrayCollection;
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
    /** @Column(type="datetime") */
    private $created;
    /** @Column(type="datetime") */
    private $modified;
    /** @Column(type="integer") */
    private $quantity = 1;
    /**
     * @OneToMany(targetEntity="MML\Booking\Models\Reservation", mappedBy="Reservation")
     * @OrderBy({"start" = "DESC"})
    */
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * Returns the availability of the resource at the time specified
     *
     * @param  DateTime $Time The time of checking. This may be subject to period smoothing
     * @return integer  number of items available at the given time
     */
    public function getAvailability(\DateTime $Time)
    {
        //@todo is an integer going to work? How long is it available for? Is this an exercise for the user?
        return 1;
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
        return $this->reservations;
    }


    public function setName($newName)
    {
        $this->name = $newName;
    }
    public function setQuantity($newQuantity)
    {
        $this->quantity = $newQuantity;
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
