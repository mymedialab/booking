<?php
namespace MML\Booking\Data;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="entities")
 */
class Entity
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
    /**
     * @OneToMany(targetEntity="MML\Booking\Data\Reservation", mappedBy="Reservation")
     * @OrderBy({"start" = "DESC"})
    */
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
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
